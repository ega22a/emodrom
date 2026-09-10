<?php

namespace App\Events;

use App\Data\RoundResultData;
use App\Models\Lobby;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class RoundEnded implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(public Lobby $lobby, public RoundResultData $result) {}

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [new Channel("lobby.{$this->lobby->code}")];
    }

    public function broadcastAs(): string
    {
        return 'round.ended';
    }
}
