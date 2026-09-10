<?php

namespace Database\Factories;

use App\Enums\RoundStatus;
use App\Models\GameRound;
use App\Models\Lobby;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GameRound>
 */
class GameRoundFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'lobby_id' => Lobby::factory(),
            'number' => 1,
            'status' => RoundStatus::Active,
            'winning_emotion_id' => null,
            'started_at' => now(),
            'ended_at' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => RoundStatus::Completed,
            'ended_at' => now(),
        ]);
    }
}
