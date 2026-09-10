<?php

namespace Database\Factories;

use App\Enums\LobbyStatus;
use App\Models\Lobby;
use App\Support\LobbyCodeGenerator;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lobby>
 */
class LobbyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => app(LobbyCodeGenerator::class)->unique(),
            'status' => LobbyStatus::Open,
            'closed_at' => null,
        ];
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => LobbyStatus::Closed,
            'closed_at' => now(),
        ]);
    }
}
