<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class RevealResultData extends Data
{
    /**
     * @param  DataCollection<int, EmotionVoteStatData>  $groups  Ordered ascending by votesCount — the reader's own emotion (and whoever matched it) is revealed last for suspense.
     * @param  DataCollection<int, VoterData>  $abstainers  Players who didn't answer at all.
     * @param  list<int>  $rewardedPlayerIds  Who received rewardSparks — the matched voters, or just the reader if nobody matched.
     * @param  DataCollection<int, VoterData>  $interrogationTargets  Players from the lowest non-matching group, called up one at a time if the lobby has interrogation enabled.
     */
    public function __construct(
        public int $roundNumber,
        public QuestionData $question,
        public PlayerData $reader,
        public EmotionData $readerEmotion,
        public DataCollection $groups,
        public DataCollection $abstainers,
        public int $rewardSparks,
        public array $rewardedPlayerIds,
        public bool $readerTookReward,
        public bool $interrogationEnabled,
        public DataCollection $interrogationTargets,
        public bool $mirrorEnabled,
        public bool $cocktailEnabled,
    ) {}
}
