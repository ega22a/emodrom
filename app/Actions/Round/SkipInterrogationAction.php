<?php

namespace App\Actions\Round;

use App\Events\InterrogationUpdated;
use App\Models\GameRound;
use App\Support\InterrogationStateBuilder;

class SkipInterrogationAction
{
    /**
     * Ends the interrogation phase early: whoever hasn't been scored yet
     * gets 0 sparks, same as if the host had tapped "0" for each of them.
     */
    public function execute(GameRound $round): void
    {
        $round->interrogations()->whereNull('sparks_awarded')->update(['sparks_awarded' => 0]);
        $round->update(['current_interrogation_player_id' => null]);

        InterrogationUpdated::dispatch($round->lobby, InterrogationStateBuilder::build($round->fresh()));
    }
}
