<?php

namespace App\Models;

use Database\Factories\InterrogationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Interrogation extends Model
{
    /** @use HasFactory<InterrogationFactory> */
    use HasFactory;

    protected $fillable = [
        'game_round_id',
        'player_id',
        'sparks_awarded',
    ];

    protected function casts(): array
    {
        return [
            'sparks_awarded' => 'integer',
        ];
    }

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

    public function isScored(): bool
    {
        return $this->sparks_awarded !== null;
    }
}
