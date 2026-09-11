<?php

namespace App\Http\Controllers;

use App\Actions\Round\CancelRoundAction;
use App\Actions\Round\ReassignReaderAction;
use App\Actions\Round\RevealRoundAction;
use App\Actions\Round\StartRoundAction;
use App\Exceptions\GameException;
use App\Http\Controllers\Concerns\RequiresHost;
use App\Http\Requests\ReassignReaderRequest;
use App\Http\Requests\StartRoundRequest;
use App\Models\Lobby;
use App\Models\Player;
use App\Services\HostSessionService;
use Illuminate\Http\Response;

class RoundController extends Controller
{
    use RequiresHost;

    public function store(StartRoundRequest $request, Lobby $lobby, HostSessionService $sessions, StartRoundAction $action): Response
    {
        $this->authorizeHost($lobby, $sessions);

        $action->execute($lobby, $request->boolean('mirror'), $request->boolean('cocktail'));

        return response()->noContent(201);
    }

    public function reveal(Lobby $lobby, HostSessionService $sessions, RevealRoundAction $action): Response
    {
        $this->authorizeHost($lobby, $sessions);

        $round = $lobby->currentRound();

        if (! $round) {
            throw GameException::roundNotActive();
        }

        $action->execute($round);

        return response()->noContent();
    }

    public function cancel(Lobby $lobby, HostSessionService $sessions, CancelRoundAction $action): Response
    {
        $this->authorizeHost($lobby, $sessions);

        $round = $lobby->currentRound();

        if (! $round) {
            throw GameException::roundNotActive();
        }

        $action->execute($round);

        return response()->noContent();
    }

    public function reassignReader(
        ReassignReaderRequest $request,
        Lobby $lobby,
        HostSessionService $sessions,
        ReassignReaderAction $action,
    ): Response {
        $this->authorizeHost($lobby, $sessions);

        $round = $lobby->currentRound();

        if (! $round) {
            throw GameException::roundNotActive();
        }

        $newReader = Player::findOrFail($request->integer('player_id'));

        $action->execute($round, $newReader);

        return response()->noContent();
    }
}
