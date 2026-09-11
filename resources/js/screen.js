document.addEventListener('alpine:init', () => {
    const REVEAL_STEP_MS = 900;

    Alpine.data('screenDisplay', (initialState, lobbyCode, sfxBaseUrl) => ({
        lobby: initialState.lobby,
        round: initialState.round,
        lastResult: initialState.lastResult,
        interrogation: initialState.interrogation,
        voteProgress: { votedCount: 0, totalVoters: Math.max(initialState.lobby.players.length - 1, 0) },
        revealedGroupCount: initialState.lastResult ? initialState.lastResult.groups.length : 0,
        revealAnimationDone: Boolean(initialState.lastResult),
        soundEnabled: JSON.parse(localStorage.getItem('emodrom.soundEnabled') ?? 'true'),

        get screen() {
            if (this.lobby.status === 'closed') {
                return 'closed';
            }

            if (this.round && this.round.status === 'voting') {
                return 'voting';
            }

            if (this.lastResult && !this.revealAnimationDone) {
                return 'revealing';
            }

            if (this.interrogation && this.lobby.interrogationEnabled && !this.interrogation.finished) {
                return 'interrogating';
            }

            return this.lastResult ? 'revealed' : 'idle';
        },

        get roundNumber() {
            return this.round?.number ?? this.lastResult?.roundNumber ?? 0;
        },

        get maxVotes() {
            if (!this.lastResult || this.lastResult.groups.length === 0) {
                return 1;
            }

            return Math.max(...this.lastResult.groups.map((g) => g.votesCount), 1);
        },

        get currentInterrogationTarget() {
            return this.interrogation?.entries.find((entry) => entry.isCurrent) ?? null;
        },

        init() {
            ['click', 'keydown', 'touchstart'].forEach((event) => {
                document.addEventListener(event, () => this.syncMusic(), { once: true });
            });

            window.Echo.channel(`lobby.${lobbyCode}`)
                .listen('.player.joined', (event) => {
                    if (!this.lobby.players.some((p) => p.id === event.player.id)) {
                        this.lobby.players.push(event.player);
                        this.playSfx('player_joined');
                    }
                })
                .listen('.round.started', (event) => {
                    this.round = { number: event.roundNumber, status: 'voting', reader: event.reader, question: event.question };
                    this.voteProgress = { votedCount: 0, totalVoters: Math.max(this.lobby.players.length - 1, 0) };
                    this.lastResult = null;
                    this.playSfx('game_start');
                })
                .listen('.vote.progress-updated', (event) => {
                    this.voteProgress = event.progress;
                    this.playSfx('voice_get');
                })
                .listen('.round.revealed', (event) => {
                    this.round = null;
                    this.lastResult = event.result;
                    this.playReveal();
                })
                .listen('.interrogation.updated', (event) => {
                    const isNewInterrogation = !this.interrogation || this.interrogation.roundNumber !== event.state.roundNumber;

                    if (isNewInterrogation) {
                        this.playSfx('drum_roll');
                    } else {
                        this.playInterrogationAwardSfx(this.interrogation, event.state);
                    }

                    this.interrogation = event.state;
                })
                .listen('.round.cancelled', () => {
                    this.round = null;
                })
                .listen('.reader.reassigned', (event) => {
                    if (this.round) {
                        this.round.reader = event.reader;
                    }
                })
                .listen('.lobby.closed', () => {
                    this.lobby.status = 'closed';
                    this.playSfx('game_over');
                });
        },

        playReveal() {
            this.revealedGroupCount = 0;
            this.revealAnimationDone = false;
            this.playSfx('reveal_start');

            this.lastResult.groups.forEach((group, index) => {
                setTimeout(() => {
                    this.revealedGroupCount = index + 1;

                    if (group.matchedReader) {
                        this.playSfx('success_chime');
                    }

                    if (index === this.lastResult.groups.length - 1) {
                        setTimeout(() => {
                            this.revealAnimationDone = true;

                            if (this.lastResult.readerTookReward) {
                                this.playSfx('wrong_answer');
                            }
                        }, REVEAL_STEP_MS);
                    }
                }, index * REVEAL_STEP_MS);
            });

            if (this.lastResult.groups.length === 0) {
                this.revealAnimationDone = true;

                if (this.lastResult.readerTookReward) {
                    this.playSfx('wrong_answer');
                }
            }
        },

        playInterrogationAwardSfx(previous, next) {
            for (const entry of next.entries) {
                if (entry.sparksAwarded === null) {
                    continue;
                }

                const previousEntry = previous?.entries.find((e) => e.id === entry.id);

                if (previousEntry && previousEntry.sparksAwarded !== null) {
                    continue;
                }

                this.playSfx(entry.sparksAwarded >= 3 ? 'coin_drop' : 'coin_small');
            }
        },

        toggleSound() {
            this.soundEnabled = !this.soundEnabled;
            localStorage.setItem('emodrom.soundEnabled', JSON.stringify(this.soundEnabled));
            this.syncMusic();
        },

        syncMusic() {
            const music = this.$refs.music;

            if (this.soundEnabled && this.screen === 'idle') {
                music.play().catch(() => {});
            } else {
                music.pause();
            }
        },

        playSfx(name) {
            if (!this.soundEnabled) {
                return;
            }

            new Audio(`${sfxBaseUrl}/${name}.wav`).play().catch(() => {});
        },
    }));
});
