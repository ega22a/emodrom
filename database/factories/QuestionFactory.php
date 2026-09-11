<?php

namespace Database\Factories;

use App\Models\Question;
use App\Models\QuestionBank;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Question>
 */
class QuestionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'question_bank_id' => QuestionBank::factory(),
            'situation' => $this->faker->sentence(),
            'reward_sparks' => $this->faker->numberBetween(1, 7),
        ];
    }
}
