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
                    class="flex size-11 items-center justify-center rounded-full bg-white text-stone-500 shadow-sm transition hover:text-stone-700 active:scale-90"
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

        <div
            x-show="!soundUnlocked && soundEnabled"
            x-transition
            x-cloak
            class="fixed inset-x-0 bottom-0 z-50 flex items-center justify-center gap-3 bg-stone-900/90 px-6 py-4 text-center text-white"
        >
            <x-icon name="music-2" class="size-5 shrink-0" />
            <span class="font-medium">Нажмите в любом месте экрана, чтобы включить звук и музыку</span>
        </div>

        <main class="mt-10 flex flex-1 flex-col items-center justify-center">
            <template x-if="screen === 'closed'">
                <div class="animate-pop-in text-center">
                    <x-icon name="party-popper" class="mx-auto size-20 text-stone-400" />
                    <h2 class="mt-6 text-4xl font-bold text-stone-900">Игра завершена</h2>
                    <p class="mt-2 text-lg text-stone-500">Спасибо за игру!</p>
                </div>
            </template>

            <template x-if="screen === 'idle'">
                <div class="grid w-full max-w-4xl animate-pop-in grid-cols-1 items-center gap-12 lg:grid-cols-[320px_1fr]">
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
                <div class="w-full max-w-3xl animate-pop-in text-center">
                    <div class="inline-flex items-center gap-2 rounded-full bg-white px-5 py-2.5 shadow-sm">
                        <div class="inline-flex size-9 items-center justify-center rounded-full text-white" :class="'bg-' + round.reader.avatarColor + '-500'">
                            <svg class="size-4.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use :href="'#icon-' + round.reader.avatar"></use></svg>
                        </div>
                        <span class="text-lg font-semibold text-stone-700">Читает: <span x-text="round.reader.name"></span></span>
                    </div>

                    <div class="mt-3 flex justify-center gap-2" x-show="round.mirrorEnabled || round.cocktailEnabled">
                        <span x-show="round.mirrorEnabled" class="rounded-full bg-white px-3 py-1 text-base font-semibold text-stone-600 shadow-sm">🪞 Зеркало</span>
                        <span x-show="round.cocktailEnabled" class="rounded-full bg-white px-3 py-1 text-base font-semibold text-stone-600 shadow-sm">🍹 Коктейль</span>
                    </div>

                    <p class="mt-8 text-4xl font-semibold leading-snug text-stone-900" x-text="round.question.situation"></p>
                    <p class="mt-4 text-xl text-amber-600">Награда: +<span x-text="round.question.rewardSparks"></span> искр</p>

                    <div class="mx-auto mt-10 h-4 w-full max-w-md overflow-hidden rounded-full bg-white">
                        <div
                            class="h-full rounded-full bg-amber-400 transition-all duration-500"
                            :style="`width: ${voteProgress.totalVoters ? (voteProgress.votedCount / voteProgress.totalVoters) * 100 : 0}%`"
                        ></div>
                    </div>
                    <p class="mt-3 text-xl text-stone-500">
                        Ответили <span class="font-semibold text-stone-700" x-text="voteProgress.votedCount"></span>
                        из <span class="font-semibold text-stone-700" x-text="voteProgress.totalVoters"></span>
                    </p>
                </div>
            </template>

            <template x-if="screen === 'revealing' || screen === 'interrogating' || screen === 'revealed'">
                <div class="w-full max-w-5xl">
                    <div class="flex flex-wrap items-end justify-center gap-4 sm:gap-6">
                        <template x-for="(group, idx) in lastResult.groups" :key="group.emotion.id">
                            <div
                                x-show="idx < revealedGroupCount"
                                x-data="{ grown: false }"
                                x-init="$nextTick(() => setTimeout(() => grown = true, 30))"
                                class="flex w-32 flex-col items-center sm:w-40"
                            >
                                <div class="mb-2 flex min-h-10 flex-wrap items-center justify-center gap-1">
                                    <template x-for="voter in group.voters" :key="voter.id">
                                        <div class="inline-flex size-8 animate-drop-in items-center justify-center rounded-full text-white sm:size-9" :class="'bg-' + voter.avatarColor + '-500'">
                                            <svg class="size-4 sm:size-4.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use :href="'#icon-' + voter.avatar"></use></svg>
                                        </div>
                                    </template>
                                </div>

                                <span class="text-3xl font-black text-stone-800 sm:text-4xl" x-text="group.votesCount"></span>

                                <div class="mt-2 flex h-56 w-full items-end justify-center overflow-hidden rounded-t-2xl bg-white shadow-inner sm:h-72">
                                    <div
                                        class="w-full rounded-t-2xl transition-all duration-700 ease-out"
                                        :class="'bg-' + group.emotion.color + '-500' + (group.matchedReader ? ' ring-4 ring-amber-400' : '')"
                                        :style="`height: ${grown ? Math.max((group.votesCount / maxVotes) * 100, 6) : 0}%`"
                                    ></div>
                                </div>

                                <div class="mt-3 flex flex-col items-center gap-1.5">
                                    <div class="inline-flex size-11 shrink-0 items-center justify-center rounded-full text-white sm:size-12" :class="'bg-' + group.emotion.color + '-500'">
                                        <svg class="size-5 sm:size-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use :href="'#icon-' + group.emotion.icon"></use></svg>
                                    </div>
                                    <span class="text-center text-lg font-bold text-stone-800 sm:text-xl" x-text="group.emotion.label"></span>
                                    <template x-if="group.matchedReader">
                                        <span class="inline-flex animate-pop-in items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-xs font-bold text-amber-700">
                                            <x-icon name="sparkles" class="size-3.5" /> ЧТЕЦ
                                        </span>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>

                    <p x-show="revealAnimationDone && lastResult.abstainers.length > 0" x-cloak class="mt-6 text-center text-lg text-stone-400">
                        Не ответили: <span x-text="lastResult.abstainers.map(a => a.avatar).join(', ')"></span>
                    </p>

                    <div x-show="revealAnimationDone" x-cloak x-transition class="mt-6 rounded-2xl bg-emerald-50 px-5 py-4 text-center text-xl text-emerald-800">
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
