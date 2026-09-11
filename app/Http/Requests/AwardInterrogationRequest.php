<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AwardInterrogationRequest extends FormRequest
{
    /**
     * The only point values the host can award — a deliberate choice, not
     * free text, per the game's rules.
     *
     * @var list<int>
     */
    public const array SPARK_CHOICES = [0, 1, 3, 5];

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
            'sparks' => ['required', 'integer', Rule::in(self::SPARK_CHOICES)],
        ];
    }
}
