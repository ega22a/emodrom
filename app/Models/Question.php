<?php

namespace App\Models;

use Database\Factories\QuestionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Question extends Model
{
    /** @use HasFactory<QuestionFactory> */
    use HasFactory;

    protected $fillable = [
        'question_bank_id',
        'situation',
        'reward_sparks',
    ];

    protected function casts(): array
    {
        return [
            'reward_sparks' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<QuestionBank, $this>
     */
    public function bank(): BelongsTo
    {
        return $this->belongsTo(QuestionBank::class, 'question_bank_id');
    }
}
