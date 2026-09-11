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

    public static function readerCannotVote(): self
    {
        return new self('Чтец не отвечает в своём раунде.');
    }

    public static function readerAlreadyChose(): self
    {
        return new self('Чтец уже выбрал эмоцию в этом раунде.');
    }

    public static function notTheReader(): self
    {
        return new self('Вы не чтец в этом раунде.');
    }

    public static function readerHasNotChosenYet(): self
    {
        return new self('Чтец ещё не выбрал эмоцию — нельзя показать результаты.');
    }

    public static function roundAlreadyRevealed(): self
    {
        return new self('Результаты этого раунда уже показаны.');
    }

    public static function sessionNotConfigured(): self
    {
        return new self('Сначала настройте сессию: банк вопросов, число раундов.');
    }

    public static function roundLimitReached(): self
    {
        return new self('Лимит раундов сессии исчерпан.');
    }

    public static function notEnoughSparks(): self
    {
        return new self('Недостаточно искр.');
    }

    public static function noEmotionsLeftToBuy(): self
    {
        return new self('У вас уже открыты все доступные эмоции.');
    }

    public static function interrogationDisabled(): self
    {
        return new self('Допрос отключён в настройках этой сессии.');
    }

    public static function notCurrentInterrogationTarget(): self
    {
        return new self('Сейчас допрашивается другой игрок.');
    }

    public static function invalidVoteShape(): self
    {
        return new self('Для этого раунда нужно выбрать другое количество эмоций.');
    }

    public static function cannotRemoveCurrentReader(): self
    {
        return new self('Нельзя удалить чтеца текущего раунда — сначала отмените или покажите результаты раунда.');
    }

    public static function playerAlreadyRemoved(): self
    {
        return new self('Этот игрок уже покинул лобби.');
    }

    public static function playerNotInLobby(): self
    {
        return new self('Этот игрок не в этом лобби.');
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
        ], 422);
    }
}
