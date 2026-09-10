<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerEmotion extends Model
{
    protected $fillable = [
        'player_id',
        'emotion_id',
        'unlocked_in_game_round_id',
    ];

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
     * @return BelongsTo<GameRound, $this>
     */
    public function unlockedInRound(): BelongsTo
    {
        return $this->belongsTo(GameRound::class, 'unlocked_in_game_round_id');
    }
}
