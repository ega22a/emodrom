<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class AvatarOptionData extends Data
{
    public function __construct(
        public string $key,
        public string $icon,
        public string $color,
        public string $label,
    ) {}
}
