<?php

namespace App\Http\Resources;

use App\Data\QuestionData;
use App\Models\GameRound;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin GameRound
 *
 * Deliberately never exposes reader_emotion_id — the reader's pick stays
 * private until RevealRoundAction runs, and that flows through a separate
 * reveal broadcast/endpoint, not this resource.
 */
class GameRoundResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'status' => $this->status->value,
            'reader' => PlayerResource::make($this->reader),
            'question' => QuestionData::fromModel($this->question)->toArray(),
        ];
    }
}
