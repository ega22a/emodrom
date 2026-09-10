<?php

namespace App\Support;

use App\Data\EmotionData;
use App\Data\EmotionVoteStatData;
use App\Data\VoterData;
use App\Data\VoteStatsData;
use App\Models\GameRound;
use App\Models\Vote;
use Spatie\LaravelData\DataCollection;

final class VoteStatsBuilder
{
    public static function build(GameRound $round): VoteStatsData
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
