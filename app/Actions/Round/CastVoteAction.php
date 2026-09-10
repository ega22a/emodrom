<?php

namespace App\Actions\Round;

use App\Data\EmotionData;
use App\Data\EmotionVoteStatData;
use App\Data\VoterData;
use App\Data\VoteStatsData;
use App\Events\VoteStatsUpdated;
use App\Exceptions\GameException;
use App\Models\Emotion;
use App\Models\GameRound;
use App\Models\Player;
use App\Models\Vote;
use Spatie\LaravelData\DataCollection;

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

        VoteStatsUpdated::dispatch($round->lobby, $this->buildStats($round));

        return $vote;
    }

    private function buildStats(GameRound $round): VoteStatsData
    {
        $votes = $round->votes()->with(['emotion', 'player'])->get();
        $totalPlayers = $round->lobby->players()->count();

        $stats = $votes
            ->groupBy('emotion_id')
            ->map(fn ($votesForEmotion) => new EmotionVoteStatData(
                emotion: EmotionData::fromModel($votesForEmotion->first()->emotion),
                votesCount: $votesForEmotion->count(),
                voters: VoterData::collect(
                    $votesForEmotion->map(fn (Vote $vote) => VoterData::fromModel($vote->player)),
                    DataCollection::class
                ),
            ))
            ->sortByDesc('votesCount')
            ->values();

        return new VoteStatsData(
            roundNumber: $round->number,
            stats: EmotionVoteStatData::collect($stats, DataCollection::class),
            totalPlayers: $totalPlayers,
            totalVotes: $votes->count(),
        );
    }
}
