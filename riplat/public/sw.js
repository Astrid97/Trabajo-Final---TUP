const CACHE_NAME = 'riplat-static-v2';
const OFFLINE_URL = '/offline.html';
const STATIC_ASSET_PREFIXES = ['/build/assets/'];
const STATIC_ASSETS = new Set([
    '/images/riplat-logo-light.jpg',
    '/pwa-icon-192.png',
    '/pwa-icon-512.png',
    '/pwa-icon-maskable-512.png',
]);
const PRECACHE_URLS = [
    OFFLINE_URL,
    '/manifest.webmanifest',
    '/pwa-icon-192.png',
    '/pwa-icon-512.png',
    '/pwa-icon-maskable-512.png',
    '/images/riplat-logo-light.jpg',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => cache.addAll(PRECACHE_URLS))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((cacheNames) => Promise.all(
                cacheNames
                    .filter((cacheName) => cacheName.startsWith('riplat-static-') && cacheName !== CACHE_NAME)
                    .map((cacheName) => caches.delete(cacheName))
            ))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const { request } = event;
    const requestUrl = new URL(request.url);

    if (request.method !== 'GET' || requestUrl.origin !== self.location.origin) {
        return;
    }

    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(async () => (
                await caches.match(OFFLINE_URL)
            ))
        );
        return;
    }

    const isStaticAsset = STATIC_ASSETS.has(requestUrl.pathname)
        || STATIC_ASSET_PREFIXES.some((prefix) => requestUrl.pathname.startsWith(prefix));

    if (!isStaticAsset) {
        return;
    }

    event.respondWith(
        caches.match(request).then(async (cachedResponse) => {
            if (cachedResponse) {
                return cachedResponse;
            }

            const response = await fetch(request);

            if (response.ok) {
                const cache = await caches.open(CACHE_NAME);
                cache.put(request, response.clone());
            }

            return response;
        })
    );
});
