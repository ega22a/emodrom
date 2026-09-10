<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GameException extends Exception
{
    public static function lobbyClosed(): self
    {
        return new self('Это лобби уже закрыто.');
    }

    public static function roundNotActive(): self
    {
        return new self('Сейчас нет активного раунда.');
    }

    public static function emotionNotUnlocked(): self
    {
        return new self('Эта эмоция вам пока недоступна.');
    }

    public static function alreadyVoted(): self
    {
        return new self('Вы уже ответили в этом раунде.');
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
        ], 422);
    }
}
