<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class RoundResultData extends Data
{
    /**
     * @param  list<int>  $rewardedPlayerIds  Players who voted with the majority and unlocked a new emotion.
     */
    public function __construct(
        public int $roundNumber,
        public ?EmotionData $winningEmotion,
        public array $rewardedPlayerIds,
    ) {}
}
