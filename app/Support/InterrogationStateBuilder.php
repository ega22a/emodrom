<?php

namespace App\Support;

use App\Data\InterrogationEntryData;
use App\Data\InterrogationStateData;
use App\Models\GameRound;
use App\Models\Interrogation;
use Spatie\LaravelData\DataCollection;

final class InterrogationStateBuilder
{
    public static function build(GameRound $round): InterrogationStateData
    {
        $entries = $round->interrogations()->with('player')->orderBy('id')->get();

        return new InterrogationStateData(
            roundNumber: $round->number,
            entries: InterrogationEntryData::collect(
                $entries->map(fn (Interrogation $entry) => InterrogationEntryData::fromModel(
                    $entry,
                    isCurrent: $entry->player_id === $round->current_interrogation_player_id,
                )),
                DataCollection::class
            ),
            finished: $entries->isNotEmpty() && $round->current_interrogation_player_id === null,
        );
    }
}
