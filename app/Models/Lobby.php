<?php

namespace App\Models;

use App\Enums\LobbyStatus;
use App\Enums\RoundStatus;
use Database\Factories\LobbyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lobby extends Model
{
    /** @use HasFactory<LobbyFactory> */
    use HasFactory;

    protected $fillable = [
        'code',
        'status',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => LobbyStatus::class,
            'closed_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'code';
    }

    /**
     * @return HasMany<Player, $this>
     */
    public function players(): HasMany
    {
        return $this->hasMany(Player::class);
    }

    /**
     * @return HasMany<GameRound, $this>
     */
    public function rounds(): HasMany
    {
        return $this->hasMany(GameRound::class);
    }

    public function currentRound(): ?GameRound
    {
        return $this->rounds()
            ->where('status', RoundStatus::Active)
            ->latest('number')
            ->first();
    }

    public function isOpen(): bool
    {
        return $this->status === LobbyStatus::Open;
    }
}
