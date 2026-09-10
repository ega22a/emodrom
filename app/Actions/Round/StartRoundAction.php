<?php

namespace App\Actions\Round;

use App\Enums\RoundStatus;
use App\Events\RoundStarted;
use App\Exceptions\GameException;
use App\Models\GameRound;
use App\Models\Lobby;
use Illuminate\Support\Facades\DB;

class StartRoundAction
{
    public function __construct(private EndRoundAction $endRound) {}

    /**
     * Finalize the lobby's current round (if any) and open the next one.
     * Finalizing is where players who backed the winning emotion earn
     * their new emotion, so it always happens right before a new round
     * starts, per the game's rules.
     */
    public function execute(Lobby $lobby): GameRound
    {
        if (! $lobby->isOpen()) {
            throw GameException::lobbyClosed();
        }

        return DB::transaction(function () use ($lobby): GameRound {
            $currentRound = $lobby->currentRound();

            if ($currentRound) {
                $this->endRound->execute($currentRound);
            }

            $round = $lobby->rounds()->create([
                'number' => ($lobby->rounds()->max('number') ?? 0) + 1,
                'status' => RoundStatus::Active,
                'started_at' => now(),
            ]);

            RoundStarted::dispatch($lobby, $round);

            return $round;
        });
    }
}
