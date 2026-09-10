<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class VoteStatsData extends Data
{
    /**
     * @param  DataCollection<int, EmotionVoteStatData>  $stats
     */
    public function __construct(
        public int $roundNumber,
        public DataCollection $stats,
        public int $totalPlayers,
        public int $totalVotes,
    ) {}
}
