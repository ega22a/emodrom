<?php

namespace App\Support;

use App\Data\EmotionData;
use App\Data\EmotionVoteStatData;
use App\Data\PlayerData;
use App\Data\QuestionData;
use App\Data\RevealResultData;
use App\Data\VoterData;
use App\Models\GameRound;
use App\Models\Vote;
use Spatie\LaravelData\DataCollection;

/**
 * Rebuilds a revealed round's result purely from what's already persisted
 * (votes, the reader's emotion, interrogation rows) — nothing here mutates
 * state, so it's safe to call again whenever a screen needs to resync.
 */
final class RevealResultBuilder
{
    public static function build(GameRound $round): RevealResultData
    {
        $round->loadMissing(['question', 'reader', 'readerEmotion', 'lobby.activePlayers', 'votes.emotion', 'votes.player', 'interrogations.player']);

        $votes = $round->votes;

        $groups = $votes
            ->groupBy('emotion_id')
            ->map(fn ($votesForEmotion) => new EmotionVoteStatData(
                emotion: EmotionData::fromModel($votesForEmotion->first()->emotion),
                votesCount: $votesForEmotion->count(),
                matchedReader: $votesForEmotion->first()->emotion_id === $round->reader_emotion_id,
                voters: VoterData::collect(
                    $votesForEmotion->map(fn (Vote $vote) => VoterData::fromModel($vote->player)),
                    DataCollection::class
                ),
            ))
            ->sortBy('votesCount')
            ->values();

        $votedPlayerIds = $votes->pluck('player_id');
        $abstainers = $round->lobby->activePlayers
            ->reject(fn ($player) => $player->id === $round->reader_player_id || $votedPlayerIds->contains($player->id));

        $matchedVoterIds = $votes
            ->filter(fn (Vote $vote) => $vote->matches($round->reader_emotion_id))
            ->pluck('player_id')
            ->values()
            ->all();

        $readerTookReward = count($matchedVoterIds) === 0;

        return new RevealResultData(
            roundNumber: $round->number,
            question: QuestionData::fromModel($round->question),
            reader: PlayerData::fromModel($round->reader),
            readerEmotion: EmotionData::fromModel($round->readerEmotion),
            groups: EmotionVoteStatData::collect($groups, DataCollection::class),
            abstainers: VoterData::collect(
                $abstainers->map(fn ($player) => VoterData::fromModel($player))->values(),
                DataCollection::class
            ),
            rewardSparks: $round->question->reward_sparks,
            rewardedPlayerIds: $readerTookReward ? [$round->reader_player_id] : $matchedVoterIds,
            readerTookReward: $readerTookReward,
            interrogationEnabled: $round->lobby->interrogation_enabled,
            interrogationTargets: VoterData::collect(
                $round->interrogations->map(fn ($interrogation) => VoterData::fromModel($interrogation->player)),
                DataCollection::class
            ),
            mirrorEnabled: $round->mirror_enabled,
            cocktailEnabled: $round->cocktail_enabled,
        );
    }
}
