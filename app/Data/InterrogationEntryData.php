<?php

namespace App\Data;

use App\Models\Interrogation;
use Spatie\LaravelData\Data;

/**
 * Unlike vote tallies, interrogation is public and verbal — the host calls
 * the player up by name — so this carries the full PlayerData, not the
 * anonymized VoterData used for reveal groups.
 */
class InterrogationEntryData extends Data
{
    public function __construct(
        public int $id,
        public PlayerData $player,
        public ?int $sparksAwarded,
        public bool $isCurrent,
    ) {}

    public static function fromModel(Interrogation $interrogation, bool $isCurrent): self
    {
        return new self(
            id: $interrogation->id,
            player: PlayerData::fromModel($interrogation->player),
            sparksAwarded: $interrogation->sparks_awarded,
            isCurrent: $isCurrent,
        );
    }
}
