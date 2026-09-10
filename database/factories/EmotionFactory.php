<?php

namespace Database\Factories;

use App\Models\Emotion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Emotion>
 */
class EmotionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $word = $this->faker->unique()->word();

        return [
            'key' => $word,
            'label' => ucfirst($word),
            'color' => $this->faker->randomElement(['amber', 'blue', 'rose', 'teal', 'violet']),
            'icon' => 'sparkles',
            'is_base' => false,
            'sort_order' => 0,
        ];
    }

    public function base(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_base' => true,
        ]);
    }
}
