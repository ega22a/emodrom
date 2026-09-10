<?php

namespace App\Actions\Round;

use App\Events\VoteStatsUpdated;
use App\Exceptions\GameException;
use App\Models\Emotion;
use App\Models\GameRound;
use App\Models\Player;
use App\Models\Vote;
use App\Support\VoteStatsBuilder;

class CastVoteAction
{
    public function execute(GameRound $round, Player $player, Emotion $emotion): Vote
    {
        if (! $round->isActive()) {
            throw GameException::roundNotActive();
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

        VoteStatsUpdated::dispatch($round->lobby, VoteStatsBuilder::build($round));

        return $vote;
    }
}
