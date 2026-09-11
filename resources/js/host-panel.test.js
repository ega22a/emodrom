import { beforeEach, describe, expect, it, vi } from 'vitest';
import { hostPanel } from './host-panel.js';
import { createFakeEcho } from './testing/fake-echo.js';
import { makeInterrogationEntry, makeInterrogationState, makeLobby, makePlayer, makeRevealResult, makeRound } from './testing/fixtures.js';

function makeState(overrides = {}) {
    return {
        lobby: makeLobby({ players: [makePlayer({ id: 1, name: 'Alice' }), makePlayer({ id: 2, name: 'Bob' })] }),
        round: null,
        lastResult: null,
        interrogation: null,
        readerHasChosen: false,
        ...overrides,
    };
}

describe('hostPanel: screen getter', () => {
    it('is "closed" once the lobby is closed, regardless of anything else', () => {
        const panel = hostPanel(makeState({ lobby: makeLobby({ status: 'closed' }), round: makeRound() }), 'ABC123');

        expect(panel.screen).toBe('closed');
    });

    it('is "voting" while a round is in progress', () => {
        const panel = hostPanel(makeState({ round: makeRound({ status: 'voting' }) }), 'ABC123');

        expect(panel.screen).toBe('voting');
    });

    it('is "interrogating" when interrogation is enabled, active, and unfinished', () => {
        const panel = hostPanel(
            makeState({
                lobby: makeLobby({ interrogationEnabled: true }),
                interrogation: makeInterrogationState({ finished: false }),
            }),
            'ABC123'
        );

        expect(panel.screen).toBe('interrogating');
    });

    it('falls through to "revealed" once interrogation is finished', () => {
        const panel = hostPanel(
            makeState({
                lobby: makeLobby({ interrogationEnabled: true }),
                interrogation: makeInterrogationState({ finished: true }),
                lastResult: makeRevealResult(),
            }),
            'ABC123'
        );

        expect(panel.screen).toBe('revealed');
    });

    it('is "idle" with no round, no result, and no interrogation', () => {
        const panel = hostPanel(makeState(), 'ABC123');

        expect(panel.screen).toBe('idle');
    });
});

describe('hostPanel: initial state hydration', () => {
    it('reads readerHasChosen from the server-provided initial state, not a hardcoded default', () => {
        // Regression: this used to be hardcoded `false` in the component,
        // so a host reload after the reader had already picked left the
        // reveal button stuck disabled with no way to unstick it.
        const chosen = hostPanel(makeState({ readerHasChosen: true }), 'ABC123');
        const notChosen = hostPanel(makeState({ readerHasChosen: false }), 'ABC123');

        expect(chosen.readerHasChosen).toBe(true);
        expect(notChosen.readerHasChosen).toBe(false);
    });
});

describe('hostPanel: roundLimitReached', () => {
    it('is false when there is no round limit', () => {
        const panel = hostPanel(makeState({ lobby: makeLobby({ roundLimit: null, roundsPlayed: 99 }) }), 'ABC123');

        expect(panel.roundLimitReached).toBe(false);
    });

    it('is true once roundsPlayed reaches the limit', () => {
        const panel = hostPanel(makeState({ lobby: makeLobby({ roundLimit: 5, roundsPlayed: 5 }) }), 'ABC123');

        expect(panel.roundLimitReached).toBe(true);
    });

    it('is false while under the limit', () => {
        const panel = hostPanel(makeState({ lobby: makeLobby({ roundLimit: 5, roundsPlayed: 4 }) }), 'ABC123');

        expect(panel.roundLimitReached).toBe(false);
    });
});

