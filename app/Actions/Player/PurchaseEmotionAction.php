<?php

namespace App\Actions\Player;

use App\Enums\EmotionSet;
use App\Exceptions\GameException;
use App\Models\Emotion;
use App\Models\Player;
use Illuminate\Support\Facades\DB;

class PurchaseEmotionAction
{
    public const int COST_IN_SPARKS = 3;

    public function execute(Player $player): Emotion
    {
        if (! $player->canAfford(self::COST_IN_SPARKS)) {
            throw GameException::notEnoughSparks();
        }

        return DB::transaction(function () use ($player): Emotion {
            $unlockedIds = $player->emotions()->pluck('emotions.id');

            $pool = Emotion::query()
                ->whereNotIn('id', $unlockedIds)
                ->when(
                    $player->lobby->emotion_set === EmotionSet::Classic,
                    fn ($query) => $query->where('is_extended', false),
                )
                ->inRandomOrder()
                ->first();

            if (! $pool) {
                throw GameException::noEmotionsLeftToBuy();
            }

            $player->emotions()->attach($pool->id);
            $player->decrement('sparks_balance', self::COST_IN_SPARKS);

            return $pool;
        });
    }
}
