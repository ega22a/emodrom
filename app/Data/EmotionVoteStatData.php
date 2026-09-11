<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class EmotionVoteStatData extends Data
{
    /**
     * @param  DataCollection<int, VoterData>  $voters
     */
    public function __construct(
        public EmotionData $emotion,
        public int $votesCount,
        public bool $matchedReader,
        public DataCollection $voters,
    ) {}
}
