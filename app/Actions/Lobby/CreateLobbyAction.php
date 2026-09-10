<?php

namespace App\Actions\Lobby;

use App\Enums\LobbyStatus;
use App\Models\Lobby;
use App\Support\LobbyCodeGenerator;

class CreateLobbyAction
{
    public function __construct(private LobbyCodeGenerator $codes) {}

    public function execute(): Lobby
    {
        return Lobby::create([
            'code' => $this->codes->unique(),
            'status' => LobbyStatus::Open,
        ]);
    }
}
