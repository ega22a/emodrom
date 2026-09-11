<?php

namespace App\Actions\Round;

use App\Enums\RoundStatus;
use App\Events\RoundStarted;
use App\Exceptions\GameException;
use App\Models\GameRound;
use App\Models\Lobby;
use App\Support\ReaderRotation;
use Illuminate\Support\Facades\DB;

class StartRoundAction
{
    public function execute(Lobby $lobby): GameRound
    {
        if (! $lobby->isOpen()) {
            throw GameException::lobbyClosed();
        }

        if (! $lobby->isSessionConfigured()) {
            throw GameException::sessionNotConfigured();
        }

        if ($lobby->hasReachedRoundLimit()) {
            throw GameException::roundLimitReached();
        }

        return DB::transaction(function () use ($lobby): GameRound {
            $reader = ReaderRotation::next($lobby);
            $question = $lobby->questionBank->questions()->inRandomOrder()->firstOrFail();

            $round = $lobby->rounds()->create([
                'number' => ($lobby->rounds()->max('number') ?? 0) + 1,
                'status' => RoundStatus::Voting,
                'question_id' => $question->id,
                'reader_player_id' => $reader->id,
                'started_at' => now(),
            ]);

            RoundStarted::dispatch($lobby, $round);

            return $round;
        });
    }
}
