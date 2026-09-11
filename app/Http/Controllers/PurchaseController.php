<?php

namespace App\Http\Controllers;

use App\Actions\Player\PurchaseEmotionAction;
use App\Http\Resources\EmotionResource;
use App\Models\Lobby;
use App\Services\PlayerSessionService;
use Illuminate\Http\JsonResponse;

class PurchaseController extends Controller
{
    public function store(
        Lobby $lobby,
        PlayerSessionService $sessions,
        PurchaseEmotionAction $action,
    ): JsonResponse {
        $player = $sessions->resolve($lobby);

        if (! $player) {
            abort(403, 'Вы ещё не присоединились к этому лобби.');
        }

        $emotion = $action->execute($player);

        return response()->json([
            'emotion' => EmotionResource::make($emotion)->resolve(),
            'sparksBalance' => $player->fresh()->sparks_balance,
        ], 201);
    }
}
