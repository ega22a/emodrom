<?php

namespace Database\Factories;

use App\Models\Question;
use App\Models\QuestionBank;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuestionBank>
 */
class QuestionBankFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (QuestionBank $bank): void {
            if ($bank->questions()->doesntExist()) {
                Question::factory()->count(5)->for($bank)->create();
            }
        });
    }
}
