<?php

namespace Tests\Feature;

use App\Actions\Round\StartRoundAction;
use App\Models\Emotion;
use App\Models\Lobby;
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

    public function test_a_joined_player_can_reach_the_play_screen_and_vote(): void
    {
        $lobby = Lobby::factory()->create();
        app(StartRoundAction::class)->execute($lobby);
        $joy = Emotion::where('key', 'joy')->firstOrFail();

        $joinResponse = $this->post(route('lobbies.join.store', $lobby), [
            'name' => 'Аня',
            'avatar' => 'cat',
        ]);

        $browser = $this->asJoinedPlayer($joinResponse, $lobby);

        $browser->get(route('lobbies.play', $lobby))->assertOk();

        $voteResponse = $browser->postJson(route('lobbies.vote', $lobby), [
            'emotion_id' => $joy->id,
        ]);

        $voteResponse->assertCreated();
        $voteResponse->assertJson(['votedEmotionId' => $joy->id]);
        $this->assertSame(1, $lobby->players()->sole()->votes()->count());
    }

    public function test_voting_for_a_locked_emotion_is_rejected(): void
    {
        $lobby = Lobby::factory()->create();
        app(StartRoundAction::class)->execute($lobby);
        $anxiety = Emotion::where('key', 'anxiety')->firstOrFail();

        $joinResponse = $this->post(route('lobbies.join.store', $lobby), [
            'name' => 'Аня',
            'avatar' => 'cat',
        ]);

        $browser = $this->asJoinedPlayer($joinResponse, $lobby);

        $browser->postJson(route('lobbies.vote', $lobby), ['emotion_id' => $anxiety->id])
            ->assertStatus(422);
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
        $cookieName = "kukarachas_player_{$lobby->code}";
        $playerId = $joinResponse->getCookie($cookieName)->getValue();

        return $this->withCredentials()->withCookie($cookieName, $playerId);
    }
}
