<?php

namespace App\Events;

use App\Models\GameRound;
use App\Models\Lobby;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class RoundStarted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public int $roundNumber;

    public function __construct(private Lobby $lobby, GameRound $round)
    {
        $this->roundNumber = $round->number;
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
        return 'round.started';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return ['roundNumber' => $this->roundNumber];
    }
}
