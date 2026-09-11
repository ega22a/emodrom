<?php

namespace App\Support;

use App\Enums\RoundStatus;
use App\Http\Resources\GameRoundResource;
use App\Http\Resources\LobbyResource;
use App\Models\Lobby;

/**
 * The view-model shared by the host panel and the public display screen —
 * everything in it is fine for both audiences to see (unlike the reader's
 * private emotion pick, which never appears here before a reveal).
 */
final class LobbyStateBuilder
{
    /**
     * @return array<string, mixed>
     */
    public static function build(Lobby $lobby): array
    {
        $lobby->loadMissing('players');
        $round = $lobby->currentRound()?->load(['reader', 'question']);
        $revealedRound = $round === null
            ? $lobby->rounds()->where('status', RoundStatus::Revealed)->latest('number')->first()
            : null;

        return [
            'lobby' => LobbyResource::make($lobby)->resolve(),
            'round' => $round ? GameRoundResource::make($round)->resolve() : null,
            'lastResult' => $revealedRound ? RevealResultBuilder::build($revealedRound)->toArray() : null,
            'interrogation' => $revealedRound && $lobby->interrogation_enabled
                ? InterrogationStateBuilder::build($revealedRound)->toArray()
                : null,
        ];
    }
}
