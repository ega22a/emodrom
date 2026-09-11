<?php

namespace App\Actions\Lobby;

use App\Enums\LobbyStatus;
use App\Events\LobbyClosed;
use App\Models\Lobby;

class CloseLobbyAction
{
    /**
     * Closing doesn't need to finalize an in-progress round: reveals are
     * host-triggered explicitly now, so a round left mid-voting when the
     * lobby closes simply never gets revealed.
     */
    public function execute(Lobby $lobby): void
    {
        $lobby->update([
            'status' => LobbyStatus::Closed,
            'closed_at' => now(),
        ]);

        LobbyClosed::dispatch($lobby);
    }
}
