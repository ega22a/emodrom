<?php

namespace App\Models;

use Database\Factories\VoteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vote extends Model
{
    /** @use HasFactory<VoteFactory> */
    use HasFactory;

    protected $fillable = [
        'game_round_id',
        'player_id',
        'emotion_id',
        'secondary_emotion_id',
    ];

    /**
     * @return BelongsTo<GameRound, $this>
     */
    public function round(): BelongsTo
    {
        return $this->belongsTo(GameRound::class, 'game_round_id');
    }

    /**
     * @return BelongsTo<Player, $this>
     */
    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    /**
     * @return BelongsTo<Emotion, $this>
     */
    public function emotion(): BelongsTo
    {
        return $this->belongsTo(Emotion::class);
    }

    /**
     * @return BelongsTo<Emotion, $this>
     */
    public function secondaryEmotion(): BelongsTo
    {
        return $this->belongsTo(Emotion::class, 'secondary_emotion_id');
    }

    public function matches(int $emotionId): bool
    {
        return $this->emotion_id === $emotionId || $this->secondary_emotion_id === $emotionId;
    }
}
