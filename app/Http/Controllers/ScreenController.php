<?php

namespace App\Http\Controllers;

use App\Models\Lobby;
use App\Support\LobbyStateBuilder;
use Illuminate\Contracts\View\View;

class ScreenController extends Controller
{
    public function show(Lobby $lobby): View
    {
        return view('screen.show', [
            'lobby' => $lobby,
            'state' => LobbyStateBuilder::build($lobby),
        ]);
    }
}
