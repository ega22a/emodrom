<x-layout title="{{ $lobby->code }} — Лас Кукарачас">
    <div
        class="mx-auto flex min-h-dvh max-w-md flex-col px-5 py-6"
        x-data="playerScreen(@js($state), @js($lobby->code))"
    >
        <header class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="inline-flex size-10 shrink-0 items-center justify-center rounded-full text-white shadow-sm" :class="'bg-' + player.avatarColor + '-500'">
                    <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <use :href="'#icon-' + player.avatar"></use>
                    </svg>
                </div>
                <span class="font-semibold text-stone-800" x-text="player.name"></span>
            </div>
            <span class="rounded-full bg-stone-200 px-2.5 py-1 text-xs font-semibold tracking-wide text-stone-500">{{ $lobby->code }}</span>
        </header>

        <div
            x-show="toast"
            x-transition
            x-cloak
            class="mt-4 flex items-center gap-2 rounded-xl bg-emerald-100 px-4 py-3 text-emerald-800"
        >
            <svg class="size-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#icon-sparkles"></use></svg>
            <span x-text="toast" class="text-sm font-medium"></span>
        </div>

        <main class="flex flex-1 flex-col items-center justify-center py-10 text-center">
            <template x-if="screen === 'closed'">
                <div>
                    <svg class="mx-auto size-16 text-stone-400" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><use href="#icon-party-popper"></use></svg>
                    <h1 class="mt-4 text-2xl font-bold text-stone-900">Игра завершена</h1>
                    <p class="mt-2 text-stone-500">Спасибо за участие! До встречи в следующей игре.</p>
                </div>
            </template>

            <template x-if="screen === 'waiting'">
                <div>
                    <svg class="mx-auto size-16 animate-pulse text-amber-400" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><use href="#icon-bug"></use></svg>
                    <h1 class="mt-4 text-2xl font-bold text-stone-900">Ждём начала раунда</h1>
                    <p class="mt-2 text-stone-500">Ведущий скоро запустит раунд — приготовьтесь выбрать эмоцию.</p>
                </div>
            </template>

            <template x-if="screen === 'voted'">
                <div>
                    <svg class="mx-auto size-16 text-emerald-500" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><use href="#icon-smile"></use></svg>
                    <h1 class="mt-4 text-2xl font-bold text-stone-900">Ответ принят!</h1>
                    <p class="mt-2 text-stone-500">Ждём, пока ответят остальные игроки.</p>
                </div>
            </template>

            <template x-if="screen === 'voting'">
                <div class="w-full text-left">
                    <h1 class="text-center text-xl font-bold text-stone-900">Что вы сейчас чувствуете?</h1>
                    <p class="mt-1 text-center text-sm text-stone-500">Выберите эмоцию, которая ближе всего к ситуации</p>

                    <div class="mt-6 grid grid-cols-2 gap-3">
                        <template x-for="emotion in availableEmotions" :key="emotion.id">
                            <button
                                type="button"
                                @click="vote(emotion)"
                                :disabled="voting"
                                class="flex flex-col items-center justify-center gap-2 rounded-2xl px-3 py-5 text-white shadow-sm transition active:scale-95"
                                :class="'bg-' + emotion.color + '-500'"
                            >
                                <svg class="size-8" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <use :href="'#icon-' + emotion.icon"></use>
                                </svg>
                                <span class="text-sm font-semibold" x-text="emotion.label"></span>
                            </button>
                        </template>
                    </div>
                </div>
            </template>
        </main>
    </div>

    @push('scripts')
        @vite('resources/js/player-screen.js')
    @endpush
</x-layout>
