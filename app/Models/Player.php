<?php

namespace App\Models;

use Database\Factories\PlayerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Player extends Model
{
    /** @use HasFactory<PlayerFactory> */
    use HasFactory;

    protected $fillable = [
        'lobby_id',
        'name',
        'avatar',
        'sparks_balance',
        'joined_at',
    ];

    protected function casts(): array
    {
        return [
            'sparks_balance' => 'integer',
            'joined_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Lobby, $this>
     */
    public function lobby(): BelongsTo
    {
        return $this->belongsTo(Lobby::class);
    }

    /**
     * @return BelongsToMany<Emotion, $this>
     */
    public function emotions(): BelongsToMany
    {
        return $this->belongsToMany(Emotion::class, 'player_emotions')
            ->withPivot('unlocked_in_game_round_id')
            ->withTimestamps();
    }

    /**
     * @return HasMany<Vote, $this>
     */
    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    /**
     * @return HasMany<GameRound, $this>
     */
    public function roundsRead(): HasMany
    {
        return $this->hasMany(GameRound::class, 'reader_player_id');
    }

    /**
     * @return HasMany<Interrogation, $this>
     */
    public function interrogations(): HasMany
    {
        return $this->hasMany(Interrogation::class);
    }

    public function hasUnlocked(Emotion $emotion): bool
    {
        return $this->emotions()->whereKey($emotion->id)->exists();
    }

    public function hasVotedIn(GameRound $round): bool
    {
        return $this->votes()->where('game_round_id', $round->id)->exists();
    }

    public function canAfford(int $sparks): bool
    {
        return $this->sparks_balance >= $sparks;
    }
}
