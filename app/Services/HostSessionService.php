<?php

namespace App\Services;

use App\Models\Lobby;
use Illuminate\Support\Facades\Cookie;

/**
 * Identifies the phone that created a lobby. The host is not a Player row —
 * they never vote or read a card — just a device holding the lobby's
 * host_token in a cookie, set once at creation time.
 */
class HostSessionService
{
    private const int REMEMBER_FOR_MINUTES = 60 * 12;

    public function remember(Lobby $lobby): void
    {
        // Cookie::queue() forwards its args to make() via array_values(),
        // which discards named-argument keys — pass positionally or the
        // values silently shift into the wrong parameters (path, domain, …).
        Cookie::queue(
            $this->cookieName($lobby),
            $lobby->host_token,
            self::REMEMBER_FOR_MINUTES,
            null,
            null,
            null,
            true,
            false,
            'lax',
        );
    }

    public function isHost(Lobby $lobby): bool
    {
        $token = request()->cookie($this->cookieName($lobby));

        return $token !== null && hash_equals($lobby->host_token, $token);
    }

    private function cookieName(Lobby $lobby): string
    {
        return "emodrom_host_{$lobby->code}";
    }
}
