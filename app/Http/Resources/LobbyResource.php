<?php

namespace App\Http\Resources;

use App\Models\Lobby;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Lobby
 */
class LobbyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'code' => $this->code,
            'status' => $this->status->value,
            'joinUrl' => route('lobbies.join', $this->resource),
            'players' => PlayerResource::collection($this->whenLoaded('players')),
        ];
    }
}
