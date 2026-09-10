<?php

namespace App\Http\Controllers;

use App\Actions\Round\CastVoteAction;
use App\Exceptions\GameException;
use App\Http\Requests\CastVoteRequest;
use App\Models\Emotion;
use App\Models\Lobby;
use App\Services\PlayerSessionService;
use Illuminate\Http\JsonResponse;

class VoteController extends Controller
{
    public function store(
        CastVoteRequest $request,
        Lobby $lobby,
        PlayerSessionService $sessions,
        CastVoteAction $action,
    ): JsonResponse {
        $player = $sessions->resolve($lobby);

        if (! $player) {
            abort(403, 'Вы ещё не присоединились к этому лобби.');
        }

        $round = $lobby->currentRound();

        if (! $round) {
            throw GameException::roundNotActive();
        }

        $emotion = Emotion::findOrFail($request->integer('emotion_id'));

        $action->execute($round, $player, $emotion);

        return response()->json(['votedEmotionId' => $emotion->id], 201);
    }
}
