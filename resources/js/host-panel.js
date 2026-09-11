document.addEventListener('alpine:init', () => {
    Alpine.data('hostPanel', (initialState, lobbyCode) => ({
        lobby: initialState.lobby,
        round: initialState.round,
        lastResult: initialState.lastResult,
        interrogation: initialState.interrogation,
        voteProgress: { votedCount: 0, totalVoters: Math.max(initialState.lobby.players.length - 1, 0) },
        readerHasChosen: false,
        working: false,
        scoredInterrogationIds: new Set(
            (initialState.interrogation?.entries ?? [])
                .filter((entry) => entry.sparksAwarded !== null)
                .map((entry) => entry.id)
        ),

        get screen() {
            if (this.lobby.status === 'closed') {
                return 'closed';
            }

            if (this.round && this.round.status === 'voting') {
                return 'voting';
            }

            if (this.interrogation && this.lobby.interrogationEnabled && !this.interrogation.finished) {
                return 'interrogating';
            }

            return this.lastResult ? 'revealed' : 'idle';
        },

        get roundLimitReached() {
            return this.lobby.roundLimit !== null && this.lobby.roundsPlayed >= this.lobby.roundLimit;
        },

        get newSessionUrl() {
            return `/host/${lobbyCode}/setup`;
        },

        init() {
            window.Echo.channel(`lobby.${lobbyCode}`)
                .listen('.player.joined', (event) => {
                    if (!this.lobby.players.some((p) => p.id === event.player.id)) {
                        this.lobby.players.push(event.player);
                    }
                })
                .listen('.round.started', (event) => {
                    this.round = { number: event.roundNumber, status: 'voting', reader: event.reader, question: event.question };
                    this.voteProgress = { votedCount: 0, totalVoters: Math.max(this.lobby.players.length - 1, 0) };
                    this.readerHasChosen = false;
                    this.lobby.roundsPlayed = event.roundNumber - 1;
                })
                .listen('.reader.submitted', () => {
                    this.readerHasChosen = true;
                })
                .listen('.vote.progress-updated', (event) => {
                    this.voteProgress = event.progress;
                })
                .listen('.round.revealed', (event) => {
                    this.lastResult = event.result;
                    this.lobby.roundsPlayed = event.result.roundNumber;
                    this.round = null;

                    event.result.rewardedPlayerIds.forEach((id) => {
                        this.creditPlayer(id, event.result.rewardSparks);
                    });
                })
                .listen('.interrogation.updated', (event) => {
                    this.interrogation = event.state;

                    event.state.entries.forEach((entry) => {
                        if (entry.sparksAwarded !== null && !this.scoredInterrogationIds.has(entry.id)) {
                            this.scoredInterrogationIds.add(entry.id);
                            this.creditPlayer(entry.player.id, entry.sparksAwarded);
                        }
                    });
                })
                .listen('.round.cancelled', () => {
                    this.round = null;
                })
                .listen('.reader.reassigned', (event) => {
                    if (this.round) {
                        this.round.reader = event.reader;
                        this.readerHasChosen = false;
                    }
                })
                .listen('.lobby.closed', () => {
                    this.lobby.status = 'closed';
                });
        },

        creditPlayer(playerId, sparks) {
            const player = this.lobby.players.find((p) => p.id === playerId);

            if (player) {
                player.sparksBalance += sparks;
            }
        },

        async startRound() {
            this.working = true;

            try {
                await window.api(`/host/${lobbyCode}/round`, { method: 'POST' });
            } catch (error) {
                alert(error.message);
            } finally {
                this.working = false;
            }
        },

        async revealRound() {
            this.working = true;

            try {
                await window.api(`/host/${lobbyCode}/round/reveal`, { method: 'POST' });
            } catch (error) {
                alert(error.message);
            } finally {
                this.working = false;
            }
        },

        async cancelRound() {
            if (!confirm('Отменить текущий раунд? Голоса не будут засчитаны.')) {
                return;
            }

            this.working = true;

            try {
                await window.api(`/host/${lobbyCode}/round/cancel`, { method: 'POST' });
            } catch (error) {
                alert(error.message);
            } finally {
                this.working = false;
            }
        },

        async reassignReader(playerId) {
            try {
                await window.api(`/host/${lobbyCode}/round/reader`, {
                    method: 'POST',
                    body: JSON.stringify({ player_id: playerId }),
                });
            } catch (error) {
                alert(error.message);
            }
        },

        async awardInterrogation(entry, sparks) {
            try {
                await window.api(`/host/${lobbyCode}/interrogation/${entry.id}/award`, {
                    method: 'POST',
                    body: JSON.stringify({ sparks }),
                });
            } catch (error) {
                alert(error.message);
            }
        },

        async skipInterrogation() {
            try {
                await window.api(`/host/${lobbyCode}/interrogation/skip`, { method: 'POST' });
            } catch (error) {
                alert(error.message);
            }
        },

        async closeLobby() {
            if (!confirm('Закрыть лобби? Игра завершится для всех игроков.')) {
                return;
            }

            try {
                await window.api(`/host/${lobbyCode}/close`, { method: 'POST' });
            } catch (error) {
                alert(error.message);
            }
        },
    }));
});
