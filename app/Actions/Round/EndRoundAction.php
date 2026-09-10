<?php

namespace App\Actions\Round;

use App\Data\EmotionData;
use App\Data\RoundResultData;
use App\Enums\RoundStatus;
use App\Events\RoundEnded;
use App\Models\Emotion;
use App\Models\GameRound;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EndRoundAction
{
    /**
     * Tally the round's votes, crown a winning emotion, and hand every
     * player who voted for it one random new emotion they don't have yet.
     */
    public function execute(GameRound $round): void
    {
        $voteCounts = $round->votes()
            ->select('emotion_id', DB::raw('count(*) as votes_count'))
            ->groupBy('emotion_id')
            ->orderByDesc('votes_count')
            ->get();

        $winningEmotionId = $this->pickWinningEmotionId($voteCounts);
        $rewardedPlayerIds = $winningEmotionId
            ? $this->rewardWinningVoters($round, $winningEmotionId)
            : [];

        $round->update([
            'status' => RoundStatus::Completed,
            'winning_emotion_id' => $winningEmotionId,
            'ended_at' => now(),
        ]);

        RoundEnded::dispatch($round->lobby, new RoundResultData(
            roundNumber: $round->number,
            winningEmotion: $winningEmotionId
                ? EmotionData::fromModel(Emotion::findOrFail($winningEmotionId))
                : null,
            rewardedPlayerIds: $rewardedPlayerIds,
        ));
    }

    /**
     * @param  Collection<int, object{emotion_id: int, votes_count: int}>  $voteCounts
     */
    private function pickWinningEmotionId($voteCounts): ?int
    {
        if ($voteCounts->isEmpty()) {
            return null;
        }

        $topCount = $voteCounts->first()->votes_count;

        return $voteCounts
            ->where('votes_count', $topCount)
            ->pluck('emotion_id')
            ->random();
    }

    /**
     * @return list<int>
     */
    private function rewardWinningVoters(GameRound $round, int $winningEmotionId): array
    {
        $winners = $round->votes()
            ->where('emotion_id', $winningEmotionId)
            ->with('player.emotions')
            ->get();

        $rewardedPlayerIds = [];

        foreach ($winners as $vote) {
            $player = $vote->player;
            $unlockedEmotionIds = $player->emotions->pluck('id');

            $reward = Emotion::query()
                ->whereNotIn('id', $unlockedEmotionIds)
                ->inRandomOrder()
                ->first();

            if (! $reward) {
                continue;
            }

            $player->emotions()->attach($reward->id, [
                'unlocked_in_game_round_id' => $round->id,
            ]);

            $rewardedPlayerIds[] = $player->id;
        }

        return $rewardedPlayerIds;
    }
}
