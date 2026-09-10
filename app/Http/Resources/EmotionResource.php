<?php

namespace App\Http\Resources;

use App\Models\Emotion;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Emotion
 */
class EmotionResource extends JsonResource
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
            'key' => $this->key,
            'label' => $this->label,
            'color' => $this->color,
            'icon' => $this->icon,
            'isBase' => $this->is_base,
        ];
    }
}
