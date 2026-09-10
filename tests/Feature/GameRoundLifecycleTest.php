<?php

namespace Tests\Feature;

use App\Actions\Lobby\CloseLobbyAction;
use App\Actions\Lobby\CreateLobbyAction;
use App\Actions\Lobby\JoinLobbyAction;
use App\Actions\Round\CastVoteAction;
use App\Actions\Round\StartRoundAction;
use App\Data\JoinLobbyData;
use App\Enums\LobbyStatus;
use App\Enums\RoundStatus;
use App\Exceptions\GameException;
use App\Models\Emotion;
use App\Support\Avatars;
use Database\Seeders\EmotionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GameRoundLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EmotionSeeder::class);
    }

    public function test_joining_a_lobby_grants_only_the_base_emotions(): void
    {
        $lobby = app(CreateLobbyAction::class)->execute();

        $player = app(JoinLobbyAction::class)->execute($lobby, new JoinLobbyData('Alice', Avatars::keys()[0]));

        $this->assertSame(6, $player->emotions()->count());
        $this->assertSame(0, $player->emotions()->where('is_base', false)->count());
    }

    public function test_starting_a_new_round_finalizes_the_previous_one_and_rewards_the_majority(): void
    {
        $lobby = app(CreateLobbyAction::class)->execute();
        $join = app(JoinLobbyAction::class);
        $alice = $join->execute($lobby, new JoinLobbyData('Alice', 'cat'));
        $bob = $join->execute($lobby, new JoinLobbyData('Bob', 'dog'));
        $carol = $join->execute($lobby, new JoinLobbyData('Carol', 'fish'));

        $joy = Emotion::where('key', 'joy')->firstOrFail();
        $fear = Emotion::where('key', 'fear')->firstOrFail();

        $startRound = app(StartRoundAction::class);
        $castVote = app(CastVoteAction::class);

        $round1 = $startRound->execute($lobby);
        $castVote->execute($round1, $alice, $joy);
        $castVote->execute($round1, $bob, $joy);
        $castVote->execute($round1, $carol, $fear);

        $round2 = $startRound->execute($lobby);

        $round1->refresh();
        $this->assertSame(RoundStatus::Completed, $round1->status);
        $this->assertSame($joy->id, $round1->winning_emotion_id);

        $this->assertSame(7, $alice->emotions()->count());
        $this->assertSame(7, $bob->emotions()->count());
        $this->assertSame(6, $carol->emotions()->count());

        $this->assertSame(2, $round2->number);
        $this->assertSame(RoundStatus::Active, $round2->status);
    }

    public function test_a_player_cannot_vote_twice_in_the_same_round(): void
    {
        $lobby = app(CreateLobbyAction::class)->execute();
        $player = app(JoinLobbyAction::class)->execute($lobby, new JoinLobbyData('Alice', 'cat'));
        $round = app(StartRoundAction::class)->execute($lobby);
        $joy = Emotion::where('key', 'joy')->firstOrFail();
        $sadness = Emotion::where('key', 'sadness')->firstOrFail();

        $castVote = app(CastVoteAction::class);
        $castVote->execute($round, $player, $joy);

        $this->expectException(GameException::class);
        $castVote->execute($round, $player, $sadness);
    }

    public function test_a_player_cannot_vote_for_an_emotion_they_have_not_unlocked(): void
    {
        $lobby = app(CreateLobbyAction::class)->execute();
        $player = app(JoinLobbyAction::class)->execute($lobby, new JoinLobbyData('Alice', 'cat'));
        $round = app(StartRoundAction::class)->execute($lobby);
        $anxiety = Emotion::where('key', 'anxiety')->firstOrFail();

        $this->expectException(GameException::class);
        app(CastVoteAction::class)->execute($round, $player, $anxiety);
    }

    public function test_closing_a_lobby_finalizes_the_active_round_and_blocks_new_joins(): void
    {
        $lobby = app(CreateLobbyAction::class)->execute();
        $join = app(JoinLobbyAction::class);
        $player = $join->execute($lobby, new JoinLobbyData('Alice', 'cat'));
        $round = app(StartRoundAction::class)->execute($lobby);

        app(CloseLobbyAction::class)->execute($lobby);

        $lobby->refresh();
        $round->refresh();

        $this->assertSame(LobbyStatus::Closed, $lobby->status);
        $this->assertSame(RoundStatus::Completed, $round->status);

        $this->expectException(GameException::class);
        $join->execute($lobby, new JoinLobbyData('Bob', 'dog'));
    }
}
