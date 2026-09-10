<?php

namespace Database\Factories;

use App\Models\Lobby;
use App\Models\Player;
use App\Support\Avatars;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Player>
 */
class PlayerFactory extends Factory
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
            'name' => $this->faker->firstName(),
            'avatar' => $this->faker->randomElement(Avatars::keys()),
            'joined_at' => now(),
        ];
    }
}
