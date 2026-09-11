/**
 * Stands in for window.Echo in tests. `.channel(name).listen(event, cb)`
 * records each callback by event name instead of opening a real socket, so
 * a test can trigger `.emit('.round.started', payload)` to drive exactly
 * what a component's init() would do when that broadcast arrives.
 */
export function createFakeEcho() {
    const listeners = {};

    const channel = {
        listen(event, callback) {
            listeners[event] = callback;

            return channel;
        },
    };

    return {
        channel: () => channel,
        emit(event, payload) {
            if (!listeners[event]) {
                throw new Error(`No listener registered for "${event}"`);
            }

            listeners[event](payload);
        },
    };
}
