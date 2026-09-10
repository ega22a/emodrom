<?php

namespace App\Models;

use Database\Factories\EmotionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Emotion extends Model
{
    /** @use HasFactory<EmotionFactory> */
    use HasFactory;

    protected $fillable = [
        'key',
        'label',
        'color',
        'icon',
        'is_base',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_base' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return HasMany<PlayerEmotion, $this>
     */
    public function playerEmotions(): HasMany
    {
        return $this->hasMany(PlayerEmotion::class);
    }

    /**
     * @return BelongsToMany<Player, $this>
     */
    public function players(): BelongsToMany
    {
        return $this->belongsToMany(Player::class, 'player_emotions');
    }
}
