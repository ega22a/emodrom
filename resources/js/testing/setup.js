import { afterEach, vi } from 'vitest';

// Node 22+ ships its own global localStorage (stable since Node 22.4) that
// shadows jsdom's — without a --localstorage-file it's a non-functional
// stub, breaking every getItem/setItem call. The npm scripts pass
// NODE_OPTIONS=--no-experimental-webstorage so jsdom's own implementation
// is what tests actually see; running `vitest` directly without that flag
// reproduces the "localStorage.getItem is not a function" failure.

// jsdom doesn't implement window.alert/confirm — the Alpine components call
// them directly on error/confirmation, so tests need a real (mockable) stub
// instead of jsdom's "not implemented" throw.
globalThis.alert = vi.fn();
globalThis.confirm = vi.fn(() => true);

afterEach(() => {
    localStorage.clear();
    vi.unstubAllGlobals();
    vi.restoreAllMocks();
});
