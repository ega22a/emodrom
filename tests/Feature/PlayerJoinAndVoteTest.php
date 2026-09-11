<?php

namespace Tests\Feature;

use App\Actions\Lobby\ConfigureSessionAction;
use App\Actions\Round\StartRoundAction;
use App\Data\ConfigureSessionData;
use App\Enums\EmotionSet;
use App\Models\Emotion;
use App\Models\GameRound;
use App\Models\Lobby;
use App\Models\Player;
use App\Models\Question;
use App\Models\QuestionBank;
use Database\Seeders\EmotionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class PlayerJoinAndVoteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EmotionSeeder::class);
    }

    public function test_join_form_renders_the_avatar_grid(): void
    {
        $lobby = Lobby::factory()->create();

        $this->get(route('lobbies.join', $lobby))
            ->assertOk()
            ->assertSee($lobby->code);
    }

    public function test_a_stranger_visiting_a_closed_lobby_sees_a_closed_message_without_redirect_looping(): void
    {
        $lobby = Lobby::factory()->closed()->create();

        $this->get(route('lobbies.join', $lobby))
            ->assertOk()
            ->assertSee('закрыто');
    }

    public function test_joining_creates_a_player_and_redirects_to_the_play_screen(): void
    {
        $lobby = Lobby::factory()->create();

        $response = $this->post(route('lobbies.join.store', $lobby), [
            'name' => 'Аня',
            'avatar' => 'cat',
        ]);

        $response->assertRedirect(route('lobbies.play', $lobby));
        $this->assertSame(1, $lobby->players()->count());
    }

    public function test_play_screen_without_a_session_cookie_redirects_to_join(): void
    {
        $lobby = Lobby::factory()->create();

        $this->get(route('lobbies.play', $lobby))
            ->assertRedirect(route('lobbies.join', $lobby));
    }

    public function test_the_second_joined_player_can_vote_while_the_first_reads(): void
    {
        [$lobby, $round, $reader] = $this->configuredLobbyWithActiveRound();
        $fear = Emotion::where('key', 'fear')->firstOrFail();

        $bobJoin = $this->post(route('lobbies.join.store', $lobby), [
            'name' => 'Bob',
            'avatar' => 'dog',
        ]);
        $bob = $this->asJoinedPlayer($bobJoin, $lobby);

        $bob->get(route('lobbies.play', $lobby))->assertOk();

        $voteResponse = $bob->postJson(route('lobbies.vote', $lobby), ['emotion_id' => $fear->id]);

        $voteResponse->assertCreated();
        $voteResponse->assertJson(['votedEmotionId' => $fear->id]);
        $this->assertSame(1, $round->votes()->count());
    }

    public function test_the_reader_cannot_vote_over_http(): void
    {
        [$lobby, , $reader] = $this->configuredLobbyWithActiveRound();
        $joy = Emotion::where('key', 'joy')->firstOrFail();

        $readerBrowser = $this->withCredentials()->withCookie("emodrom_player_{$lobby->code}", (string) $reader->id);

        $readerBrowser->postJson(route('lobbies.vote', $lobby), ['emotion_id' => $joy->id])
            ->assertStatus(422);
    }

    public function test_the_reader_can_submit_their_emotion_over_http(): void
    {
        [$lobby, $round, $reader] = $this->configuredLobbyWithActiveRound();
        $joy = Emotion::where('key', 'joy')->firstOrFail();

        $readerBrowser = $this->withCredentials()->withCookie("emodrom_player_{$lobby->code}", (string) $reader->id);

        $readerBrowser->postJson(route('lobbies.reader-emotion', $lobby), ['emotion_id' => $joy->id])
            ->assertCreated()
            ->assertJson(['chosenEmotionId' => $joy->id]);

        $this->assertSame($joy->id, $round->fresh()->reader_emotion_id);
    }

    public function test_voting_for_a_locked_emotion_is_rejected(): void
    {
        [$lobby] = $this->configuredLobbyWithActiveRound();
        $anxiety = Emotion::where('key', 'anxiety')->firstOrFail();

        $bobJoin = $this->post(route('lobbies.join.store', $lobby), [
            'name' => 'Bob',
            'avatar' => 'dog',
        ]);
        $bob = $this->asJoinedPlayer($bobJoin, $lobby);

        $bob->postJson(route('lobbies.vote', $lobby), ['emotion_id' => $anxiety->id])
            ->assertStatus(422);
    }

    public function test_a_player_can_buy_an_emotion_over_http(): void
    {
        $lobby = Lobby::factory()->create();
        $joinResponse = $this->post(route('lobbies.join.store', $lobby), [
            'name' => 'Аня',
            'avatar' => 'cat',
        ]);
        $player = Player::sole();
        $player->update(['sparks_balance' => 3]);
        $browser = $this->asJoinedPlayer($joinResponse, $lobby);

        $response = $browser->postJson(route('lobbies.purchase', $lobby));

        $response->assertCreated();
        $this->assertSame(0, $player->fresh()->sparks_balance);
    }

    /**
     * @return array{0: Lobby, 1: GameRound, 2: Player}
     */
    private function configuredLobbyWithActiveRound(): array
    {
        $lobby = Lobby::factory()->create();

        $readerJoin = $this->post(route('lobbies.join.store', $lobby), [
            'name' => 'Reader',
            'avatar' => 'cat',
        ]);
        $reader = Player::sole();
        $this->asJoinedPlayer($readerJoin, $lobby);

        $bank = QuestionBank::factory()->create();
        Question::factory()->for($bank, 'bank')->create();

        app(ConfigureSessionAction::class)->execute($lobby, new ConfigureSessionData(
            questionBankId: $bank->id,
            roundLimit: null,
            interrogationEnabled: false,
            emotionSet: EmotionSet::Classic,
        ));

        $round = app(StartRoundAction::class)->execute($lobby->fresh());

        return [$lobby->fresh(), $round, $reader];
    }

    /**
     * Illuminate\Testing's withCookie() encrypts whatever value it's given
     * before attaching it to the next request (mirroring how a real cookie
     * jar always carries cipher text) — so this needs the *decrypted*
     * player id from the join response, not its raw Set-Cookie value.
     * withCredentials() is also required: postJson()/getJson() otherwise
     * drop cookies entirely, mirroring a fetch() call without
     * `credentials: 'include'`.
     */
    private function asJoinedPlayer(TestResponse $joinResponse, Lobby $lobby): self
    {
        $cookieName = "emodrom_player_{$lobby->code}";
        $playerId = $joinResponse->getCookie($cookieName)->getValue();

        return $this->withCredentials()->withCookie($cookieName, $playerId);
    }
}
