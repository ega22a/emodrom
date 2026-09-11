<?php

namespace App\Events;

use App\Data\RevealResultData;
use App\Models\Lobby;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class RoundRevealed implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(private Lobby $lobby, public RevealResultData $result) {}

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [new Channel("lobby.{$this->lobby->code}")];
    }

    public function broadcastAs(): string
    {
        return 'round.revealed';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return ['result' => $this->result->toArray()];
    }
}
