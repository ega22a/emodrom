<?php

namespace App\Events;

use App\Data\PlayerData;
use App\Models\Lobby;
use App\Models\Player;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class PlayerJoined implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public PlayerData $player;

    public function __construct(private Lobby $lobby, Player $player)
    {
        $this->player = PlayerData::fromModel($player);
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
        return 'player.joined';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return ['player' => $this->player->toArray()];
    }
}
