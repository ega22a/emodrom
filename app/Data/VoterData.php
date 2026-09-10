<?php

namespace App\Data;

use App\Models\Player;
use App\Support\Avatars;
use Spatie\LaravelData\Data;

/**
 * An anonymized player reference shown next to a vote count on the host
 * screen: the game only reveals who picked an emotion by avatar, never by
 * name, so the other players still have to guess.
 */
class VoterData extends Data
{
    public function __construct(
        public int $id,
        public string $avatar,
        public string $avatarColor,
    ) {}

    public static function fromModel(Player $player): self
    {
        return new self(
            id: $player->id,
            avatar: $player->avatar,
            avatarColor: Avatars::color($player->avatar),
        );
    }
}
