<?php

namespace App\Actions\Lobby;

use App\Events\PlayerRemoved;
use App\Exceptions\GameException;
use App\Models\Lobby;
use App\Models\Player;

class RemovePlayerAction
{
    /**
     * Soft-removes a player who left early. The row stays — past rounds
     * still reference it directly by id — but they drop out of the active
     * roster, so reader rotation, vote totals, and every "who's here" view
     * stop counting them from this point on.
     */
    public function execute(Lobby $lobby, Player $player): void
    {
        if ($player->lobby_id !== $lobby->id) {
            throw GameException::playerNotInLobby();
        }

        if (! $player->isActive()) {
            throw GameException::playerAlreadyRemoved();
        }

        $activeRound = $lobby->currentRound();

        if ($activeRound && $activeRound->reader_player_id === $player->id) {
            throw GameException::cannotRemoveCurrentReader();
        }

        $player->update(['removed_at' => now()]);

        PlayerRemoved::dispatch($lobby, $player);
    }
}
