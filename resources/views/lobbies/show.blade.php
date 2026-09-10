<x-layout :title="$lobby->code.' — Лас Кукарачас'">
    <div
        class="flex min-h-dvh flex-col p-6 sm:p-10"
        x-data="hostScreen(@js($state), @js($lobby->code))"
    >
        <header class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="flex size-12 shrink-0 items-center justify-center rounded-full bg-amber-500 text-white">
                    <svg class="size-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#icon-bug"></use></svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-stone-900">Лас Кукарачас</h1>
                    <p class="text-sm text-stone-500" x-show="round" x-cloak>Раунд <span x-text="round?.number"></span></p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <img src="{{ route('lobbies.qr', $lobby) }}" alt="QR-код лобби" class="size-14 rounded-lg border border-stone-200 bg-white p-1">
                <span class="rounded-full bg-white px-5 py-2.5 text-xl font-bold tracking-[0.2em] text-amber-600 shadow-sm">{{ $lobby->code }}</span>
            </div>
        </header>

        <main class="mt-10 flex flex-1 flex-col">
            <template x-if="screen === 'closed'">
                <div class="flex flex-1 flex-col items-center justify-center text-center">
                    <svg class="size-16 text-stone-400" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><use href="#icon-party-popper"></use></svg>
                    <h2 class="mt-4 text-3xl font-bold text-stone-900">Лобби закрыто</h2>
                    <p class="mt-2 text-stone-500">Спасибо за игру! Создайте новое лобби, чтобы сыграть ещё раз.</p>
                    <a href="{{ route('lobbies.create') }}" class="mt-6 rounded-full bg-amber-500 px-6 py-3 font-semibold text-white shadow-md transition hover:bg-amber-600">
                        Новое лобби
                    </a>
                </div>
            </template>

            <template x-if="screen === 'lobby'">
                <div class="grid flex-1 grid-cols-1 items-start gap-10 lg:grid-cols-[320px_1fr]">
                    <div class="rounded-3xl bg-white p-8 text-center shadow-sm">
                        <img src="{{ route('lobbies.qr', $lobby) }}" alt="QR-код лобби" class="mx-auto size-56">
                        <p class="mt-4 text-sm text-stone-500">Отсканируйте камерой телефона, чтобы присоединиться</p>
                    </div>

                    <div>
                        <h2 class="mb-4 text-lg font-semibold text-stone-700">
                            Игроки (<span x-text="lobby.players.length"></span>)
                        </h2>

                        <p x-show="lobby.players.length === 0" x-cloak class="text-stone-400">Ждём первых игроков…</p>

                        <div class="flex flex-wrap gap-5">
                            <template x-for="p in lobby.players" :key="p.id">
                                <div class="flex flex-col items-center gap-2">
                                    <div class="inline-flex size-16 items-center justify-center rounded-full text-white shadow-sm" :class="'bg-' + p.avatarColor + '-500'">
                                        <svg class="size-8" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <use :href="'#icon-' + p.avatar"></use>
                                        </svg>
                                    </div>
                                    <span class="max-w-20 truncate text-sm font-medium text-stone-600" x-text="p.name"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </template>

            <template x-if="screen === 'round'">
                <div class="mx-auto w-full max-w-3xl">
                    <div
                        x-show="lastResult"
                        x-cloak
                        x-transition
                        class="mb-6 rounded-2xl bg-emerald-50 px-5 py-4 text-emerald-800"
                    >
                        <template x-if="lastResult?.winningEmotion">
                            <p>
                                В прошлом раунде чаще всего выбирали
                                <strong x-text="lastResult.winningEmotion.label"></strong> —
                                игроки, угадавшие эмоцию, получили новую карту.
                            </p>
                        </template>
                        <template x-if="lastResult && !lastResult.winningEmotion">
                            <p>В прошлом раунде никто не проголосовал.</p>
                        </template>
                    </div>

                    <p class="mb-6 text-center text-stone-500">
                        Проголосовало <span class="font-semibold text-stone-700" x-text="voteStats.totalVotes"></span>
                        из <span class="font-semibold text-stone-700" x-text="voteStats.totalPlayers"></span>
                    </p>

                    <div class="space-y-3">
                        <template x-for="stat in voteStats.stats" :key="stat.emotion.id">
                            <div class="rounded-2xl bg-white p-4 shadow-sm">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2.5">
                                        <div class="inline-flex size-9 items-center justify-center rounded-full text-white" :class="'bg-' + stat.emotion.color + '-500'">
                                            <svg class="size-4.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <use :href="'#icon-' + stat.emotion.icon"></use>
                                            </svg>
                                        </div>
                                        <span class="font-semibold text-stone-800" x-text="stat.emotion.label"></span>
                                    </div>
                                    <span class="text-sm font-semibold text-stone-500" x-text="stat.votesCount"></span>
                                </div>

                                <div class="mt-3 h-2 overflow-hidden rounded-full bg-stone-100">
                                    <div
                                        class="h-full rounded-full transition-all"
                                        :class="'bg-' + stat.emotion.color + '-500'"
                                        :style="`width: ${voteStats.totalPlayers ? (stat.votesCount / voteStats.totalPlayers) * 100 : 0}%`"
                                    ></div>
                                </div>

                                <div class="mt-3 flex flex-wrap gap-1.5">
                                    <template x-for="voter in stat.voters" :key="voter.id">
                                        <div class="inline-flex size-7 items-center justify-center rounded-full text-white" :class="'bg-' + voter.avatarColor + '-500'">
                                            <svg class="size-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <use :href="'#icon-' + voter.avatar"></use>
                                            </svg>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <p x-show="voteStats.stats.length === 0" x-cloak class="text-center text-stone-400">Пока никто не ответил</p>
                    </div>
                </div>
            </template>
        </main>

        <footer class="mt-10 flex items-center justify-between gap-4 border-t border-stone-200 pt-6" x-show="screen !== 'closed'" x-cloak>
            <button
                type="button"
                @click="closeLobby"
                class="rounded-full border border-stone-300 px-5 py-2.5 font-medium text-stone-600 transition hover:bg-stone-100"
            >
                Закрыть лобби
            </button>

            <button
                type="button"
                @click="startRound"
                :disabled="working"
                class="rounded-full bg-amber-500 px-8 py-3 text-lg font-semibold text-white shadow-md shadow-amber-500/30 transition hover:bg-amber-600 disabled:opacity-50"
                x-text="round ? 'Следующий раунд' : 'Начать раунд'"
            ></button>
        </footer>
    </div>

    @push('scripts')
        @vite('resources/js/host-screen.js')
    @endpush
</x-layout>
