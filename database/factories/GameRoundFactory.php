<?php

namespace Database\Factories;

use App\Enums\RoundStatus;
use App\Models\GameRound;
use App\Models\Lobby;
use App\Models\Player;
use App\Models\Question;
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
            'status' => RoundStatus::Voting,
            'question_id' => Question::factory(),
            'reader_player_id' => Player::factory(),
            'reader_emotion_id' => null,
            'current_interrogation_player_id' => null,
            'mirror_enabled' => false,
            'cocktail_enabled' => false,
            'started_at' => now(),
            'revealed_at' => null,
        ];
    }

    public function revealed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => RoundStatus::Revealed,
            'revealed_at' => now(),
        ]);
    }

    public function mirror(): static
    {
        return $this->state(fn (array $attributes): array => [
            'mirror_enabled' => true,
        ]);
    }

    public function cocktail(): static
    {
        return $this->state(fn (array $attributes): array => [
            'cocktail_enabled' => true,
        ]);
    }
}
