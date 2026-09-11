<?php

namespace Database\Seeders;

use App\Models\Emotion;
use Illuminate\Database\Seeder;

class EmotionSeeder extends Seeder
{
    /**
     * The six emotions every player starts with, regardless of the lobby's
     * emotion set.
     *
     * @var list<array{key: string, label: string, color: string, icon: string, is_base: bool, is_extended: bool}>
     */
    private const array BASE_EMOTIONS = [
        ['key' => 'shame', 'label' => 'Стыд', 'color' => 'amber', 'icon' => 'eye-off', 'is_base' => true, 'is_extended' => false],
        ['key' => 'sadness', 'label' => 'Грусть', 'color' => 'blue', 'icon' => 'cloud-rain', 'is_base' => true, 'is_extended' => false],
        ['key' => 'joy', 'label' => 'Радость', 'color' => 'yellow', 'icon' => 'sun', 'is_base' => true, 'is_extended' => false],
        ['key' => 'anger', 'label' => 'Гнев', 'color' => 'red', 'icon' => 'flame', 'is_base' => true, 'is_extended' => false],
        ['key' => 'fear', 'label' => 'Страх', 'color' => 'violet', 'icon' => 'ghost', 'is_base' => true, 'is_extended' => false],
        ['key' => 'interest', 'label' => 'Интерес', 'color' => 'teal', 'icon' => 'search', 'is_base' => true, 'is_extended' => false],
    ];

    /**
     * Earned mid-game in the "classic" emotion set: after a round is
     * revealed, players who matched the reader's emotion each unlock one
     * random new emotion from the pool they don't already have.
     *
     * @var list<array{key: string, label: string, color: string, icon: string, is_base: bool, is_extended: bool}>
     */
    private const array CLASSIC_EMOTIONS = [
        ['key' => 'anxiety', 'label' => 'Беспокойство', 'color' => 'orange', 'icon' => 'zap', 'is_base' => false, 'is_extended' => false],
        ['key' => 'surprise', 'label' => 'Удивление', 'color' => 'pink', 'icon' => 'sparkles', 'is_base' => false, 'is_extended' => false],
        ['key' => 'guilt', 'label' => 'Вина', 'color' => 'stone', 'icon' => 'cloud-drizzle', 'is_base' => false, 'is_extended' => false],
        ['key' => 'irritation', 'label' => 'Раздражение', 'color' => 'rose', 'icon' => 'flame-kindling', 'is_base' => false, 'is_extended' => false],
        ['key' => 'calm', 'label' => 'Спокойствие', 'color' => 'emerald', 'icon' => 'leaf', 'is_base' => false, 'is_extended' => false],
        ['key' => 'delight', 'label' => 'Восторг', 'color' => 'fuchsia', 'icon' => 'party-popper', 'is_base' => false, 'is_extended' => false],
        ['key' => 'irony', 'label' => 'Ирония', 'color' => 'cyan', 'icon' => 'drama', 'is_base' => false, 'is_extended' => false],
        ['key' => 'admiration', 'label' => 'Восхищение', 'color' => 'indigo', 'icon' => 'star', 'is_base' => false, 'is_extended' => false],
        ['key' => 'apathy', 'label' => 'Апатия', 'color' => 'gray', 'icon' => 'moon', 'is_base' => false, 'is_extended' => false],
    ];

    /**
     * Only in the pool when the host picks the "extended" emotion set.
     *
     * @var list<array{key: string, label: string, color: string, icon: string, is_base: bool, is_extended: bool}>
     */
    private const array EXTENDED_EMOTIONS = [
        ['key' => 'nostalgia', 'label' => 'Ностальгия', 'color' => 'sky', 'icon' => 'history', 'is_base' => false, 'is_extended' => true],
        ['key' => 'embarrassment', 'label' => 'Смущение', 'color' => 'pink', 'icon' => 'meh', 'is_base' => false, 'is_extended' => true],
        ['key' => 'pride', 'label' => 'Гордость', 'color' => 'amber', 'icon' => 'medal', 'is_base' => false, 'is_extended' => true],
        ['key' => 'disappointment', 'label' => 'Разочарование', 'color' => 'gray', 'icon' => 'frown', 'is_base' => false, 'is_extended' => true],
        ['key' => 'anticipation', 'label' => 'Предвкушение', 'color' => 'lime', 'icon' => 'hourglass', 'is_base' => false, 'is_extended' => true],
        ['key' => 'tenderness', 'label' => 'Умиление', 'color' => 'rose', 'icon' => 'heart', 'is_base' => false, 'is_extended' => true],
        ['key' => 'envy', 'label' => 'Зависть', 'color' => 'green', 'icon' => 'eye', 'is_base' => false, 'is_extended' => true],
        ['key' => 'relief', 'label' => 'Облегчение', 'color' => 'sky', 'icon' => 'wind', 'is_base' => false, 'is_extended' => true],
        ['key' => 'confusion', 'label' => 'Растерянность', 'color' => 'violet', 'icon' => 'help-circle', 'is_base' => false, 'is_extended' => true],
        ['key' => 'gratitude', 'label' => 'Благодарность', 'color' => 'amber', 'icon' => 'gift', 'is_base' => false, 'is_extended' => true],
        ['key' => 'excitement', 'label' => 'Азарт', 'color' => 'red', 'icon' => 'target', 'is_base' => false, 'is_extended' => true],
        ['key' => 'sympathy', 'label' => 'Сочувствие', 'color' => 'emerald', 'icon' => 'hand-heart', 'is_base' => false, 'is_extended' => true],
    ];

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $all = [...self::BASE_EMOTIONS, ...self::CLASSIC_EMOTIONS, ...self::EXTENDED_EMOTIONS];

        foreach ($all as $order => $emotion) {
            Emotion::updateOrCreate(
                ['key' => $emotion['key']],
                [...$emotion, 'sort_order' => $order]
            );
        }
    }
}
