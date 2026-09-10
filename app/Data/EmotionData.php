<?php

namespace App\Data;

use App\Models\Emotion;
use Spatie\LaravelData\Data;

class EmotionData extends Data
{
    public function __construct(
        public int $id,
        public string $key,
        public string $label,
        public string $color,
        public string $icon,
        public bool $isBase,
    ) {}

    public static function fromModel(Emotion $emotion): self
    {
        return new self(
            id: $emotion->id,
            key: $emotion->key,
            label: $emotion->label,
            color: $emotion->color,
            icon: $emotion->icon,
            isBase: $emotion->is_base,
        );
    }
}
