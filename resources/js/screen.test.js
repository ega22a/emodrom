import { beforeEach, describe, expect, it, vi } from 'vitest';
import { screenDisplay } from './screen.js';
import { createFakeEcho } from './testing/fake-echo.js';
import { makeInterrogationEntry, makeInterrogationState, makeLobby, makePlayer, makeRevealResult, makeRound } from './testing/fixtures.js';

const SFX_BASE = 'http://example.test/static/assets/sfx';

function makeState(overrides = {}) {
    return {
        lobby: makeLobby({ players: [makePlayer({ id: 1 })] }),
        round: null,
        lastResult: null,
        interrogation: null,
        ...overrides,
    };
}

describe('screenDisplay: screen getter', () => {
    it('is "closed" once the lobby closes', () => {
        const screen = screenDisplay(makeState({ lobby: makeLobby({ status: 'closed' }) }), 'ABC123', SFX_BASE);

        expect(screen.screen).toBe('closed');
    });

    it('is "voting" while a round is in progress', () => {
        const screen = screenDisplay(makeState({ round: makeRound({ status: 'voting' }) }), 'ABC123', SFX_BASE);

        expect(screen.screen).toBe('voting');
    });

    it('is "revealing" right after a reveal, before the stagger animation finishes', () => {
        const screen = screenDisplay(makeState({ lastResult: makeRevealResult() }), 'ABC123', SFX_BASE);
        screen.revealAnimationDone = false;

        expect(screen.screen).toBe('revealing');
    });

    it('moves to "interrogating" once the reveal animation is done and interrogation is active', () => {
        const screen = screenDisplay(
            makeState({
                lobby: makeLobby({ interrogationEnabled: true }),
                lastResult: makeRevealResult(),
                interrogation: makeInterrogationState({ finished: false }),
            }),
            'ABC123',
            SFX_BASE
        );
        screen.revealAnimationDone = true;

        expect(screen.screen).toBe('interrogating');
    });

    it('is "revealed" once everything has settled', () => {
        const screen = screenDisplay(makeState({ lastResult: makeRevealResult() }), 'ABC123', SFX_BASE);
        screen.revealAnimationDone = true;

        expect(screen.screen).toBe('revealed');
    });

    it('is "idle" with nothing going on', () => {
        const screen = screenDisplay(makeState(), 'ABC123', SFX_BASE);

        expect(screen.screen).toBe('idle');
    });
});

describe('screenDisplay: sound preference', () => {
    it('defaults to enabled when nothing is stored yet', () => {
        const screen = screenDisplay(makeState(), 'ABC123', SFX_BASE);

        expect(screen.soundEnabled).toBe(true);
    });

    it('reads a previously stored preference', () => {
        localStorage.setItem('emodrom.soundEnabled', 'false');

        const screen = screenDisplay(makeState(), 'ABC123', SFX_BASE);

        expect(screen.soundEnabled).toBe(false);
    });

    it('toggleSound flips and persists the preference', () => {
        const screen = screenDisplay(makeState(), 'ABC123', SFX_BASE);
        screen.$refs = { music: { play: vi.fn().mockResolvedValue(), pause: vi.fn() } };

        screen.toggleSound();

        expect(screen.soundEnabled).toBe(false);
        expect(localStorage.getItem('emodrom.soundEnabled')).toBe('false');
    });
});

describe('screenDisplay: playSfx', () => {
    it('never constructs an Audio element when sound is disabled', () => {
        const AudioSpy = vi.fn();
        vi.stubGlobal('Audio', AudioSpy);
        const screen = screenDisplay(makeState(), 'ABC123', SFX_BASE);
        screen.soundEnabled = false;

        screen.playSfx('game_start');

        expect(AudioSpy).not.toHaveBeenCalled();
    });

    it('plays the right file when sound is enabled', () => {
        const play = vi.fn().mockResolvedValue();
        const AudioSpy = vi.fn(function () {
            return { play };
        });
        vi.stubGlobal('Audio', AudioSpy);
        const screen = screenDisplay(makeState(), 'ABC123', SFX_BASE);
        screen.soundEnabled = true;

        screen.playSfx('game_start');

        expect(AudioSpy).toHaveBeenCalledWith(`${SFX_BASE}/game_start.wav`);
        expect(play).toHaveBeenCalled();
    });
});

describe('screenDisplay: playInterrogationAwardSfx', () => {
    let AudioSpy;

    beforeEach(() => {
        AudioSpy = vi.fn(function () {
            return { play: vi.fn().mockResolvedValue() };
        });
        vi.stubGlobal('Audio', AudioSpy);
    });

    it('plays the small-coin sound for a low award and the drop sound for a big one', () => {
        const screen = screenDisplay(makeState(), 'ABC123', SFX_BASE);
        const previous = makeInterrogationState({
            entries: [
                makeInterrogationEntry({ id: 1, sparksAwarded: null }),
                makeInterrogationEntry({ id: 2, sparksAwarded: null }),
            ],
        });
        const next = makeInterrogationState({
            entries: [
                makeInterrogationEntry({ id: 1, sparksAwarded: 1 }),
                makeInterrogationEntry({ id: 2, sparksAwarded: 5 }),
            ],
        });

        screen.playInterrogationAwardSfx(previous, next);

        expect(AudioSpy).toHaveBeenCalledWith(`${SFX_BASE}/coin_small.wav`);
        expect(AudioSpy).toHaveBeenCalledWith(`${SFX_BASE}/coin_drop.wav`);
        expect(AudioSpy).toHaveBeenCalledTimes(2);
    });

    it('does not re-play a sound for an entry that was already scored', () => {
        const screen = screenDisplay(makeState(), 'ABC123', SFX_BASE);
        const alreadyScored = makeInterrogationState({ entries: [makeInterrogationEntry({ id: 1, sparksAwarded: 3 })] });

        screen.playInterrogationAwardSfx(alreadyScored, alreadyScored);

        expect(AudioSpy).not.toHaveBeenCalled();
    });
});

describe('screenDisplay: live updates via Echo', () => {
    let echo;

    beforeEach(() => {
        echo = createFakeEcho();
        vi.stubGlobal('Echo', echo);
        vi.stubGlobal(
            'Audio',
            vi.fn(function () {
                return { play: vi.fn().mockResolvedValue() };
            })
        );
    });

    it('drops a removed player from the projector roster', () => {
        const screen = screenDisplay(
            makeState({ lobby: makeLobby({ players: [makePlayer({ id: 1 }), makePlayer({ id: 2 })] }) }),
            'ABC123',
            SFX_BASE
        );
        screen.$refs = { music: { play: vi.fn().mockResolvedValue(), pause: vi.fn() } };
        screen.init();

        echo.emit('.player.removed', { playerId: 2 });

        expect(screen.lobby.players.map((p) => p.id)).toEqual([1]);
    });
});
