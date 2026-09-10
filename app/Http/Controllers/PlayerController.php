<?php

namespace App\Http\Controllers;

use App\Actions\Lobby\JoinLobbyAction;
use App\Data\JoinLobbyData;
use App\Http\Requests\JoinLobbyRequest;
use App\Http\Resources\EmotionResource;
use App\Http\Resources\GameRoundResource;
use App\Http\Resources\PlayerResource;
use App\Models\Lobby;
use App\Models\Player;
use App\Services\PlayerSessionService;
use App\Support\Avatars;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class PlayerController extends Controller
{
    public function __construct(private PlayerSessionService $sessions) {}

    public function create(Lobby $lobby): View|RedirectResponse
    {
        if (! $lobby->isOpen()) {
            return redirect()->route('lobbies.play', $lobby);
        }

        if ($this->sessions->resolve($lobby)) {
            return redirect()->route('lobbies.play', $lobby);
        }

        return view('players.join', [
            'lobby' => $lobby,
            'avatars' => Avatars::options(),
        ]);
    }

    public function store(JoinLobbyRequest $request, Lobby $lobby, JoinLobbyAction $action): RedirectResponse
    {
        $player = $action->execute($lobby, JoinLobbyData::from($request->validated()));

        $this->sessions->remember($lobby, $player);

        return redirect()->route('lobbies.play', $lobby);
    }

    public function show(Lobby $lobby): View|RedirectResponse
    {
        $player = $this->sessions->resolve($lobby);

        if (! $player) {
            return redirect()->route('lobbies.join', $lobby);
        }

        return view('players.play', [
            'lobby' => $lobby,
            'state' => $this->stateFor($lobby, $player),
        ]);
    }

    public function state(Lobby $lobby): JsonResponse
    {
        $player = $this->sessions->resolve($lobby);

        if (! $player) {
            abort(403, 'Вы ещё не присоединились к этому лобби.');
        }

        return response()->json($this->stateFor($lobby, $player));
    }

    /**
     * @return array<string, mixed>
     */
    private function stateFor(Lobby $lobby, Player $player): array
    {
        $round = $lobby->currentRound();
        $hasVoted = $round && $player->hasVotedIn($round);

        return [
            'lobbyStatus' => $lobby->status->value,
            'player' => PlayerResource::make($player)->resolve(),
            'round' => $round ? GameRoundResource::make($round)->resolve() : null,
            'availableEmotions' => EmotionResource::collection(
                $player->emotions()->orderBy('sort_order')->get()
            )->resolve(),
            'hasVoted' => $hasVoted,
            'votedEmotionId' => $hasVoted
                ? $player->votes()->where('game_round_id', $round->id)->value('emotion_id')
                : null,
        ];
    }
}
