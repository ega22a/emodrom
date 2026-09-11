document.addEventListener('alpine:init', () => {
    Alpine.data('playerScreen', (initialState, lobbyCode) => ({
        lobbyStatus: initialState.lobbyStatus,
        removed: initialState.removed,
        player: initialState.player,
        round: initialState.round,
        isReader: initialState.isReader,
        readerHasChosen: initialState.readerHasChosen,
        hasVoted: initialState.hasVoted,
        votedEmotionId: initialState.votedEmotionId,
        availableEmotions: initialState.availableEmotions,
        isBeingInterrogated: initialState.isBeingInterrogated,
        voting: false,
        submitting: false,
        buying: false,
        toast: null,
        cocktailPick: null,

        get screen() {
            if (this.removed) {
                return 'removed';
            }

            if (this.lobbyStatus === 'closed') {
                return 'closed';
            }

            if (this.isBeingInterrogated) {
                return 'interrogated';
            }

            if (!this.round) {
                return 'waiting';
            }

            if (this.isReader) {
                return this.readerHasChosen ? 'reading-done' : 'reading';
            }

            return this.hasVoted ? 'voted' : 'voting';
        },

        init() {
            window.Echo.channel(`lobby.${lobbyCode}`)
                .listen('.round.started', () => {
                    this.toast = null;
                    this.cocktailPick = null;
                    this.refresh();
                })
                .listen('.reader.reassigned', () => {
                    this.refresh();
                })
                .listen('.round.revealed', (event) => {
                    if (event.result.rewardedPlayerIds.includes(this.player.id)) {
                        this.toast = `Вы заработали ${event.result.rewardSparks} искр!`;
                    }

                    this.refresh();
                })
                .listen('.interrogation.updated', (event) => {
                    this.isBeingInterrogated = event.state.entries.some(
                        (entry) => entry.isCurrent && entry.player.id === this.player.id
                    );
                })
                .listen('.player.removed', (event) => {
                    if (event.playerId === this.player.id) {
                        this.removed = true;
                    }
                })
                .listen('.lobby.closed', () => {
                    this.lobbyStatus = 'closed';
                });
        },

        async refresh() {
            const state = await window.api(`/play/${lobbyCode}/state`);

            this.lobbyStatus = state.lobbyStatus;
            this.player = state.player;
            this.round = state.round;
            this.isReader = state.isReader;
            this.readerHasChosen = state.readerHasChosen;
            this.hasVoted = state.hasVoted;
            this.votedEmotionId = state.votedEmotionId;
            this.availableEmotions = state.availableEmotions;
            this.isBeingInterrogated = state.isBeingInterrogated;
        },

        async vote(emotion) {
            if (this.voting || this.hasVoted) {
                return;
            }

            if (this.round?.cocktailEnabled) {
                if (this.cocktailPick && this.cocktailPick.id === emotion.id) {
                    this.cocktailPick = null;
                    return;
                }

                if (!this.cocktailPick) {
                    this.cocktailPick = emotion;
                    return;
                }

                await this.submitVote(this.cocktailPick, emotion);
                return;
            }

            await this.submitVote(emotion, null);
        },

        async submitVote(emotion, secondaryEmotion) {
            this.voting = true;

            try {
                await window.api(`/play/${lobbyCode}/vote`, {
                    method: 'POST',
                    body: JSON.stringify({
                        emotion_id: emotion.id,
                        secondary_emotion_id: secondaryEmotion ? secondaryEmotion.id : null,
                    }),
                });

                this.hasVoted = true;
                this.votedEmotionId = emotion.id;
                this.cocktailPick = null;
            } catch (error) {
                this.cocktailPick = null;
                alert(error.message);
            } finally {
                this.voting = false;
            }
        },

        async submitReaderEmotion(emotion) {
            if (this.submitting || this.readerHasChosen) {
                return;
            }

            this.submitting = true;

            try {
                await window.api(`/play/${lobbyCode}/reader-emotion`, {
                    method: 'POST',
                    body: JSON.stringify({ emotion_id: emotion.id }),
                });

                this.readerHasChosen = true;
            } catch (error) {
                alert(error.message);
            } finally {
                this.submitting = false;
            }
        },

        async buyEmotion() {
            if (this.buying || this.player.sparksBalance < 3) {
                return;
            }

            this.buying = true;

            try {
                const result = await window.api(`/play/${lobbyCode}/purchase`, { method: 'POST' });

                this.player.sparksBalance = result.sparksBalance;
                this.availableEmotions.push(result.emotion);
                this.toast = `Вы открыли новую эмоцию: ${result.emotion.label}!`;
            } catch (error) {
                alert(error.message);
            } finally {
                this.buying = false;
            }
        },
    }));
});
