<?php

namespace App\Http\Controllers;

use App\Actions\Lobby\CloseLobbyAction;
use App\Actions\Lobby\CreateLobbyAction;
use App\Http\Resources\GameRoundResource;
use App\Http\Resources\LobbyResource;
use App\Models\Lobby;
use App\Support\VoteStatsBuilder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

class LobbyController extends Controller
{
    public function create(): View
    {
        return view('lobbies.create');
    }

    public function store(CreateLobbyAction $action): RedirectResponse
    {
        $lobby = $action->execute();

        return redirect()->route('lobbies.show', $lobby);
    }

    public function show(Lobby $lobby): View
    {
        $lobby->load('players');
        $round = $lobby->currentRound()?->load('winningEmotion');

        return view('lobbies.show', [
            'lobby' => $lobby,
            'state' => [
                'lobby' => LobbyResource::make($lobby)->resolve(),
                'round' => $round ? GameRoundResource::make($round)->resolve() : null,
                'voteStats' => $round ? VoteStatsBuilder::build($round)->toArray() : null,
            ],
        ]);
    }

    public function close(Lobby $lobby, CloseLobbyAction $action): Response
    {
        $action->execute($lobby);

        return response()->noContent();
    }
}
