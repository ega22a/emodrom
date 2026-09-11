<?php

namespace Tests\Feature;

use App\Enums\LobbyStatus;
use App\Models\Emotion;
use App\Models\Lobby;
use App\Models\Player;
use App\Models\Question;
use App\Models\QuestionBank;
use Database\Seeders\EmotionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class HostHttpTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EmotionSeeder::class);
    }

    public function test_landing_page_renders(): void
    {
        $this->get(route('host.create'))
            ->assertOk()
            ->assertSee('Создать лобби');
    }

    public function test_creating_a_lobby_redirects_to_session_setup(): void
    {
        $response = $this->post(route('host.store'));

        $lobby = Lobby::sole();

        $response->assertRedirect(route('host.setup', $lobby));
        $this->assertSame(LobbyStatus::Open, $lobby->status);
    }

    public function test_someone_without_the_host_cookie_cannot_open_the_setup_page(): void
    {
        $lobby = Lobby::factory()->create();

        $this->get(route('host.setup', $lobby))->assertForbidden();
    }

    public function test_configuring_a_session_redirects_to_the_host_panel(): void
    {
        [$lobby, $host] = $this->createLobbyAsHost();
        $bank = QuestionBank::factory()->create();

        $response = $host->post(route('host.configure', $lobby), [
            'question_bank_id' => $bank->id,
            'round_limit' => 10,
            'interrogation_enabled' => true,
            'emotion_set' => 'classic',
        ]);

        $response->assertRedirect(route('host.show', $lobby));
        $this->assertTrue($lobby->fresh()->isSessionConfigured());
    }

    public function test_the_host_panel_redirects_to_setup_until_a_session_is_configured(): void
    {
        [$lobby, $host] = $this->createLobbyAsHost();

        $host->get(route('host.show', $lobby))->assertRedirect(route('host.setup', $lobby));
    }

    public function test_the_screen_is_public_and_shows_the_lobby_code(): void
    {
        $lobby = Lobby::factory()->create();

        $this->get(route('screen.show', $lobby))
            ->assertOk()
            ->assertSee($lobby->code);
    }

    public function test_a_non_host_cannot_start_a_round(): void
    {
        $lobby = Lobby::factory()->configured()->create();

        $this->post(route('host.round.store', $lobby))->assertForbidden();
    }

    public function test_the_host_can_start_reveal_and_close_over_http(): void
    {
        [$lobby, $host] = $this->createConfiguredLobbyAsHost();
        $alice = Player::factory()->for($lobby)->create();
        $bob = Player::factory()->for($lobby)->create();
        $baseEmotionIds = Emotion::where('is_base', true)->pluck('id');
        $alice->emotions()->attach($baseEmotionIds);
        $bob->emotions()->attach($baseEmotionIds);

        $host->post(route('host.round.store', $lobby))->assertNoContent(201);

        $round = $lobby->fresh()->currentRound();
        $this->assertNotNull($round);

        $joy = Emotion::where('key', 'joy')->firstOrFail();
        $reader = $round->reader;
        $readerCookie = $this->joinCookieFor($lobby, $reader);
        $this->withCredentials()->withCookie(...$readerCookie)
            ->postJson(route('lobbies.reader-emotion', $lobby), ['emotion_id' => $joy->id])
            ->assertCreated();

        $host->post(route('host.round.reveal', $lobby))->assertNoContent();
        $this->assertTrue($round->fresh()->isRevealed());

        $host->post(route('host.close', $lobby))->assertNoContent();
        $this->assertSame(LobbyStatus::Closed, $lobby->fresh()->status);
    }

    /**
     * @return array{0: Lobby, 1: TestCase}
     */
    private function createLobbyAsHost(): array
    {
        $response = $this->post(route('host.store'));
        $lobby = Lobby::sole();

        return [$lobby, $this->asHost($response, $lobby)];
    }

    /**
     * @return array{0: Lobby, 1: TestCase}
     */
    private function createConfiguredLobbyAsHost(): array
    {
        [$lobby, $host] = $this->createLobbyAsHost();
        $bank = QuestionBank::factory()->create();
        Question::factory()->for($bank, 'bank')->create();

        $host->post(route('host.configure', $lobby), [
            'question_bank_id' => $bank->id,
            'round_limit' => null,
            'interrogation_enabled' => false,
            'emotion_set' => 'classic',
        ]);

        return [$lobby->fresh(), $host];
    }

    /**
     * See PlayerJoinAndVoteTest::asJoinedPlayer() for why the decrypted
     * (not raw Set-Cookie) value is what withCookie() needs.
     */
    private function asHost(TestResponse $storeResponse, Lobby $lobby): self
    {
        $cookieName = "emodrom_host_{$lobby->code}";
        $token = $storeResponse->getCookie($cookieName)->getValue();

        return $this->withCredentials()->withCookie($cookieName, $token);
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function joinCookieFor(Lobby $lobby, Player $player): array
    {
        return ["emodrom_player_{$lobby->code}", (string) $player->id];
    }
}
