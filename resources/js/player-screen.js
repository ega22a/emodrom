document.addEventListener('alpine:init', () => {
    Alpine.data('playerScreen', (initialState, lobbyCode) => ({
        lobbyStatus: initialState.lobbyStatus,
        player: initialState.player,
        round: initialState.round,
        availableEmotions: initialState.availableEmotions,
        hasVoted: initialState.hasVoted,
        votedEmotionId: initialState.votedEmotionId,
        voting: false,
        toast: null,

        get screen() {
            if (this.lobbyStatus === 'closed') {
                return 'closed';
            }

            if (!this.round || this.round.status !== 'active') {
                return 'waiting';
            }

            return this.hasVoted ? 'voted' : 'voting';
        },

        init() {
            window.Echo.channel(`lobby.${lobbyCode}`)
                .listen('.round.started', () => {
                    this.toast = null;
                    this.refresh();
                })
                .listen('.round.ended', (event) => {
                    if (event.result.rewardedPlayerIds.includes(this.player.id)) {
                        this.toast = 'Вы открыли новую эмоцию! Она появится в следующем раунде.';
                    }
                })
                .listen('.lobby.closed', () => {
                    this.lobbyStatus = 'closed';
                });
        },

        async refresh() {
            const state = await window.api(`/play/${lobbyCode}/state`);

            this.lobbyStatus = state.lobbyStatus;
            this.round = state.round;
            this.availableEmotions = state.availableEmotions;
            this.hasVoted = state.hasVoted;
            this.votedEmotionId = state.votedEmotionId;
        },

        async vote(emotion) {
            if (this.voting || this.hasVoted) {
                return;
            }

            this.voting = true;

            try {
                await window.api(`/play/${lobbyCode}/vote`, {
                    method: 'POST',
                    body: JSON.stringify({ emotion_id: emotion.id }),
                });

                this.hasVoted = true;
                this.votedEmotionId = emotion.id;
            } catch (error) {
                alert(error.message);
            } finally {
                this.voting = false;
            }
        },
    }));
});
