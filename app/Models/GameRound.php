<?php

namespace App\Models;

use App\Enums\RoundStatus;
use Database\Factories\GameRoundFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GameRound extends Model
{
    /** @use HasFactory<GameRoundFactory> */
    use HasFactory;

    protected $fillable = [
        'lobby_id',
        'number',
        'status',
        'winning_emotion_id',
        'started_at',
        'ended_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => RoundStatus::class,
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
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
     * @return BelongsTo<Emotion, $this>
     */
    public function winningEmotion(): BelongsTo
    {
        return $this->belongsTo(Emotion::class, 'winning_emotion_id');
    }

    /**
     * @return HasMany<Vote, $this>
     */
    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function isActive(): bool
    {
        return $this->status === RoundStatus::Active;
    }
}
