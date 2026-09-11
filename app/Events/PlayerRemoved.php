<?php

namespace App\Events;

use App\Models\Lobby;
use App\Models\Player;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class PlayerRemoved implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public int $playerId;

    public function __construct(private Lobby $lobby, Player $player)
    {
        $this->playerId = $player->id;
    }

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [new Channel("lobby.{$this->lobby->code}")];
    }

    public function broadcastAs(): string
    {
        return 'player.removed';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return ['playerId' => $this->playerId];
    }
}
