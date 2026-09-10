<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class JoinLobbyData extends Data
{
    public function __construct(
        public string $name,
        public string $avatar,
    ) {}
}
