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
        $secondaryEmotion = $request->filled('secondary_emotion_id')
            ? Emotion::findOrFail($request->integer('secondary_emotion_id'))
            : null;

        $vote = $action->execute($round, $player, $emotion, $secondaryEmotion);

        return response()->json([
            'votedEmotionId' => $vote->emotion_id,
            'secondaryEmotionId' => $vote->secondary_emotion_id,
        ], 201);
    }
}