describe('hostPanel: live updates via Echo', () => {
    let echo;

    beforeEach(() => {
        echo = createFakeEcho();
        vi.stubGlobal('Echo', echo);
    });

    it('adds a newly joined player exactly once, even if the event repeats', () => {
        const panel = hostPanel(makeState({ lobby: makeLobby({ players: [makePlayer({ id: 1 })] }) }), 'ABC123');
        panel.init();

        const newPlayer = makePlayer({ id: 2, name: 'Carol' });
        echo.emit('.player.joined', { player: newPlayer });
        echo.emit('.player.joined', { player: newPlayer });

        expect(panel.lobby.players).toHaveLength(2);
        expect(panel.lobby.players.filter((p) => p.id === 2)).toHaveLength(1);
    });

    it('drops a removed player from the roster', () => {
        const panel = hostPanel(
            makeState({ lobby: makeLobby({ players: [makePlayer({ id: 1 }), makePlayer({ id: 2 })] }) }),
            'ABC123'
        );
        panel.init();

        echo.emit('.player.removed', { playerId: 2 });

        expect(panel.lobby.players.map((p) => p.id)).toEqual([1]);
    });

    it('resets readerHasChosen and starts a fresh vote count when a round starts', () => {
        const panel = hostPanel(makeState({ readerHasChosen: true }), 'ABC123');
        panel.init();

        echo.emit('.round.started', {
            roundNumber: 3,
            reader: makePlayer(),
            question: { id: 1, situation: 'x', rewardSparks: 5 },
            mirrorEnabled: true,
            cocktailEnabled: false,
        });

        expect(panel.readerHasChosen).toBe(false);
        expect(panel.round.status).toBe('voting');
        expect(panel.round.mirrorEnabled).toBe(true);
        expect(panel.voteProgress).toEqual({ votedCount: 0, totalVoters: 1 });
        expect(panel.lobby.roundsPlayed).toBe(2);
    });

    it('credits every rewarded player when a round is revealed', () => {
        const panel = hostPanel(
            makeState({
                lobby: makeLobby({ players: [makePlayer({ id: 1, sparksBalance: 0 }), makePlayer({ id: 2, sparksBalance: 0 })] }),
                round: makeRound(),
            }),
            'ABC123'
        );
        panel.init();

        echo.emit('.round.revealed', {
            result: makeRevealResult({ roundNumber: 1, rewardSparks: 5, rewardedPlayerIds: [1, 2] }),
        });

        expect(panel.lobby.players.find((p) => p.id === 1).sparksBalance).toBe(5);
        expect(panel.lobby.players.find((p) => p.id === 2).sparksBalance).toBe(5);
        expect(panel.round).toBeNull();
    });

    it('credits an interrogation award only once, even if the same state broadcasts again', () => {
        const panel = hostPanel(
            makeState({
                lobby: makeLobby({ players: [makePlayer({ id: 1, sparksBalance: 0 })] }),
                interrogation: makeInterrogationState({ entries: [makeInterrogationEntry({ id: 10, player: makePlayer({ id: 1 }), sparksAwarded: null })] }),
            }),
            'ABC123'
        );
        panel.init();

        const scoredState = makeInterrogationState({
            entries: [makeInterrogationEntry({ id: 10, player: makePlayer({ id: 1 }), sparksAwarded: 3, isCurrent: false })],
            finished: true,
        });

        echo.emit('.interrogation.updated', { state: scoredState });
        echo.emit('.interrogation.updated', { state: scoredState });

        expect(panel.lobby.players.find((p) => p.id === 1).sparksBalance).toBe(3);
    });
});

describe('hostPanel: actions', () => {
    let api;

    beforeEach(() => {
        api = vi.fn().mockResolvedValue({});
        vi.stubGlobal('api', api);
    });

    it('startRound posts the selected modifiers and clears them on success', async () => {
        const panel = hostPanel(makeState(), 'ABC123');
        panel.mirrorEnabled = true;
        panel.cocktailEnabled = false;

        await panel.startRound();

        expect(api).toHaveBeenCalledWith('/host/ABC123/round', {
            method: 'POST',
            body: JSON.stringify({ mirror: true, cocktail: false }),
        });
        expect(panel.mirrorEnabled).toBe(false);
        expect(panel.working).toBe(false);
    });

    it('removePlayer asks for confirmation and drops the player locally on success', async () => {
        const panel = hostPanel(
            makeState({ lobby: makeLobby({ players: [makePlayer({ id: 1 }), makePlayer({ id: 2 })] }) }),
            'ABC123'
        );
        globalThis.confirm.mockReturnValue(true);

        await panel.removePlayer(makePlayer({ id: 2, name: 'Bob' }));

        expect(globalThis.confirm).toHaveBeenCalled();
        expect(api).toHaveBeenCalledWith('/host/ABC123/players/2/remove', { method: 'POST' });
        expect(panel.lobby.players.map((p) => p.id)).toEqual([1]);
    });

    it('removePlayer does nothing if the host cancels the confirmation', async () => {
        const panel = hostPanel(
            makeState({ lobby: makeLobby({ players: [makePlayer({ id: 1 }), makePlayer({ id: 2 })] }) }),
            'ABC123'
        );
        globalThis.confirm.mockReturnValue(false);

        await panel.removePlayer(makePlayer({ id: 2 }));

        expect(api).not.toHaveBeenCalled();
        expect(panel.lobby.players).toHaveLength(2);
    });

    it('surfaces the server error via alert when an action fails', async () => {
        api.mockRejectedValue(new Error('Сейчас нет активного раунда.'));
        const panel = hostPanel(makeState(), 'ABC123');

        await panel.revealRound();

        expect(globalThis.alert).toHaveBeenCalledWith('Сейчас нет активного раунда.');
        expect(panel.working).toBe(false);
    });
});
