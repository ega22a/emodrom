const CACHE_NAME = 'emodrom-static-v1';
const CACHEABLE_PATHS = ['/build/', '/icons/', '/static/'];

self.addEventListener('install', () => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))))
            .then(() => self.clients.claim()),
    );
});

/**
 * Only static build assets are cached — pages, game state, and Echo
 * websocket traffic must always hit the network, since gameplay is live.
 */
self.addEventListener('fetch', (event) => {
    const url = new URL(event.request.url);

    if (event.request.method !== 'GET' || !CACHEABLE_PATHS.some((path) => url.pathname.startsWith(path))) {
        return;
    }

    event.respondWith(
        caches.open(CACHE_NAME).then(async (cache) => {
            const cached = await cache.match(event.request);
            const network = fetch(event.request)
                .then((response) => {
                    cache.put(event.request, response.clone());
                    return response;
                })
                .catch(() => cached);

            return cached ?? network;
        }),
    );
});
