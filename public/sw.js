const CACHE_NAME = 'tuition-lms-v1';
const CORE_ASSETS = ['/', '/manifest.webmanifest', '/favicon.ico'];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll(CORE_ASSETS)),
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches
            .keys()
            .then((keys) =>
                Promise.all(
                    keys
                        .filter((key) => key !== CACHE_NAME)
                        .map((key) => caches.delete(key)),
                ),
            ),
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    if (event.request.method !== 'GET') {
        return;
    }

    const url = new URL(event.request.url);
    const isCacheableAsset =
        url.origin === self.location.origin &&
        (url.pathname.startsWith('/build/') ||
            url.pathname === '/manifest.webmanifest' ||
            url.pathname === '/favicon.ico');

    if (!isCacheableAsset) {
        return;
    }

    event.respondWith(
        fetch(event.request)
            .then((response) => {
                const copy = response.clone();
                caches.open(CACHE_NAME).then((cache) => {
                    cache.put(event.request, copy);
                });

                return response;
            })
            .catch(() => caches.match(event.request)),
    );
});
