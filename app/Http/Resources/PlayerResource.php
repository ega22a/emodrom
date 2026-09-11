<?php

namespace App\Http\Resources;

use App\Models\Player;
use App\Support\Avatars;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Player
 */
class PlayerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'avatar' => $this->avatar,
            'avatarColor' => Avatars::color($this->avatar),
            'sparksBalance' => $this->sparks_balance,
        ];
    }
}
