<?php

namespace App\Support;

use App\Models\Lobby;

final class LobbyCodeGenerator
{
    /**
     * Characters chosen to avoid visual ambiguity (no 0/O, 1/I/L) since
     * players read this code off a projector screen from across a room.
     */
    private const string ALPHABET = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

    private const int LENGTH = 6;

    public function unique(): string
    {
        do {
            $code = $this->random();
        } while (Lobby::where('code', $code)->exists());

        return $code;
    }

    public function random(): string
    {
        $alphabet = self::ALPHABET;
        $max = strlen($alphabet) - 1;

        $code = '';

        for ($i = 0; $i < self::LENGTH; $i++) {
            $code .= $alphabet[random_int(0, $max)];
        }

        return $code;
    }
}
