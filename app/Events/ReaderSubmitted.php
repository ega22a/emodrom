<?php

namespace App\Events;

use App\Models\Lobby;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Lets the host screen flip "Показать результаты" from disabled to enabled
 * once the reader has privately chosen their emotion.
 */
class ReaderSubmitted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(private Lobby $lobby, public int $roundNumber) {}

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [new Channel("lobby.{$this->lobby->code}")];
    }

    public function broadcastAs(): string
    {
        return 'reader.submitted';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return ['roundNumber' => $this->roundNumber];
    }
}
