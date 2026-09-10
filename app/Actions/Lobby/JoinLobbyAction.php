<?php

namespace App\Actions\Lobby;

use App\Data\JoinLobbyData;
use App\Events\PlayerJoined;
use App\Exceptions\GameException;
use App\Models\Emotion;
use App\Models\Lobby;
use App\Models\Player;

class JoinLobbyAction
{
    public function execute(Lobby $lobby, JoinLobbyData $data): Player
    {
        if (! $lobby->isOpen()) {
            throw GameException::lobbyClosed();
        }

        $player = $lobby->players()->create([
            'name' => $data->name,
            'avatar' => $data->avatar,
            'joined_at' => now(),
        ]);

        $player->emotions()->attach(
            Emotion::query()->where('is_base', true)->pluck('id')
        );

        PlayerJoined::dispatch($lobby, $player);

        return $player;
    }
}
