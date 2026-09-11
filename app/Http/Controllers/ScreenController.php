<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConnectScreenRequest;
use App\Models\Lobby;
use App\Support\LobbyStateBuilder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ScreenController extends Controller
{
    public function join(): View
    {
        return view('screen.join');
    }

    public function connect(ConnectScreenRequest $request): RedirectResponse
    {
        $lobby = Lobby::where('code', $request->string('code'))->firstOrFail();

        return redirect()->route('screen.show', $lobby);
    }

    public function show(Lobby $lobby): View
    {
        return view('screen.show', [
            'lobby' => $lobby,
            'state' => LobbyStateBuilder::build($lobby),
        ]);
    }
}
