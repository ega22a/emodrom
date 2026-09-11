<?php

namespace App\Actions\Lobby;

use App\Data\ConfigureSessionData;
use App\Exceptions\GameException;
use App\Models\Emotion;
use App\Models\Lobby;
use Illuminate\Support\Facades\DB;

/**
 * Applies (or re-applies) a lobby's session settings. The first call sets
 * up the game before any rounds are played; a later call — "start a new
 * session" from the host panel — resets every player's rounds/sparks/
 * emotions back to a fresh start, without needing them to rejoin.
 */
class ConfigureSessionAction
{
    public function execute(Lobby $lobby, ConfigureSessionData $data): Lobby
    {
        if (! $lobby->isOpen()) {
            throw GameException::lobbyClosed();
        }

        DB::transaction(function () use ($lobby, $data): void {
            $lobby->update([
                'question_bank_id' => $data->questionBankId,
                'round_limit' => $data->roundLimit,
                'interrogation_enabled' => $data->interrogationEnabled,
                'emotion_set' => $data->emotionSet,
            ]);

            $lobby->rounds()->delete();

            $baseEmotionIds = Emotion::query()->where('is_base', true)->pluck('id');

            foreach ($lobby->players as $player) {
                $player->emotions()->sync($baseEmotionIds);
                $player->update(['sparks_balance' => 0]);
            }
        });

        return $lobby->fresh();
    }
}
