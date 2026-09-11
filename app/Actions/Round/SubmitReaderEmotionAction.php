<?php

namespace App\Actions\Round;

use App\Events\ReaderSubmitted;
use App\Exceptions\GameException;
use App\Models\Emotion;
use App\Models\GameRound;
use App\Models\Player;

class SubmitReaderEmotionAction
{
    public function execute(GameRound $round, Player $reader, Emotion $emotion): GameRound
    {
        if (! $round->isVoting()) {
            throw GameException::roundNotActive();
        }

        if ($round->reader_player_id !== $reader->id) {
            throw GameException::notTheReader();
        }

        if ($round->readerHasChosen()) {
            throw GameException::readerAlreadyChose();
        }

        if (! $reader->hasUnlocked($emotion)) {
            throw GameException::emotionNotUnlocked();
        }

        $round->update(['reader_emotion_id' => $emotion->id]);

        ReaderSubmitted::dispatch($round->lobby, $round->number);

        return $round;
    }
}
