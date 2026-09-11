<?php

namespace App\Data;

use Spatie\LaravelData\Data;

/**
 * Broadcast while a round is still voting: how many players have answered,
 * with no breakdown by emotion — results stay hidden until the host reveals
 * them.
 */
class VoteProgressData extends Data
{
    public function __construct(
        public int $roundNumber,
        public int $votedCount,
        public int $totalVoters,
    ) {}
}
