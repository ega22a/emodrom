<?php

namespace App\Actions\Round;

use App\Events\ReaderReassigned;
use App\Exceptions\GameException;
use App\Models\GameRound;
use App\Models\Player;
use Illuminate\Support\Facades\DB;

class ReassignReaderAction
{
    public function execute(GameRound $round, Player $newReader): GameRound
    {
        if (! $round->isVoting()) {
            throw GameException::roundAlreadyRevealed();
        }

        if ($newReader->lobby_id !== $round->lobby_id) {
            throw GameException::notTheReader();
        }

        DB::transaction(function () use ($round, $newReader): void {
            // The new reader can't also have a standing vote in this round.
            $round->votes()->where('player_id', $newReader->id)->delete();

            $round->update([
                'reader_player_id' => $newReader->id,
                'reader_emotion_id' => null,
            ]);
        });

        $round = $round->fresh();

        ReaderReassigned::dispatch($round->lobby, $round);

        return $round;
    }
}
