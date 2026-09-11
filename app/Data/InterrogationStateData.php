<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class InterrogationStateData extends Data
{
    /**
     * @param  DataCollection<int, InterrogationEntryData>  $entries
     */
    public function __construct(
        public int $roundNumber,
        public DataCollection $entries,
        public bool $finished,
    ) {}
}
