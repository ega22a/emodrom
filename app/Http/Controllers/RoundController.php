<?php

namespace App\Http\Controllers;

use App\Actions\Round\StartRoundAction;
use App\Models\Lobby;
use Illuminate\Http\Response;

class RoundController extends Controller
{
    public function store(Lobby $lobby, StartRoundAction $action): Response
    {
        $action->execute($lobby);

        return response()->noContent(201);
    }
}
