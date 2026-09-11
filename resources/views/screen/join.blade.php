<x-layout title="Подключить экран — Эмодром">
    <div class="mx-auto flex min-h-dvh max-w-md flex-col items-center justify-center px-6 py-12 text-center">
        <div class="flex size-20 animate-pop-in items-center justify-center rounded-full bg-amber-500 text-white shadow-md">
            <x-icon name="bug" class="size-10" />
        </div>

        <h1 class="mt-6 text-3xl font-bold text-stone-900">Подключить экран</h1>
        <p class="mt-2 text-stone-500">Введите код лобби, который показывает ведущий</p>

        <form method="POST" action="{{ route('screen.connect') }}" class="mt-8 w-full">
            @csrf

            <input
                type="text"
                name="code"
                maxlength="6"
                autocomplete="off"
                autocapitalize="characters"
                spellcheck="false"
                required
                value="{{ old('code') }}"
                placeholder="КОД"
                class="w-full rounded-2xl border border-stone-300 px-4 py-4 text-center text-3xl font-bold uppercase tracking-[0.3em] text-stone-900 shadow-sm outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-200"
            >
            @error('code')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror

            <button
                type="submit"
                class="mt-6 w-full rounded-full bg-amber-500 py-4 text-lg font-semibold text-white shadow-lg shadow-amber-500/30 transition hover:bg-amber-600 active:scale-95"
            >
                Подключиться
            </button>
        </form>
    </div>
</x-layout>
