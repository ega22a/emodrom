<?php

namespace App\Support;

use App\Data\AvatarOptionData;
use Illuminate\Support\Collection;

final class Avatars
{
    /**
     * Lucide icon slugs used as player avatars. At least 50 are required so
     * every player at the table can pick a visually distinct one.
     *
     * @var list<string>
     */
    private const array ICONS = [
        'cat', 'dog', 'rabbit', 'bird', 'fish', 'bug', 'squirrel', 'turtle',
        'snail', 'shell', 'feather', 'paw-print', 'smile', 'laugh', 'ghost',
        'skull', 'bot', 'drama', 'sparkles', 'crown', 'glasses', 'sun',
        'moon', 'star', 'flame', 'zap', 'heart', 'gem', 'rocket', 'anchor',
        'umbrella', 'snowflake', 'flower', 'flower-2', 'leaf', 'trees',
        'cloud', 'rainbow', 'apple', 'cherry', 'banana', 'grape', 'citrus',
        'carrot', 'pizza', 'cookie', 'ice-cream-cone', 'donut', 'coffee',
        'cake', 'guitar', 'music-2', 'gamepad-2', 'dice-5', 'puzzle',
        'palette', 'paintbrush', 'camera', 'compass', 'map', 'car', 'plane',
        'sailboat', 'train-front', 'bus', 'tent', 'mountain', 'wand-2',
        'award', 'medal', 'trophy', 'target', 'party-popper',
    ];

    /**
     * Tailwind color tokens cycled across the icons above so the avatar grid
     * reads as varied and colorful, matching the game's card art style.
     *
     * @var list<string>
     */
    private const array PALETTE = [
        'amber', 'orange', 'red', 'rose', 'pink', 'fuchsia', 'purple',
        'violet', 'indigo', 'blue', 'sky', 'cyan', 'teal', 'emerald',
        'green', 'lime',
    ];

    /**
     * @return Collection<int, AvatarOptionData>
     */
    public static function options(): Collection
    {
        return collect(self::ICONS)->values()->map(fn (string $icon, int $index): AvatarOptionData => new AvatarOptionData(
            key: $icon,
            icon: $icon,
            color: self::PALETTE[$index % count(self::PALETTE)],
            label: ucfirst(str_replace('-', ' ', $icon)),
        ));
    }

    /**
     * @return list<string>
     */
    public static function keys(): array
    {
        return self::ICONS;
    }

    public static function isValid(string $key): bool
    {
        return in_array($key, self::ICONS, true);
    }

    public static function color(string $key): string
    {
        return self::options()->firstWhere('key', $key)?->color ?? self::PALETTE[0];
    }
}
