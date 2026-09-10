<?php

namespace App\Services;

use App\Models\Lobby;
use App\Models\Player;
use Illuminate\Support\Facades\Cookie;

/**
 * Identifies which player a phone belongs to, without requiring an account.
 * A player's id is remembered in a cookie scoped to that lobby's code, so the
 * same device can rejoin different lobbies over time as a different player.
 */
class PlayerSessionService
{
    private const int REMEMBER_FOR_MINUTES = 60 * 12;

    public function remember(Lobby $lobby, Player $player): void
    {
        // Cookie::queue() forwards its args to make() via array_values(),
        // which discards named-argument keys — pass positionally or the
        // values silently shift into the wrong parameters (path, domain, …).
        Cookie::queue(
            $this->cookieName($lobby),
            (string) $player->id,
            self::REMEMBER_FOR_MINUTES,
            null,
            null,
            null,
            true,
            false,
            'lax',
        );
    }

    public function resolve(Lobby $lobby): ?Player
    {
        $playerId = request()->cookie($this->cookieName($lobby));

        if (! $playerId) {
            return null;
        }

        return $lobby->players()->find($playerId);
    }

    private function cookieName(Lobby $lobby): string
    {
        return "kukarachas_player_{$lobby->code}";
    }
}
