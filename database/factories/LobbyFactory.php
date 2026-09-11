<?php

namespace Database\Factories;

use App\Enums\EmotionSet;
use App\Enums\LobbyStatus;
use App\Models\Lobby;
use App\Models\QuestionBank;
use App\Support\LobbyCodeGenerator;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
            'host_token' => Str::random(40),
            'question_bank_id' => null,
            'round_limit' => null,
            'interrogation_enabled' => false,
            'emotion_set' => EmotionSet::Classic,
        ];
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => LobbyStatus::Closed,
            'closed_at' => now(),
        ]);
    }

    /**
     * A lobby whose host has finished session setup and is ready to play.
     */
    public function configured(): static
    {
        return $this->state(fn (array $attributes): array => [
            'question_bank_id' => QuestionBank::factory(),
        ]);
    }
}
