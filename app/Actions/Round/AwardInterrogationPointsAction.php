<?php

namespace App\Actions\Round;

use App\Events\InterrogationUpdated;
use App\Exceptions\GameException;
use App\Models\GameRound;
use App\Models\Interrogation;
use App\Support\InterrogationStateBuilder;
use Illuminate\Support\Facades\DB;

class AwardInterrogationPointsAction
{
    /**
     * @param  int  $sparks  One of 0, 1, 3, 5 — enforced by the request layer.
     */
    public function execute(GameRound $round, Interrogation $interrogation, int $sparks): void
    {
        if ($round->current_interrogation_player_id !== $interrogation->player_id) {
            throw GameException::notCurrentInterrogationTarget();
        }

        DB::transaction(function () use ($round, $interrogation, $sparks): void {
            $interrogation->update(['sparks_awarded' => $sparks]);

            if ($sparks > 0) {
                $interrogation->player()->increment('sparks_balance', $sparks);
            }

            $next = $round->interrogations()
                ->whereNull('sparks_awarded')
                ->orderBy('id')
                ->first();

            $round->update(['current_interrogation_player_id' => $next?->player_id]);
        });

        InterrogationUpdated::dispatch($round->lobby, InterrogationStateBuilder::build($round->fresh()));
    }
}
