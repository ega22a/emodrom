document.addEventListener('alpine:init', () => {
    const REVEAL_STEP_MS = 900;

    Alpine.data('screenDisplay', (initialState, lobbyCode) => ({
        lobby: initialState.lobby,
        round: initialState.round,
        lastResult: initialState.lastResult,
        interrogation: initialState.interrogation,
        voteProgress: { votedCount: 0, totalVoters: Math.max(initialState.lobby.players.length - 1, 0) },
        revealedGroupCount: initialState.lastResult ? initialState.lastResult.groups.length : 0,
        revealAnimationDone: Boolean(initialState.lastResult),
        musicEnabled: JSON.parse(localStorage.getItem('emodrom.musicEnabled') ?? 'true'),

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
                    }
                })
                .listen('.round.started', (event) => {
                    this.round = { number: event.roundNumber, status: 'voting', reader: event.reader, question: event.question };
                    this.voteProgress = { votedCount: 0, totalVoters: Math.max(this.lobby.players.length - 1, 0) };
                    this.lastResult = null;
                })
                .listen('.vote.progress-updated', (event) => {
                    this.voteProgress = event.progress;
                })
                .listen('.round.revealed', (event) => {
                    this.round = null;
                    this.lastResult = event.result;
                    this.playReveal();
                })
                .listen('.interrogation.updated', (event) => {
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
                });
        },

        playReveal() {
            this.revealedGroupCount = 0;
            this.revealAnimationDone = false;

            this.lastResult.groups.forEach((_, index) => {
                setTimeout(() => {
                    this.revealedGroupCount = index + 1;

                    if (index === this.lastResult.groups.length - 1) {
                        setTimeout(() => {
                            this.revealAnimationDone = true;
                        }, REVEAL_STEP_MS);
                    }
                }, index * REVEAL_STEP_MS);
            });

            if (this.lastResult.groups.length === 0) {
                this.revealAnimationDone = true;
            }
        },

        toggleMusic() {
            this.musicEnabled = !this.musicEnabled;
            localStorage.setItem('emodrom.musicEnabled', JSON.stringify(this.musicEnabled));
            this.syncMusic();
        },

        syncMusic() {
            const music = this.$refs.music;

            if (this.musicEnabled && this.screen === 'idle') {
                music.play().catch(() => {});
            } else {
                music.pause();
            }
        },
    }));
});
