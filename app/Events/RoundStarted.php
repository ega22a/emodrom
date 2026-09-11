<?php

namespace App\Events;

use App\Data\PlayerData;
use App\Data\QuestionData;
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

    public PlayerData $reader;

    public QuestionData $question;

    public bool $mirrorEnabled;

    public bool $cocktailEnabled;

    public function __construct(private Lobby $lobby, GameRound $round)
    {
        $round->loadMissing(['reader', 'question']);

        $this->roundNumber = $round->number;
        $this->reader = PlayerData::fromModel($round->reader);
        $this->question = QuestionData::fromModel($round->question);
        $this->mirrorEnabled = $round->mirror_enabled;
        $this->cocktailEnabled = $round->cocktail_enabled;
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
        return [
            'roundNumber' => $this->roundNumber,
            'reader' => $this->reader->toArray(),
            'question' => $this->question->toArray(),
            'mirrorEnabled' => $this->mirrorEnabled,
            'cocktailEnabled' => $this->cocktailEnabled,
        ];
    }
}
