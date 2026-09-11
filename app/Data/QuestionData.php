<?php

namespace App\Data;

use App\Models\Question;
use Spatie\LaravelData\Data;

class QuestionData extends Data
{
    public function __construct(
        public int $id,
        public string $situation,
        public int $rewardSparks,
    ) {}

    public static function fromModel(Question $question): self
    {
        return new self(
            id: $question->id,
            situation: $question->situation,
            rewardSparks: $question->reward_sparks,
        );
    }
}
