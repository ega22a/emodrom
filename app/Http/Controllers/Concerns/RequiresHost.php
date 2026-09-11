<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Lobby;
use App\Services\HostSessionService;
use Symfony\Component\HttpKernel\Exception\HttpException;

trait RequiresHost
{
    /**
     * @throws HttpException
     */
    protected function authorizeHost(Lobby $lobby, HostSessionService $sessions): void
    {
        if (! $sessions->isHost($lobby)) {
            abort(403, 'Только ведущий может это сделать.');
        }
    }
}
