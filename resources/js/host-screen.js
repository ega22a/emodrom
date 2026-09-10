document.addEventListener('alpine:init', () => {
    Alpine.data('hostScreen', (initialState, lobbyCode) => ({
        lobby: initialState.lobby,
        round: initialState.round,
        voteStats: initialState.voteStats ?? { stats: [], totalPlayers: 0, totalVotes: 0 },
        lastResult: null,
        working: false,

        get screen() {
            if (this.lobby.status === 'closed') {
                return 'closed';
            }

            return this.round && this.round.status === 'active' ? 'round' : 'lobby';
        },

        init() {
            window.Echo.channel(`lobby.${lobbyCode}`)
                .listen('.player.joined', (event) => {
                    if (!this.lobby.players.some((p) => p.id === event.player.id)) {
                        this.lobby.players.push(event.player);
                    }
                })
                .listen('.round.started', (event) => {
                    this.round = { number: event.roundNumber, status: 'active' };
                    this.voteStats = {
                        roundNumber: event.roundNumber,
                        stats: [],
                        totalPlayers: this.lobby.players.length,
                        totalVotes: 0,
                    };
                })
                .listen('.round.ended', (event) => {
                    this.lastResult = event.result;
                })
                .listen('.vote.stats-updated', (event) => {
                    this.voteStats = event.stats;
                })
                .listen('.lobby.closed', () => {
                    this.lobby.status = 'closed';
                });
        },

        async startRound() {
            this.working = true;

            try {
                await window.api(`/lobbies/${lobbyCode}/rounds`, { method: 'POST' });
            } catch (error) {
                alert(error.message);
            } finally {
                this.working = false;
            }
        },

        async closeLobby() {
            if (!confirm('Закрыть лобби? Игра завершится для всех игроков.')) {
                return;
            }

            this.working = true;

            try {
                await window.api(`/lobbies/${lobbyCode}/close`, { method: 'POST' });
            } catch (error) {
                alert(error.message);
            } finally {
                this.working = false;
            }
        },
    }));
});
