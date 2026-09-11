<x-layout :title="$lobby->code.' — пульт ведущего'">
    <div
        class="mx-auto flex min-h-dvh max-w-md flex-col px-5 py-6"
        x-data="hostPanel(@js($state), @js($lobby->code))"
    >
        <header class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-stone-900">Эмодром</h1>
                <p class="text-sm text-stone-500">
                    Раунд <span x-text="lobby.roundsPlayed + (screen === 'voting' ? 1 : 0)"></span><template x-if="lobby.roundLimit"><span>&nbsp;из <span x-text="lobby.roundLimit"></span></span></template>
                </p>
            </div>
            <span class="rounded-full bg-white px-4 py-2 text-lg font-bold tracking-widest text-amber-600 shadow-sm">{{ $lobby->code }}</span>
        </header>

        <p class="mt-2 text-center text-xs text-stone-400">
            Экран для игроков:
            <a class="underline" href="{{ route('screen.show', $lobby) }}" target="_blank">{{ route('screen.show', $lobby) }}</a>
        </p>

        <main class="mt-6 flex flex-1 flex-col">
            <template x-if="screen === 'closed'">
                <div class="flex flex-1 animate-pop-in flex-col items-center justify-center text-center">
                    <x-icon name="party-popper" class="size-16 text-stone-400" />
                    <h2 class="mt-4 text-2xl font-bold text-stone-900">Лобби закрыто</h2>
                    <a href="{{ route('host.create') }}" class="mt-6 rounded-full bg-amber-500 px-6 py-3 font-semibold text-white shadow-md transition hover:bg-amber-600 active:scale-95">
                        Новое лобби
                    </a>
                </div>
            </template>

            <template x-if="screen === 'idle' || screen === 'revealed'">
                <div class="animate-pop-in">
                    <template x-if="screen === 'revealed'">
                        <div class="mb-6 rounded-2xl bg-white p-4 shadow-sm">
                            <p class="text-sm font-semibold text-stone-500">Раунд <span x-text="lastResult.roundNumber"></span> — итог</p>
                            <template x-if="!lastResult.readerTookReward">
                                <p class="mt-1 text-stone-800">
                                    Эмоцию чтеца (<strong x-text="lastResult.readerEmotion.label"></strong>) угадали:
                                    <span x-text="lastResult.rewardedPlayerIds.length"></span> игрок(ов), +<span x-text="lastResult.rewardSparks"></span> искр каждому.
                                </p>
                            </template>
                            <template x-if="lastResult.readerTookReward">
                                <p class="mt-1 text-stone-800">
                                    Никто не выбрал <strong x-text="lastResult.readerEmotion.label"></strong> — чтец забирает
                                    <span x-text="lastResult.rewardSparks"></span> искр себе.
                                </p>
                            </template>
                        </div>
                    </template>

                    <h2 class="mb-3 text-lg font-semibold text-stone-700">
                        Игроки (<span x-text="lobby.players.length"></span>)
                    </h2>
                    <div class="flex flex-wrap gap-4">
                        <template x-for="p in lobby.players" :key="p.id">
                            <div class="flex animate-drop-in flex-col items-center gap-1">
                                <div class="inline-flex size-14 items-center justify-center rounded-full text-white shadow-sm" :class="'bg-' + p.avatarColor + '-500'">
                                    <svg class="size-7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use :href="'#icon-' + p.avatar"></use></svg>
                                </div>
                                <span class="max-w-16 truncate text-xs font-medium text-stone-600" x-text="p.name"></span>
                                <span class="text-xs text-amber-600" x-text="p.sparksBalance + ' ✦'"></span>
                            </div>
                        </template>
                    </div>

                    <template x-if="lobby.players.length < 2">
                        <p class="mt-4 text-sm text-stone-400">Нужно минимум 2 игрока, чтобы начать раунд.</p>
                    </template>
                </div>
            </template>

            <template x-if="screen === 'voting'">
                <div class="animate-pop-in">
                    <div class="rounded-2xl bg-white p-4 shadow-sm">
                        <div class="flex items-center gap-2 text-sm font-semibold text-stone-500">
                            <div class="inline-flex size-6 items-center justify-center rounded-full text-white" :class="'bg-' + round.reader.avatarColor + '-500'">
                                <svg class="size-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use :href="'#icon-' + round.reader.avatar"></use></svg>
                            </div>
                            Чтец: <span x-text="round.reader.name"></span>
                        </div>
                        <p class="mt-2 text-stone-800" x-text="round.question.situation"></p>
                        <p class="mt-2 text-xs font-semibold text-amber-600">Награда: +<span x-text="round.question.rewardSparks"></span> искр</p>
                    </div>

                    <p class="mt-4 text-center text-stone-500">
                        Ответили <span class="font-semibold text-stone-700" x-text="voteProgress.votedCount"></span>
                        из <span class="font-semibold text-stone-700" x-text="voteProgress.totalVoters"></span>
                    </p>

                    <p class="mt-2 text-center text-sm" :class="readerHasChosen ? 'text-emerald-600' : 'text-stone-400'">
                        <span x-show="!readerHasChosen">Ждём, пока чтец выберет эмоцию…</span>
                        <span x-show="readerHasChosen">Чтец готов</span>
                    </p>

                    <details class="mt-6">
                        <summary class="cursor-pointer text-sm font-medium text-stone-500">Сменить чтеца</summary>
                        <div class="mt-2 flex flex-wrap gap-2">
                            <template x-for="p in lobby.players.filter(pl => pl.id !== round.reader.id)" :key="p.id">
                                <button
                                    type="button"
                                    @click="reassignReader(p.id)"
                                    class="rounded-full border border-stone-300 px-3 py-1.5 text-sm text-stone-700 transition hover:bg-stone-100 active:scale-95"
                                    x-text="p.name"
                                ></button>
                            </template>
                        </div>
                    </details>
                </div>
            </template>

            <template x-if="screen === 'interrogating'">
                <div class="animate-pop-in">
                    <h2 class="text-lg font-semibold text-stone-700">Допрос</h2>
                    <p class="mt-1 text-sm text-stone-500">Наименьшая группа не угадала чтеца — пусть объяснятся.</p>

                    <div class="mt-4 space-y-2">
                        <template x-for="entry in interrogation.entries" :key="entry.player.id">
                            <div
                                class="flex animate-drop-in items-center justify-between rounded-xl border p-3 transition-colors"
                                :class="entry.isCurrent ? 'border-amber-400 bg-amber-50' : 'border-stone-200'"
                            >
                                <div class="flex items-center gap-2">
                                    <div class="inline-flex size-8 items-center justify-center rounded-full text-white" :class="'bg-' + entry.player.avatarColor + '-500'">
                                        <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use :href="'#icon-' + entry.player.avatar"></use></svg>
                                    </div>
                                    <span class="text-sm font-medium text-stone-700" x-text="entry.player.name"></span>
                                </div>

                                <template x-if="entry.sparksAwarded !== null">
                                    <span class="animate-pop-in text-sm font-semibold text-amber-600" x-text="'+' + entry.sparksAwarded"></span>
                                </template>

                                <div class="flex gap-1.5" x-show="entry.isCurrent" x-transition>
                                    <template x-for="value in [0, 1, 3, 5]" :key="value">
                                        <button
                                            type="button"
                                            @click="awardInterrogation(entry, value)"
                                            class="size-9 rounded-full bg-stone-800 text-sm font-semibold text-white transition hover:bg-stone-700 active:scale-90"
                                            x-text="value"
                                        ></button>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>

                    <button
                        type="button"
                        @click="skipInterrogation"
                        class="mt-4 text-sm font-medium text-stone-400 underline active:scale-95"
                    >
                        Пропустить допрос
                    </button>
                </div>
            </template>
        </main>

        <footer class="mt-8 flex flex-col gap-2 border-t border-stone-200 pt-6" x-show="screen !== 'closed'" x-cloak>
            <button
                type="button"
                @click="startRound"
                x-show="screen === 'idle' || screen === 'revealed'"
                :disabled="working || lobby.players.length < 2 || roundLimitReached"
                class="w-full rounded-full bg-amber-500 py-3 font-semibold text-white shadow-md shadow-amber-500/30 transition hover:bg-amber-600 active:scale-95 disabled:opacity-50 disabled:active:scale-100"
                x-text="lobby.roundsPlayed > 0 ? 'Следующий раунд' : 'Начать раунд'"
            ></button>

            <template x-if="screen === 'voting'">
                <div class="flex flex-col gap-2">
                    <button
                        type="button"
                        @click="revealRound"
                        :disabled="working || !readerHasChosen"
                        class="w-full rounded-full bg-amber-500 py-3 font-semibold text-white shadow-md shadow-amber-500/30 transition hover:bg-amber-600 active:scale-95 disabled:opacity-50 disabled:active:scale-100"
                    >
                        Показать результаты
                    </button>
                    <button type="button" @click="cancelRound" :disabled="working" class="w-full rounded-full border border-red-300 py-3 text-sm font-medium text-red-600 transition hover:bg-red-50 active:scale-95">
                        Отменить раунд
                    </button>
                </div>
            </template>

            <button type="button" @click="closeLobby" class="w-full rounded-full border border-stone-300 py-3 text-sm font-medium text-stone-600 transition hover:bg-stone-100 active:scale-95">
                Закрыть лобби
            </button>
            <a :href="newSessionUrl" class="w-full rounded-full border border-stone-300 py-3 text-center text-sm font-medium text-stone-600 transition hover:bg-stone-100 active:scale-95">
                Новая сессия
            </a>
        </footer>
    </div>

    @push('scripts')
        @vite('resources/js/host-panel.js')
    @endpush
</x-layout>
