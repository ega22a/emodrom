export function makePlayer(overrides = {}) {
    return {
        id: 1,
        name: 'Alice',
        avatar: 'cat',
        avatarColor: 'orange',
        sparksBalance: 0,
        ...overrides,
    };
}

export function makeLobby(overrides = {}) {
    return {
        code: 'ABC123',
        status: 'open',
        joinUrl: 'http://example.test/join/ABC123',
        players: [],
        isConfigured: true,
        roundLimit: null,
        roundsPlayed: 0,
        interrogationEnabled: false,
        emotionSet: 'classic',
        ...overrides,
    };
}

export function makeEmotion(overrides = {}) {
    return {
        id: 1,
        key: 'joy',
        label: 'Радость',
        icon: 'sun',
        color: 'yellow',
        ...overrides,
    };
}

export function makeQuestion(overrides = {}) {
    return {
        id: 1,
        situation: 'Тестовая ситуация',
        rewardSparks: 5,
        ...overrides,
    };
}

export function makeRound(overrides = {}) {
    return {
        id: 1,
        number: 1,
        status: 'voting',
        reader: makePlayer(),
        question: makeQuestion(),
        mirrorEnabled: false,
        cocktailEnabled: false,
        ...overrides,
    };
}

export function makeRevealResult(overrides = {}) {
    return {
        roundNumber: 1,
        question: makeQuestion(),
        reader: makePlayer(),
        readerEmotion: makeEmotion(),
        groups: [],
        abstainers: [],
        rewardSparks: 5,
        rewardedPlayerIds: [],
        readerTookReward: false,
        interrogationEnabled: false,
        interrogationTargets: [],
        mirrorEnabled: false,
        cocktailEnabled: false,
        ...overrides,
    };
}

export function makeInterrogationEntry(overrides = {}) {
    return {
        id: 1,
        player: makePlayer(),
        sparksAwarded: null,
        isCurrent: true,
        ...overrides,
    };
}

export function makeInterrogationState(overrides = {}) {
    return {
        roundNumber: 1,
        entries: [],
        finished: false,
        ...overrides,
    };
}
