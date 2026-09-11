<?php

namespace Database\Factories;

use App\Models\GameRound;
use App\Models\Interrogation;
use App\Models\Player;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Interrogation>
 */
class InterrogationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'game_round_id' => GameRound::factory(),
            'player_id' => Player::factory(),
            'sparks_awarded' => null,
        ];
    }
}
