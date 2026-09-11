<?php

namespace App\Data;

use App\Models\Interrogation;
use Spatie\LaravelData\Data;

class InterrogationEntryData extends Data
{
    public function __construct(
        public VoterData $player,
        public ?int $sparksAwarded,
        public bool $isCurrent,
    ) {}

    public static function fromModel(Interrogation $interrogation, bool $isCurrent): self
    {
        return new self(
            player: VoterData::fromModel($interrogation->player),
            sparksAwarded: $interrogation->sparks_awarded,
            isCurrent: $isCurrent,
        );
    }
}
