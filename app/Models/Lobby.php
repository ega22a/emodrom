<?php

namespace App\Models;

use App\Enums\EmotionSet;
use App\Enums\LobbyStatus;
use App\Enums\RoundStatus;
use Database\Factories\LobbyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lobby extends Model
{
    /** @use HasFactory<LobbyFactory> */
    use HasFactory;

    protected $fillable = [
        'code',
        'status',
        'closed_at',
        'host_token',
        'question_bank_id',
        'round_limit',
        'interrogation_enabled',
        'emotion_set',
    ];

    protected function casts(): array
    {
        return [
            'status' => LobbyStatus::class,
            'closed_at' => 'datetime',
            'interrogation_enabled' => 'boolean',
            'emotion_set' => EmotionSet::class,
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
     * The current roster — everyone who hasn't been removed by the host.
     * Historical rounds still reference removed players directly by id, so
     * this is only for "who's here right now" views (rosters, totals,
     * reader rotation), never for round history.
     *
     * @return HasMany<Player, $this>
     */
    public function activePlayers(): HasMany
    {
        return $this->players()->whereNull('removed_at');
    }

    /**
     * @return HasMany<GameRound, $this>
     */
    public function rounds(): HasMany
    {
        return $this->hasMany(GameRound::class);
    }

    /**
     * @return BelongsTo<QuestionBank, $this>
     */
    public function questionBank(): BelongsTo
    {
        return $this->belongsTo(QuestionBank::class);
    }

    public function currentRound(): ?GameRound
    {
        return $this->rounds()
            ->where('status', RoundStatus::Voting)
            ->latest('number')
            ->first();
    }

    public function isOpen(): bool
    {
        return $this->status === LobbyStatus::Open;
    }

    public function isSessionConfigured(): bool
    {
        return $this->question_bank_id !== null;
    }

    public function roundsPlayedCount(): int
    {
        return $this->rounds()->count();
    }

    public function hasReachedRoundLimit(): bool
    {
        return $this->round_limit !== null && $this->roundsPlayedCount() >= $this->round_limit;
    }
}
