<?php

namespace App\Http\Controllers;

use App\Actions\Round\AwardInterrogationPointsAction;
use App\Actions\Round\SkipInterrogationAction;
use App\Exceptions\GameException;
use App\Http\Controllers\Concerns\RequiresHost;
use App\Http\Requests\AwardInterrogationRequest;
use App\Models\Interrogation;
use App\Models\Lobby;
use App\Services\HostSessionService;
use Illuminate\Http\Response;

class InterrogationController extends Controller
{
    use RequiresHost;

    public function award(
        AwardInterrogationRequest $request,
        Lobby $lobby,
        Interrogation $interrogation,
        HostSessionService $sessions,
        AwardInterrogationPointsAction $action,
    ): Response {
        $this->authorizeHost($lobby, $sessions);

        $round = $lobby->currentRound() ?? $lobby->rounds()->latest('number')->first();

        if (! $round || $interrogation->game_round_id !== $round->id) {
            throw GameException::roundNotActive();
        }

        $action->execute($round, $interrogation, $request->integer('sparks'));

        return response()->noContent();
    }

    public function skip(Lobby $lobby, HostSessionService $sessions, SkipInterrogationAction $action): Response
    {
        $this->authorizeHost($lobby, $sessions);

        $round = $lobby->currentRound() ?? $lobby->rounds()->latest('number')->first();

        if (! $round) {
            throw GameException::roundNotActive();
        }

        $action->execute($round);

        return response()->noContent();
    }
}
