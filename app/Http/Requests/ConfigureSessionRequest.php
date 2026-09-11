<?php

namespace App\Http\Requests;

use App\Data\ConfigureSessionData;
use App\Enums\EmotionSet;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class ConfigureSessionRequest extends FormRequest
{
    /**
     * The number-of-rounds choices the host picks from. Null means "until
     * the host ends it".
     *
     * @var list<int>
     */
    public const array ROUND_LIMIT_CHOICES = [5, 10, 15, 25, 35];

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'question_bank_id' => ['required', 'integer', 'exists:question_banks,id'],
            'round_limit' => ['nullable', 'integer', Rule::in(self::ROUND_LIMIT_CHOICES)],
            'interrogation_enabled' => ['required', 'boolean'],
            'emotion_set' => ['required', new Enum(EmotionSet::class)],
        ];
    }

    public function toData(): ConfigureSessionData
    {
        return new ConfigureSessionData(
            questionBankId: $this->integer('question_bank_id'),
            roundLimit: $this->filled('round_limit') ? $this->integer('round_limit') : null,
            interrogationEnabled: $this->boolean('interrogation_enabled'),
            emotionSet: EmotionSet::from($this->string('emotion_set')->toString()),
        );
    }
}
