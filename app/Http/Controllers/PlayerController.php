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
        if ($this->sessions->resolve($lobby)) {
            return redirect()->route('lobbies.play', $lobby);
        }

        if (! $lobby->isOpen()) {
            // A visitor who never joined has no play screen to send them to
            // — redirecting there would bounce straight back here forever.
            return view('players.closed');
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
        $activeRound = $lobby->currentRound();
        $latestRound = $activeRound ?? $lobby->rounds()->latest('number')->first();

        $isReader = $latestRound !== null && $latestRound->reader_player_id === $player->id;
        $hasVoted = $activeRound && $player->hasVotedIn($activeRound);
        $isBeingInterrogated = $latestRound !== null
            && $latestRound->isRevealed()
            && $latestRound->current_interrogation_player_id === $player->id;

        return [
            'lobbyStatus' => $lobby->status->value,
            'removed' => ! $player->isActive(),
            'player' => PlayerResource::make($player)->resolve(),
            'round' => $activeRound
                ? GameRoundResource::make($activeRound->load(['reader', 'question']))->resolve()
                : null,
            'isReader' => $isReader,
            'readerHasChosen' => $activeRound?->readerHasChosen() ?? false,
            'hasVoted' => $hasVoted,
            'votedEmotionId' => $hasVoted
                ? $player->votes()->where('game_round_id', $activeRound->id)->value('emotion_id')
                : null,
            'availableEmotions' => EmotionResource::collection(
                $player->emotions()->orderBy('sort_order')->get()
            )->resolve(),
            'isBeingInterrogated' => $isBeingInterrogated,
        ];
    }
}
