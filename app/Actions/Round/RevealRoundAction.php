<?php

namespace App\Actions\Round;

use App\Enums\RoundStatus;
use App\Events\InterrogationUpdated;
use App\Events\RoundRevealed;
use App\Exceptions\GameException;
use App\Models\GameRound;
use App\Models\Interrogation;
use App\Models\Player;
use App\Models\Vote;
use App\Support\InterrogationStateBuilder;
use App\Support\RevealResultBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RevealRoundAction
{
    /**
     * Tally the round's votes against the reader's own (until now private)
     * choice, pay out sparks, and — if the lobby has interrogation enabled —
     * line up the players from the least-picked non-matching emotion to be
     * questioned one at a time.
     */
    public function execute(GameRound $round): void
    {
        if (! $round->isVoting()) {
            throw GameException::roundAlreadyRevealed();
        }

        if (! $round->readerHasChosen()) {
            throw GameException::readerHasNotChosenYet();
        }

        DB::transaction(function () use ($round): void {
            $votes = $round->votes()->with('player')->get();

            $matchedVotes = $votes->where('emotion_id', $round->reader_emotion_id);

            if ($matchedVotes->isNotEmpty()) {
                $this->awardSparks($matchedVotes->pluck('player')->unique('id'), $round->question->reward_sparks);
            } else {
                $this->awardSparks(collect([$round->reader]), $round->question->reward_sparks);
            }

            $firstInterrogationPlayerId = null;

            if ($round->lobby->interrogation_enabled) {
                $firstInterrogationPlayerId = $this->queueInterrogation($round, $votes);
            }

            $round->update([
                'status' => RoundStatus::Revealed,
                'revealed_at' => now(),
                'current_interrogation_player_id' => $firstInterrogationPlayerId,
            ]);
        });

        RoundRevealed::dispatch($round->lobby, RevealResultBuilder::build($round->fresh()));

        if ($round->lobby->interrogation_enabled) {
            InterrogationUpdated::dispatch($round->lobby, InterrogationStateBuilder::build($round->fresh()));
        }
    }

    /**
     * @param  Collection<int, Player>  $players
     */
    private function awardSparks($players, int $sparks): void
    {
        foreach ($players as $player) {
            $player->increment('sparks_balance', $sparks);
        }
    }

    /**
     * @param  Collection<int, Vote>  $votes
     */
    private function queueInterrogation(GameRound $round, $votes): ?int
    {
        $nonMatching = $votes->where('emotion_id', '!=', $round->reader_emotion_id);

        if ($nonMatching->isEmpty()) {
            return null;
        }

        $countsByEmotion = $nonMatching->groupBy('emotion_id')->map->count();
        $lowestCount = $countsByEmotion->min();
        $targetEmotionIds = $countsByEmotion->filter(fn (int $count) => $count === $lowestCount)->keys();

        $targets = $nonMatching->whereIn('emotion_id', $targetEmotionIds);

        foreach ($targets as $vote) {
            Interrogation::create([
                'game_round_id' => $round->id,
                'player_id' => $vote->player_id,
            ]);
        }

        return $targets->first()?->player_id;
    }
}
