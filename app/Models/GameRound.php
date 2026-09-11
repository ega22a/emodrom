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
        'question_id',
        'reader_player_id',
        'reader_emotion_id',
        'current_interrogation_player_id',
        'started_at',
        'revealed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => RoundStatus::class,
            'started_at' => 'datetime',
            'revealed_at' => 'datetime',
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
     * @return BelongsTo<Question, $this>
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * @return BelongsTo<Player, $this>
     */
    public function reader(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'reader_player_id');
    }

    /**
     * @return BelongsTo<Emotion, $this>
     */
    public function readerEmotion(): BelongsTo
    {
        return $this->belongsTo(Emotion::class, 'reader_emotion_id');
    }

    /**
     * @return BelongsTo<Player, $this>
     */
    public function currentInterrogationPlayer(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'current_interrogation_player_id');
    }

    /**
     * @return HasMany<Vote, $this>
     */
    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    /**
     * @return HasMany<Interrogation, $this>
     */
    public function interrogations(): HasMany
    {
        return $this->hasMany(Interrogation::class);
    }

    public function isVoting(): bool
    {
        return $this->status === RoundStatus::Voting;
    }

    public function isRevealed(): bool
    {
        return $this->status === RoundStatus::Revealed;
    }

    public function readerHasChosen(): bool
    {
        return $this->reader_emotion_id !== null;
    }
}
