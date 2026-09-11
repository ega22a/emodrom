<?php

namespace App\Actions\Round;

use App\Events\RoundCancelled;
use App\Exceptions\GameException;
use App\Models\GameRound;

class CancelRoundAction
{
    /**
     * Voids the round entirely — it never happened: no sparks were paid out
     * yet (that only occurs at reveal), so there's nothing to undo besides
     * the round and its votes, and it doesn't count against the round limit.
     */
    public function execute(GameRound $round): void
    {
        if (! $round->isVoting()) {
            throw GameException::roundAlreadyRevealed();
        }

        $lobby = $round->lobby;
        $roundNumber = $round->number;

        $round->delete();

        RoundCancelled::dispatch($lobby, $roundNumber);
    }
}
