import { beforeEach, describe, expect, it, vi } from 'vitest';
import { playerScreen } from './player-screen.js';
import { createFakeEcho } from './testing/fake-echo.js';
import { makeEmotion, makePlayer, makeRevealResult, makeRound } from './testing/fixtures.js';

function makeState(overrides = {}) {
    return {
        lobbyStatus: 'open',
        removed: false,
        player: makePlayer({ id: 1 }),
        round: null,
        isReader: false,
        readerHasChosen: false,
        hasVoted: false,
        votedEmotionId: null,
        availableEmotions: [makeEmotion({ id: 1, key: 'joy' }), makeEmotion({ id: 2, key: 'fear' })],
        isBeingInterrogated: false,
        ...overrides,
    };
}

describe('playerScreen: screen getter', () => {
    it('is "removed" before anything else, even if the lobby looks otherwise fine', () => {
        const screen = playerScreen(makeState({ removed: true, round: makeRound() }), 'ABC123');

        expect(screen.screen).toBe('removed');
    });

    it('is "closed" once the lobby closes', () => {
        const screen = playerScreen(makeState({ lobbyStatus: 'closed' }), 'ABC123');

        expect(screen.screen).toBe('closed');
    });

    it('is "interrogated" while this player is being questioned', () => {
        const screen = playerScreen(makeState({ isBeingInterrogated: true }), 'ABC123');

        expect(screen.screen).toBe('interrogated');
    });

    it('is "waiting" between rounds', () => {
        const screen = playerScreen(makeState({ round: null }), 'ABC123');

        expect(screen.screen).toBe('waiting');
    });

    it('is "reading" for the reader until they submit, then "reading-done"', () => {
        const reading = playerScreen(makeState({ round: makeRound(), isReader: true, readerHasChosen: false }), 'ABC123');
        const done = playerScreen(makeState({ round: makeRound(), isReader: true, readerHasChosen: true }), 'ABC123');

        expect(reading.screen).toBe('reading');
        expect(done.screen).toBe('reading-done');
    });

    it('is "voting" for a non-reader until they vote, then "voted"', () => {
        const voting = playerScreen(makeState({ round: makeRound(), isReader: false, hasVoted: false }), 'ABC123');
        const voted = playerScreen(makeState({ round: makeRound(), isReader: false, hasVoted: true }), 'ABC123');

        expect(voting.screen).toBe('voting');
        expect(voted.screen).toBe('voted');
    });
});

describe('playerScreen: vote() in a normal round', () => {
    let api;

    beforeEach(() => {
        api = vi.fn().mockResolvedValue({});
        vi.stubGlobal('api', api);
    });

    it('submits the single picked emotion straight away', async () => {
        const screen = playerScreen(makeState({ round: makeRound({ cocktailEnabled: false }) }), 'ABC123');

        await screen.vote(makeEmotion({ id: 2 }));

        expect(api).toHaveBeenCalledWith('/play/ABC123/vote', {
            method: 'POST',
            body: JSON.stringify({ emotion_id: 2, secondary_emotion_id: null }),
        });
        expect(screen.hasVoted).toBe(true);
        expect(screen.votedEmotionId).toBe(2);
    });

    it('ignores further taps once already voted', async () => {
        const screen = playerScreen(makeState({ round: makeRound(), hasVoted: true }), 'ABC123');

        await screen.vote(makeEmotion({ id: 2 }));

        expect(api).not.toHaveBeenCalled();
    });
});

describe('playerScreen: vote() in a cocktail round', () => {
    let api;

    beforeEach(() => {
        api = vi.fn().mockResolvedValue({});
        vi.stubGlobal('api', api);
    });

    it('holds the first pick without submitting anything yet', async () => {
        const screen = playerScreen(makeState({ round: makeRound({ cocktailEnabled: true }) }), 'ABC123');

        await screen.vote(makeEmotion({ id: 1 }));

        expect(api).not.toHaveBeenCalled();
        expect(screen.cocktailPick).toEqual(makeEmotion({ id: 1 }));
        expect(screen.hasVoted).toBe(false);
    });

    it('deselects the first pick on a second tap of the same emotion', async () => {
        const screen = playerScreen(makeState({ round: makeRound({ cocktailEnabled: true }) }), 'ABC123');

        await screen.vote(makeEmotion({ id: 1 }));
        await screen.vote(makeEmotion({ id: 1 }));

        expect(screen.cocktailPick).toBeNull();
        expect(api).not.toHaveBeenCalled();
    });

    it('submits both picks once a second, different emotion is tapped', async () => {
        const screen = playerScreen(makeState({ round: makeRound({ cocktailEnabled: true }) }), 'ABC123');

        await screen.vote(makeEmotion({ id: 1 }));
        await screen.vote(makeEmotion({ id: 2 }));

        expect(api).toHaveBeenCalledWith('/play/ABC123/vote', {
            method: 'POST',
            body: JSON.stringify({ emotion_id: 1, secondary_emotion_id: 2 }),
        });
        expect(screen.hasVoted).toBe(true);
        expect(screen.cocktailPick).toBeNull();
    });
});

describe('playerScreen: live updates via Echo', () => {
    let echo;

    beforeEach(() => {
        echo = createFakeEcho();
        vi.stubGlobal('Echo', echo);
        vi.stubGlobal('api', vi.fn().mockResolvedValue(makeState()));
    });

    it('marks only this player as removed, not a broadcast about someone else', () => {
        const mine = playerScreen(makeState({ player: makePlayer({ id: 1 }) }), 'ABC123');
        mine.init();

        echo.emit('.player.removed', { playerId: 99 });
        expect(mine.removed).toBe(false);

        echo.emit('.player.removed', { playerId: 1 });
        expect(mine.removed).toBe(true);
    });

    it('shows a reward toast only when this player is among the winners', () => {
        const winner = playerScreen(makeState({ player: makePlayer({ id: 1 }) }), 'ABC123');
        winner.init();
        echo.emit('.round.revealed', { result: makeRevealResult({ rewardedPlayerIds: [1], rewardSparks: 5 }) });
        expect(winner.toast).toBe('Вы заработали 5 искр!');

        const loser = playerScreen(makeState({ player: makePlayer({ id: 1 }) }), 'ABC123');
        loser.init();
        echo.emit('.round.revealed', { result: makeRevealResult({ rewardedPlayerIds: [2], rewardSparks: 5 }) });
        expect(loser.toast).toBeNull();
    });
});
