<x-layout :title="$lobby->code.' — экран'">
    <div
        class="flex min-h-dvh flex-col p-8 sm:p-12"
        x-data="screenDisplay(@js($state), @js($lobby->code), @js(asset('static/assets/sfx')))"
    >
        <header class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="flex size-14 shrink-0 items-center justify-center rounded-full bg-amber-500 text-white">
                    <x-icon name="bug" class="size-7" />
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-stone-900">Эмодром</h1>
                    <p class="text-stone-500" x-show="round || screen === 'revealing' || screen === 'interrogating'" x-cloak>
                        Раунд <span x-text="roundNumber"></span><template x-if="lobby.roundLimit"><span> из <span x-text="lobby.roundLimit"></span></span></template>
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button
                    type="button"
                    @click="toggleSound()"
                    class="flex size-11 items-center justify-center rounded-full bg-white text-stone-500 shadow-sm transition hover:text-stone-700"
                    :class="soundEnabled ? 'text-amber-600' : ''"
                    :aria-label="soundEnabled ? 'Выключить звук' : 'Включить звук'"
                >
                    <x-icon name="music-2" class="size-5" />
                </button>
                <img src="{{ route('lobbies.qr', $lobby) }}" alt="QR-код лобби" class="size-16 rounded-lg border border-stone-200 bg-white p-1">
                <span class="rounded-full bg-white px-6 py-3 text-2xl font-bold tracking-[0.2em] text-amber-600 shadow-sm">{{ $lobby->code }}</span>
            </div>
        </header>

        <audio x-ref="music" src="{{ asset('static/assets/sfx/lobby.wav') }}" loop preload="auto" x-effect="syncMusic()"></audio>

        <main class="mt-10 flex flex-1 flex-col items-center justify-center">
            <template x-if="screen === 'closed'">
                <div class="text-center">
                    <x-icon name="party-popper" class="mx-auto size-20 text-stone-400" />
                    <h2 class="mt-6 text-4xl font-bold text-stone-900">Игра завершена</h2>
                    <p class="mt-2 text-lg text-stone-500">Спасибо за игру!</p>
                </div>
            </template>

            <template x-if="screen === 'idle'">
                <div class="grid w-full max-w-4xl grid-cols-1 items-center gap-12 lg:grid-cols-[320px_1fr]">
                    <div class="rounded-3xl bg-white p-8 text-center shadow-sm">
                        <img src="{{ route('lobbies.qr', $lobby) }}" alt="QR-код лобби" class="mx-auto size-56">
                        <p class="mt-4 text-sm text-stone-500">Отсканируйте камерой телефона, чтобы присоединиться</p>
                    </div>

                    <div>
                        <h2 class="mb-4 text-xl font-semibold text-stone-700">
                            Игроки (<span x-text="lobby.players.length"></span>)
                        </h2>
                        <p x-show="lobby.players.length === 0" x-cloak class="text-stone-400">Ждём первых игроков…</p>
                        <div class="flex flex-wrap gap-5">
                            <template x-for="p in lobby.players" :key="p.id">
                                <div class="flex animate-drop-in flex-col items-center gap-2">
                                    <div class="inline-flex size-16 items-center justify-center rounded-full text-white shadow-sm" :class="'bg-' + p.avatarColor + '-500'">
                                        <svg class="size-8" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use :href="'#icon-' + p.avatar"></use></svg>
                                    </div>
                                    <span class="max-w-20 truncate text-sm font-medium text-stone-600" x-text="p.name"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </template>

            <template x-if="screen === 'voting'">
                <div class="w-full max-w-2xl text-center">
                    <div class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 shadow-sm">
                        <div class="inline-flex size-8 items-center justify-center rounded-full text-white" :class="'bg-' + round.reader.avatarColor + '-500'">
                            <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use :href="'#icon-' + round.reader.avatar"></use></svg>
                        </div>
                        <span class="font-semibold text-stone-700">Читает: <span x-text="round.reader.name"></span></span>
                    </div>

                    <p class="mt-8 text-3xl font-semibold leading-snug text-stone-900" x-text="round.question.situation"></p>
                    <p class="mt-4 text-amber-600">Награда: +<span x-text="round.question.rewardSparks"></span> искр</p>

                    <div class="mx-auto mt-10 h-3 w-full max-w-md overflow-hidden rounded-full bg-white">
                        <div
                            class="h-full rounded-full bg-amber-400 transition-all duration-500"
                            :style="`width: ${voteProgress.totalVoters ? (voteProgress.votedCount / voteProgress.totalVoters) * 100 : 0}%`"
                        ></div>
                    </div>
                    <p class="mt-3 text-stone-500">
                        Ответили <span class="font-semibold text-stone-700" x-text="voteProgress.votedCount"></span>
                        из <span class="font-semibold text-stone-700" x-text="voteProgress.totalVoters"></span>
                    </p>
                </div>
            </template>

            <template x-if="screen === 'revealing' || screen === 'interrogating' || screen === 'revealed'">
                <div class="w-full max-w-3xl">
                    <div class="flex flex-col-reverse gap-4">
                        <template x-for="(group, idx) in lastResult.groups" :key="group.emotion.id">
                            <div
                                x-show="idx < revealedGroupCount"
                                x-data="{ grown: false }"
                                x-init="$nextTick(() => setTimeout(() => grown = true, 30))"
                                class="rounded-2xl bg-white p-4 shadow-sm"
                                :class="group.matchedReader ? 'ring-4 ring-amber-400' : ''"
                            >
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <template x-for="voter in group.voters" :key="voter.id">
                                        <div class="inline-flex size-9 animate-drop-in items-center justify-center rounded-full text-white" :class="'bg-' + voter.avatarColor + '-500'">
                                            <svg class="size-4.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use :href="'#icon-' + voter.avatar"></use></svg>
                                        </div>
                                    </template>
                                    <template x-if="group.matchedReader">
                                        <span class="ml-1 inline-flex animate-pop-in items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-xs font-bold text-amber-700">
                                            <x-icon name="sparkles" class="size-3.5" /> ЧТЕЦ
                                        </span>
                                    </template>
                                </div>

                                <div class="mt-3 flex items-center gap-3">
                                    <div class="inline-flex size-9 shrink-0 items-center justify-center rounded-full text-white" :class="'bg-' + group.emotion.color + '-500'">
                                        <svg class="size-4.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use :href="'#icon-' + group.emotion.icon"></use></svg>
                                    </div>
                                    <span class="font-semibold text-stone-800" x-text="group.emotion.label"></span>
                                    <span class="ml-auto text-lg font-bold text-stone-500" x-text="group.votesCount"></span>
                                </div>

                                <div class="mt-2 h-3 overflow-hidden rounded-full bg-stone-100">
                                    <div
                                        class="h-full rounded-full transition-all duration-700 ease-out"
                                        :class="'bg-' + group.emotion.color + '-500'"
                                        :style="`width: ${grown ? (group.votesCount / maxVotes) * 100 : 0}%`"
                                    ></div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <p x-show="revealAnimationDone && lastResult.abstainers.length > 0" x-cloak class="mt-4 text-center text-sm text-stone-400">
                        Не ответили: <span x-text="lastResult.abstainers.map(a => a.avatar).join(', ')"></span>
                    </p>

                    <div x-show="revealAnimationDone" x-cloak x-transition class="mt-6 rounded-2xl bg-emerald-50 px-5 py-4 text-center text-emerald-800">
                        <template x-if="!lastResult.readerTookReward">
                            <p>Совпало с чтецом (<strong x-text="lastResult.readerEmotion.label"></strong>): +<span x-text="lastResult.rewardSparks"></span> искр каждому угадавшему!</p>
                        </template>
                        <template x-if="lastResult.readerTookReward">
                            <p>Никто не выбрал <strong x-text="lastResult.readerEmotion.label"></strong> — чтец забирает <span x-text="lastResult.rewardSparks"></span> искр.</p>
                        </template>
                    </div>

                    <template x-if="screen === 'interrogating'">
                        <div class="mt-8 text-center">
                            <p class="text-sm font-bold uppercase tracking-widest text-red-500">Допрос</p>
                            <template x-if="currentInterrogationTarget">
                                <div class="mt-3 flex flex-col items-center gap-3">
                                    <div class="inline-flex size-20 animate-pop-in items-center justify-center rounded-full text-white shadow-lg" :class="'bg-' + currentInterrogationTarget.player.avatarColor + '-500'">
                                        <svg class="size-10" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use :href="'#icon-' + currentInterrogationTarget.player.avatar"></use></svg>
                                    </div>
                                    <p class="text-2xl font-bold text-stone-900" x-text="currentInterrogationTarget.player.name"></p>
                                    <p class="text-stone-500">Ведущий задаёт вопрос…</p>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </template>
        </main>
    </div>

    @push('scripts')
        @vite('resources/js/screen.js')
    @endpush
</x-layout>
