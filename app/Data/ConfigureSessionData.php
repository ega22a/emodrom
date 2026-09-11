<?php

namespace App\Data;

use App\Enums\EmotionSet;
use Spatie\LaravelData\Data;

class ConfigureSessionData extends Data
{
    public function __construct(
        public int $questionBankId,
        public ?int $roundLimit,
        public bool $interrogationEnabled,
        public EmotionSet $emotionSet,
    ) {}
}
