<?php

namespace Tests\Feature;

use App\Actions\Lobby\CloseLobbyAction;
use App\Actions\Lobby\ConfigureSessionAction;
use App\Actions\Lobby\CreateLobbyAction;
use App\Actions\Lobby\JoinLobbyAction;
use App\Actions\Player\PurchaseEmotionAction;
use App\Actions\Round\AwardInterrogationPointsAction;
use App\Actions\Round\CancelRoundAction;
use App\Actions\Round\CastVoteAction;
use App\Actions\Round\ReassignReaderAction;
use App\Actions\Round\RevealRoundAction;
use App\Actions\Round\StartRoundAction;
use App\Actions\Round\SubmitReaderEmotionAction;
use App\Data\ConfigureSessionData;
use App\Data\JoinLobbyData;
use App\Enums\EmotionSet;
use App\Enums\LobbyStatus;
use App\Exceptions\GameException;
use App\Models\Emotion;
use App\Models\Lobby;
use App\Models\Question;
use App\Models\QuestionBank;
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

    private function configuredLobby(int $rewardSparks = 5, ?int $roundLimit = null, bool $interrogationEnabled = false): Lobby
    {
        $lobby = app(CreateLobbyAction::class)->execute();
        // Plain create(), not the factory: QuestionBankFactory auto-fills a
        // bank with random-reward questions the moment it has none, which
        // would race with the single fixed-reward question this test needs.
        $bank = QuestionBank::create(['name' => 'Test bank']);
        Question::factory()->create(['question_bank_id' => $bank->id, 'reward_sparks' => $rewardSparks]);

        app(ConfigureSessionAction::class)->execute($lobby, new ConfigureSessionData(
            questionBankId: $bank->id,
            roundLimit: $roundLimit,
            interrogationEnabled: $interrogationEnabled,
            emotionSet: EmotionSet::Classic,
        ));

        return $lobby->fresh();
    }

    public function test_joining_a_lobby_grants_only_the_base_emotions(): void
    {
        $lobby = app(CreateLobbyAction::class)->execute();

        $player = app(JoinLobbyAction::class)->execute($lobby, new JoinLobbyData('Alice', 'cat'));

        $this->assertSame(6, $player->emotions()->count());
        $this->assertSame(0, $player->emotions()->where('is_base', false)->count());
    }

    public function test_starting_a_round_requires_a_configured_session(): void
    {
        $lobby = app(CreateLobbyAction::class)->execute();
        app(JoinLobbyAction::class)->execute($lobby, new JoinLobbyData('Alice', 'cat'));

        $this->expectException(GameException::class);
        app(StartRoundAction::class)->execute($lobby);
    }

    public function test_first_joined_player_reads_the_first_round(): void
    {
        $lobby = $this->configuredLobby();
        $join = app(JoinLobbyAction::class);
        $alice = $join->execute($lobby, new JoinLobbyData('Alice', 'cat'));
        $join->execute($lobby, new JoinLobbyData('Bob', 'dog'));

        $round = app(StartRoundAction::class)->execute($lobby);

        $this->assertSame($alice->id, $round->reader_player_id);
        $this->assertTrue($round->isVoting());
        $this->assertNull($round->reader_emotion_id);
    }

    public function test_the_reader_cannot_vote_in_their_own_round(): void
    {
        $lobby = $this->configuredLobby();
        $join = app(JoinLobbyAction::class);
        $alice = $join->execute($lobby, new JoinLobbyData('Alice', 'cat'));
        $join->execute($lobby, new JoinLobbyData('Bob', 'dog'));
        $round = app(StartRoundAction::class)->execute($lobby);
        $joy = Emotion::where('key', 'joy')->firstOrFail();

        $this->expectException(GameException::class);
        app(CastVoteAction::class)->execute($round, $alice, $joy);
    }

    public function test_revealing_requires_the_reader_to_have_chosen_first(): void
    {
        $lobby = $this->configuredLobby();
        app(JoinLobbyAction::class)->execute($lobby, new JoinLobbyData('Alice', 'cat'));
        $round = app(StartRoundAction::class)->execute($lobby);

        $this->expectException(GameException::class);
        app(RevealRoundAction::class)->execute($round);
    }

    public function test_players_who_match_the_readers_emotion_earn_the_questions_sparks(): void
    {
        $lobby = $this->configuredLobby(rewardSparks: 5);
        $join = app(JoinLobbyAction::class);
        $alice = $join->execute($lobby, new JoinLobbyData('Alice', 'cat'));
        $bob = $join->execute($lobby, new JoinLobbyData('Bob', 'dog'));
        $carol = $join->execute($lobby, new JoinLobbyData('Carol', 'fish'));

        $joy = Emotion::where('key', 'joy')->firstOrFail();
        $fear = Emotion::where('key', 'fear')->firstOrFail();

        $round = app(StartRoundAction::class)->execute($lobby);
        app(SubmitReaderEmotionAction::class)->execute($round, $alice, $joy);
        app(CastVoteAction::class)->execute($round, $bob, $joy);
        app(CastVoteAction::class)->execute($round, $carol, $fear);

        app(RevealRoundAction::class)->execute($round->fresh());

        $this->assertSame(5, $bob->fresh()->sparks_balance);
        $this->assertSame(0, $carol->fresh()->sparks_balance);
        $this->assertSame(0, $alice->fresh()->sparks_balance);
        $this->assertTrue($round->fresh()->isRevealed());
    }

    public function test_the_reader_keeps_the_sparks_when_nobody_matches_their_emotion(): void
    {
        $lobby = $this->configuredLobby(rewardSparks: 7);
        $join = app(JoinLobbyAction::class);
        $alice = $join->execute($lobby, new JoinLobbyData('Alice', 'cat'));
        $bob = $join->execute($lobby, new JoinLobbyData('Bob', 'dog'));

        $joy = Emotion::where('key', 'joy')->firstOrFail();
        $fear = Emotion::where('key', 'fear')->firstOrFail();

        $round = app(StartRoundAction::class)->execute($lobby);
        app(SubmitReaderEmotionAction::class)->execute($round, $alice, $joy);
        app(CastVoteAction::class)->execute($round, $bob, $fear);

        app(RevealRoundAction::class)->execute($round->fresh());

        $this->assertSame(7, $alice->fresh()->sparks_balance);
        $this->assertSame(0, $bob->fresh()->sparks_balance);
    }

    public function test_interrogation_targets_the_smallest_non_matching_group_and_awards_points(): void
    {
        $lobby = $this->configuredLobby(rewardSparks: 5, interrogationEnabled: true);
        $join = app(JoinLobbyAction::class);
        $alice = $join->execute($lobby, new JoinLobbyData('Alice', 'cat'));
        $bob = $join->execute($lobby, new JoinLobbyData('Bob', 'dog'));
        $carol = $join->execute($lobby, new JoinLobbyData('Carol', 'fish'));
        $dave = $join->execute($lobby, new JoinLobbyData('Dave', 'bug'));

        $joy = Emotion::where('key', 'joy')->firstOrFail();
        $fear = Emotion::where('key', 'fear')->firstOrFail();
        $sadness = Emotion::where('key', 'sadness')->firstOrFail();

        $round = app(StartRoundAction::class)->execute($lobby);
        app(SubmitReaderEmotionAction::class)->execute($round, $alice, $joy);
        app(CastVoteAction::class)->execute($round, $bob, $fear);
        app(CastVoteAction::class)->execute($round, $carol, $fear);
        app(CastVoteAction::class)->execute($round, $dave, $sadness);

        app(RevealRoundAction::class)->execute($round->fresh());
        $round = $round->fresh();

        $this->assertSame(1, $round->interrogations()->count());
        $this->assertSame($dave->id, $round->interrogations()->first()->player_id);
        $this->assertSame($dave->id, $round->current_interrogation_player_id);

        $interrogation = $round->interrogations()->first();
        app(AwardInterrogationPointsAction::class)->execute($round, $interrogation, 3);

        $this->assertSame(3, $dave->fresh()->sparks_balance);
        $this->assertNull($round->fresh()->current_interrogation_player_id);
    }

    public function test_cancelling_a_round_deletes_it_without_side_effects(): void
    {
        $lobby = $this->configuredLobby();
        $join = app(JoinLobbyAction::class);
        $alice = $join->execute($lobby, new JoinLobbyData('Alice', 'cat'));
        $join->execute($lobby, new JoinLobbyData('Bob', 'dog'));
        $round = app(StartRoundAction::class)->execute($lobby);

        app(CancelRoundAction::class)->execute($round);

        $this->assertSame(0, $lobby->rounds()->count());
        $this->assertSame(0, $alice->fresh()->sparks_balance);
    }

    public function test_the_host_can_reassign_the_reader_before_reveal(): void
    {
        $lobby = $this->configuredLobby();
        $join = app(JoinLobbyAction::class);
        $alice = $join->execute($lobby, new JoinLobbyData('Alice', 'cat'));
        $bob = $join->execute($lobby, new JoinLobbyData('Bob', 'dog'));
        $round = app(StartRoundAction::class)->execute($lobby);
        $joy = Emotion::where('key', 'joy')->firstOrFail();
        app(CastVoteAction::class)->execute($round, $bob, $joy);

        $round = app(ReassignReaderAction::class)->execute($round, $bob);

        $this->assertSame($bob->id, $round->reader_player_id);
        $this->assertNull($round->reader_emotion_id);
        $this->assertSame(0, $round->votes()->count());
    }

    public function test_a_player_can_buy_a_random_emotion_for_three_sparks(): void
    {
        $lobby = app(CreateLobbyAction::class)->execute();
        $player = app(JoinLobbyAction::class)->execute($lobby, new JoinLobbyData('Alice', 'cat'));
        $player->update(['sparks_balance' => 3]);

        app(PurchaseEmotionAction::class)->execute($player->fresh());

        $this->assertSame(0, $player->fresh()->sparks_balance);
        $this->assertSame(7, $player->fresh()->emotions()->count());
    }

    public function test_buying_an_emotion_without_enough_sparks_fails(): void
    {
        $lobby = app(CreateLobbyAction::class)->execute();
        $player = app(JoinLobbyAction::class)->execute($lobby, new JoinLobbyData('Alice', 'cat'));

        $this->expectException(GameException::class);
        app(PurchaseEmotionAction::class)->execute($player);
    }

    public function test_starting_a_round_past_the_configured_limit_is_blocked(): void
    {
        $lobby = $this->configuredLobby(roundLimit: 1);
        $join = app(JoinLobbyAction::class);
        $alice = $join->execute($lobby, new JoinLobbyData('Alice', 'cat'));
        $join->execute($lobby, new JoinLobbyData('Bob', 'dog'));
        $round = app(StartRoundAction::class)->execute($lobby);
        $joy = Emotion::where('key', 'joy')->firstOrFail();
        app(SubmitReaderEmotionAction::class)->execute($round, $alice, $joy);
        app(RevealRoundAction::class)->execute($round->fresh());

        $this->expectException(GameException::class);
        app(StartRoundAction::class)->execute($lobby->fresh());
    }

    public function test_starting_a_new_session_resets_rounds_sparks_and_emotions(): void
    {
        $lobby = $this->configuredLobby();
        $join = app(JoinLobbyAction::class);
        $alice = $join->execute($lobby, new JoinLobbyData('Alice', 'cat'));
        $join->execute($lobby, new JoinLobbyData('Bob', 'dog'));
        $round = app(StartRoundAction::class)->execute($lobby);
        $joy = Emotion::where('key', 'joy')->firstOrFail();
        app(SubmitReaderEmotionAction::class)->execute($round, $alice, $joy);
        app(RevealRoundAction::class)->execute($round->fresh());
        $alice->update(['sparks_balance' => 42]);

        $newBank = QuestionBank::factory()->create();
        Question::factory()->create(['question_bank_id' => $newBank->id]);

        app(ConfigureSessionAction::class)->execute($lobby->fresh(), new ConfigureSessionData(
            questionBankId: $newBank->id,
            roundLimit: null,
            interrogationEnabled: false,
            emotionSet: EmotionSet::Classic,
        ));

        $this->assertSame(0, $lobby->fresh()->rounds()->count());
        $this->assertSame(0, $alice->fresh()->sparks_balance);
        $this->assertSame(6, $alice->fresh()->emotions()->count());
    }

    public function test_closing_a_lobby_blocks_new_joins(): void
    {
        $lobby = app(CreateLobbyAction::class)->execute();
        $join = app(JoinLobbyAction::class);
        $join->execute($lobby, new JoinLobbyData('Alice', 'cat'));

        app(CloseLobbyAction::class)->execute($lobby);

        $this->assertSame(LobbyStatus::Closed, $lobby->fresh()->status);

        $this->expectException(GameException::class);
        $join->execute($lobby->fresh(), new JoinLobbyData('Bob', 'dog'));
    }
}
