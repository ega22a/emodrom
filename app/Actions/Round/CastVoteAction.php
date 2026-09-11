<?php

namespace App\Actions\Round;

use App\Data\VoteProgressData;
use App\Events\VoteProgressUpdated;
use App\Exceptions\GameException;
use App\Models\Emotion;
use App\Models\GameRound;
use App\Models\Player;
use App\Models\Vote;

class CastVoteAction
{
    public function execute(GameRound $round, Player $player, Emotion $emotion): Vote
    {
        if (! $round->isVoting()) {
            throw GameException::roundNotActive();
        }

        if ($round->reader_player_id === $player->id) {
            throw GameException::readerCannotVote();
        }

        if (! $player->hasUnlocked($emotion)) {
            throw GameException::emotionNotUnlocked();
        }

        if ($player->hasVotedIn($round)) {
            throw GameException::alreadyVoted();
        }

        $vote = $round->votes()->create([
            'player_id' => $player->id,
            'emotion_id' => $emotion->id,
        ]);

        $totalVoters = $round->lobby->players()->count() - 1;

        VoteProgressUpdated::dispatch($round->lobby, new VoteProgressData(
            roundNumber: $round->number,
            votedCount: $round->votes()->count(),
            totalVoters: max($totalVoters, 0),
        ));

        return $vote;
    }
}
