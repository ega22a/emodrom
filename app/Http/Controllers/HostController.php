<?php

namespace App\Http\Controllers;

use App\Actions\Lobby\CloseLobbyAction;
use App\Actions\Lobby\ConfigureSessionAction;
use App\Actions\Lobby\CreateLobbyAction;
use App\Http\Controllers\Concerns\RequiresHost;
use App\Http\Requests\ConfigureSessionRequest;
use App\Models\Lobby;
use App\Models\QuestionBank;
use App\Services\HostSessionService;
use App\Support\LobbyStateBuilder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

class HostController extends Controller
{
    use RequiresHost;

    public function create(): View
    {
        return view('host.create');
    }

    public function store(CreateLobbyAction $action, HostSessionService $sessions): RedirectResponse
    {
        $lobby = $action->execute();

        $sessions->remember($lobby);

        return redirect()->route('host.setup', $lobby);
    }

    public function setup(Lobby $lobby, HostSessionService $sessions): View
    {
        $this->authorizeHost($lobby, $sessions);

        return view('host.setup', [
            'lobby' => $lobby,
            'banks' => QuestionBank::orderBy('name')->get(),
            'roundLimitChoices' => ConfigureSessionRequest::ROUND_LIMIT_CHOICES,
        ]);
    }

    public function configure(
        ConfigureSessionRequest $request,
        Lobby $lobby,
        HostSessionService $sessions,
        ConfigureSessionAction $action,
    ): RedirectResponse {
        $this->authorizeHost($lobby, $sessions);

        $action->execute($lobby, $request->toData());

        return redirect()->route('host.show', $lobby);
    }

    public function show(Lobby $lobby, HostSessionService $sessions): View|RedirectResponse
    {
        $this->authorizeHost($lobby, $sessions);

        if (! $lobby->isSessionConfigured()) {
            return redirect()->route('host.setup', $lobby);
        }

        return view('host.show', [
            'lobby' => $lobby,
            'state' => LobbyStateBuilder::build($lobby),
        ]);
    }

    public function close(Lobby $lobby, HostSessionService $sessions, CloseLobbyAction $action): Response
    {
        $this->authorizeHost($lobby, $sessions);

        $action->execute($lobby);

        return response()->noContent();
    }
}
