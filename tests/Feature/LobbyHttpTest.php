<?php

namespace Tests\Feature;

use App\Enums\LobbyStatus;
use App\Enums\RoundStatus;
use App\Models\Lobby;
use Database\Seeders\EmotionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LobbyHttpTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EmotionSeeder::class);
    }

    public function test_landing_page_renders(): void
    {
        $this->get(route('lobbies.create'))
            ->assertOk()
            ->assertSee('Создать лобби');
    }

    public function test_creating_a_lobby_redirects_to_the_host_screen(): void
    {
        $response = $this->post(route('lobbies.store'));

        $lobby = Lobby::sole();

        $response->assertRedirect(route('lobbies.show', $lobby));
        $this->assertSame(LobbyStatus::Open, $lobby->status);
    }

    public function test_host_screen_renders_the_lobby_code_and_qr_code(): void
    {
        $lobby = Lobby::factory()->create();

        $this->get(route('lobbies.show', $lobby))
            ->assertOk()
            ->assertSee($lobby->code)
            ->assertSee(route('lobbies.qr', $lobby), escape: false);
    }

    public function test_qr_endpoint_returns_an_svg_pointing_at_the_join_url(): void
    {
        $lobby = Lobby::factory()->create();

        $response = $this->get(route('lobbies.qr', $lobby));

        $response->assertOk();
        $this->assertSame('image/svg+xml', $response->headers->get('Content-Type'));
    }

    public function test_starting_a_round_makes_it_active(): void
    {
        $lobby = Lobby::factory()->create();

        $this->post(route('lobbies.rounds.store', $lobby))->assertNoContent(201);

        $round = $lobby->currentRound();
        $this->assertNotNull($round);
        $this->assertSame(1, $round->number);
        $this->assertSame(RoundStatus::Active, $round->status);
    }

    public function test_closing_a_lobby_marks_it_closed(): void
    {
        $lobby = Lobby::factory()->create();

        $this->post(route('lobbies.close', $lobby))->assertNoContent();

        $this->assertSame(LobbyStatus::Closed, $lobby->fresh()->status);
    }
}
