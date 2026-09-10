<?php

namespace Database\Seeders;

use App\Models\Emotion;
use Illuminate\Database\Seeder;

class EmotionSeeder extends Seeder
{
    /**
     * The six emotions every player starts with.
     *
     * The rest are earned mid-game: after a round, players who voted for the
     * emotion with the most votes each unlock one random new emotion from
     * the full pool.
     *
     * @var list<array{key: string, label: string, color: string, icon: string, is_base: bool}>
     */
    private const array EMOTIONS = [
        ['key' => 'shame', 'label' => 'Стыд', 'color' => 'amber', 'icon' => 'eye-off', 'is_base' => true],
        ['key' => 'sadness', 'label' => 'Грусть', 'color' => 'blue', 'icon' => 'cloud-rain', 'is_base' => true],
        ['key' => 'joy', 'label' => 'Радость', 'color' => 'yellow', 'icon' => 'sun', 'is_base' => true],
        ['key' => 'anger', 'label' => 'Гнев', 'color' => 'red', 'icon' => 'flame', 'is_base' => true],
        ['key' => 'fear', 'label' => 'Страх', 'color' => 'violet', 'icon' => 'ghost', 'is_base' => true],
        ['key' => 'interest', 'label' => 'Интерес', 'color' => 'teal', 'icon' => 'search', 'is_base' => true],

        ['key' => 'anxiety', 'label' => 'Беспокойство', 'color' => 'orange', 'icon' => 'zap', 'is_base' => false],
        ['key' => 'surprise', 'label' => 'Удивление', 'color' => 'pink', 'icon' => 'sparkles', 'is_base' => false],
        ['key' => 'guilt', 'label' => 'Вина', 'color' => 'stone', 'icon' => 'cloud-drizzle', 'is_base' => false],
        ['key' => 'irritation', 'label' => 'Раздражение', 'color' => 'rose', 'icon' => 'flame-kindling', 'is_base' => false],
        ['key' => 'calm', 'label' => 'Спокойствие', 'color' => 'emerald', 'icon' => 'leaf', 'is_base' => false],
        ['key' => 'delight', 'label' => 'Восторг', 'color' => 'fuchsia', 'icon' => 'party-popper', 'is_base' => false],
        ['key' => 'irony', 'label' => 'Ирония', 'color' => 'cyan', 'icon' => 'drama', 'is_base' => false],
        ['key' => 'admiration', 'label' => 'Восхищение', 'color' => 'indigo', 'icon' => 'star', 'is_base' => false],
        ['key' => 'apathy', 'label' => 'Апатия', 'color' => 'gray', 'icon' => 'moon', 'is_base' => false],
    ];

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        foreach (self::EMOTIONS as $order => $emotion) {
            Emotion::updateOrCreate(
                ['key' => $emotion['key']],
                [...$emotion, 'sort_order' => $order]
            );
        }
    }
}
