<?php

namespace App\Data;

use App\Models\Player;
use App\Support\Avatars;
use Spatie\LaravelData\Data;

class PlayerData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public string $avatar,
        public string $avatarColor,
    ) {}

    public static function fromModel(Player $player): self
    {
        return new self(
            id: $player->id,
            name: $player->name,
            avatar: $player->avatar,
            avatarColor: Avatars::color($player->avatar),
        );
    }
}
