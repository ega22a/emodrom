<?php

namespace App\Actions\Lobby;

use App\Actions\Round\EndRoundAction;
use App\Enums\LobbyStatus;
use App\Events\LobbyClosed;
use App\Models\Lobby;
use Illuminate\Support\Facades\DB;

class CloseLobbyAction
{
    public function __construct(private EndRoundAction $endRound) {}

    public function execute(Lobby $lobby): void
    {
        DB::transaction(function () use ($lobby): void {
            $currentRound = $lobby->currentRound();

            if ($currentRound) {
                $this->endRound->execute($currentRound);
            }

            $lobby->update([
                'status' => LobbyStatus::Closed,
                'closed_at' => now(),
            ]);
        });

        LobbyClosed::dispatch($lobby);
    }
}
