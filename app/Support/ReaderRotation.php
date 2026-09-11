<?php

namespace App\Support;

use App\Models\Lobby;
use App\Models\Player;

/**
 * Picks who reads the next round's card. Players take turns in the order
 * they joined; a player who joins mid-session is simply slotted in wherever
 * their join time falls the next time the rotation wraps around.
 */
final class ReaderRotation
{
    public static function next(Lobby $lobby): Player
    {
        $players = $lobby->activePlayers()->orderBy('joined_at')->orderBy('id')->get();

        if ($players->isEmpty()) {
            throw new \RuntimeException('Cannot pick a reader for a lobby with no players.');
        }

        $lastReaderId = $lobby->rounds()->latest('number')->value('reader_player_id');

        if ($lastReaderId === null) {
            return $players->first();
        }

        $lastIndex = $players->search(fn (Player $player) => $player->id === $lastReaderId);

        if ($lastIndex === false) {
            return $players->first();
        }

        return $players[($lastIndex + 1) % $players->count()];
    }
}
