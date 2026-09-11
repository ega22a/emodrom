<x-layout title="Настройка сессии — Эмодром">
    <div class="mx-auto flex min-h-dvh max-w-md flex-col px-5 py-8">
        <div class="text-center">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-3 py-1 text-sm font-semibold tracking-wide text-amber-700">
                Лобби {{ $lobby->code }}
            </span>
            <h1 class="mt-4 text-2xl font-bold text-stone-900">
                {{ $lobby->isSessionConfigured() ? 'Новая сессия' : 'Настройка игры' }}
            </h1>
            <p class="mt-1 text-stone-500">Выберите банк вопросов и параметры раунда</p>
        </div>

        <form method="POST" action="{{ route('host.configure', $lobby) }}" class="mt-8 flex flex-1 flex-col gap-6">
            @csrf

            <div>
                <label for="question_bank_id" class="mb-1.5 block text-sm font-medium text-stone-700">Банк вопросов</label>
                <select
                    id="question_bank_id"
                    name="question_bank_id"
                    required
                    class="w-full rounded-xl border border-stone-300 px-4 py-3 text-base text-stone-900 shadow-sm outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-200"
                >
                    @foreach ($banks as $bank)
                        <option value="{{ $bank->id }}" @selected(old('question_bank_id', $lobby->question_bank_id) == $bank->id)>
                            {{ $bank->name }}
                        </option>
                    @endforeach
                </select>
                @error('question_bank_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <span class="mb-1.5 block text-sm font-medium text-stone-700">Число раундов</span>
                <div class="grid grid-cols-3 gap-2">
                    @foreach ($roundLimitChoices as $choice)
                        <label class="flex cursor-pointer items-center justify-center rounded-xl border border-stone-300 py-2.5 text-sm font-semibold text-stone-700 has-[:checked]:border-amber-500 has-[:checked]:bg-amber-50 has-[:checked]:text-amber-700">
                            <input type="radio" name="round_limit" value="{{ $choice }}" class="sr-only" @checked(old('round_limit', $lobby->round_limit) == $choice)>
                            {{ $choice }}
                        </label>
                    @endforeach
                    <label class="col-span-3 flex cursor-pointer items-center justify-center rounded-xl border border-stone-300 py-2.5 text-sm font-semibold text-stone-700 has-[:checked]:border-amber-500 has-[:checked]:bg-amber-50 has-[:checked]:text-amber-700">
                        <input type="radio" name="round_limit" value="" class="sr-only" @checked(old('round_limit', $lobby->round_limit) === null)>
                        Без ограничений — закончу сам
                    </label>
                </div>
            </div>

            <label class="flex items-center justify-between rounded-xl border border-stone-300 px-4 py-3">
                <span class="text-sm font-medium text-stone-700">Режим допроса</span>
                <input
                    type="checkbox"
                    name="interrogation_enabled"
                    value="1"
                    class="size-5 rounded text-amber-500 focus:ring-amber-400"
                    @checked(old('interrogation_enabled', $lobby->interrogation_enabled))
                >
            </label>

            <div>
                <span class="mb-1.5 block text-sm font-medium text-stone-700">Набор эмоций</span>
                <div class="grid grid-cols-2 gap-2">
                    <label class="flex cursor-pointer items-center justify-center rounded-xl border border-stone-300 py-2.5 text-sm font-semibold text-stone-700 has-[:checked]:border-amber-500 has-[:checked]:bg-amber-50 has-[:checked]:text-amber-700">
                        <input type="radio" name="emotion_set" value="classic" class="sr-only" @checked(old('emotion_set', $lobby->emotion_set->value) === 'classic')>
                        Классический
                    </label>
                    <label class="flex cursor-pointer items-center justify-center rounded-xl border border-stone-300 py-2.5 text-sm font-semibold text-stone-700 has-[:checked]:border-amber-500 has-[:checked]:bg-amber-50 has-[:checked]:text-amber-700">
                        <input type="radio" name="emotion_set" value="extended" class="sr-only" @checked(old('emotion_set', $lobby->emotion_set->value) === 'extended')>
                        Расширенный
                    </label>
                </div>
            </div>

            <button
                type="submit"
                class="mt-auto w-full rounded-full bg-amber-500 py-4 text-lg font-semibold text-white shadow-lg shadow-amber-500/30 transition active:scale-95"
            >
                {{ $lobby->isSessionConfigured() ? 'Начать новую сессию' : 'Начать игру' }}
            </button>
        </form>
    </div>
</x-layout>
