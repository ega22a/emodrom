<?php

namespace App\Events;

use App\Data\PlayerData;
use App\Models\GameRound;
use App\Models\Lobby;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class ReaderReassigned implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public int $roundNumber;

    public PlayerData $reader;

    public function __construct(private Lobby $lobby, GameRound $round)
    {
        $round->loadMissing('reader');

        $this->roundNumber = $round->number;
        $this->reader = PlayerData::fromModel($round->reader);
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
        return 'reader.reassigned';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'roundNumber' => $this->roundNumber,
            'reader' => $this->reader->toArray(),
        ];
    }
}
