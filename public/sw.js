const CACHE_NAME = 'apar-srp-v1';
const OFFLINE_URL = '/offline.html';

// Static assets to pre-cache on install
const PRE_CACHE = [
    '/',
    '/offline.html',
    '/manifest.json',
    '/icons/icon.svg',
];

// ─── Install: pre-cache shell ────────────────────────────────────────────────
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => cache.addAll(PRE_CACHE))
    );
    self.skipWaiting();
});

// ─── Activate: clean up old caches ───────────────────────────────────────────
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys =>
            Promise.all(
                keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k))
            )
        )
    );
    self.clients.claim();
});

// ─── Fetch: strategy per request type ────────────────────────────────────────
self.addEventListener('fetch', event => {
    const { request } = event;
    const url = new URL(request.url);

    // Ignore non-GET, cross-origin, and Laravel internals (_debugbar, etc.)
    if (request.method !== 'GET') return;
    if (url.origin !== self.location.origin) return;
    if (url.pathname.startsWith('/_debugbar')) return;

    // CDN scripts (tailwind, qrcode, html5-qrcode) → cache-first
    if (url.hostname !== self.location.hostname) {
        event.respondWith(cacheFirst(request));
        return;
    }

    // Static files (images, icons, manifest) → cache-first
    if (/\.(svg|png|jpg|jpeg|ico|webp|woff2?|ttf)$/i.test(url.pathname)) {
        event.respondWith(cacheFirst(request));
        return;
    }

    // HTML navigation → network-first with offline fallback
    if (request.headers.get('accept')?.includes('text/html')) {
        event.respondWith(networkFirstWithOfflineFallback(request));
        return;
    }

    // Everything else → network-first
    event.respondWith(networkFirst(request));
});

// ─── Strategies ──────────────────────────────────────────────────────────────

async function cacheFirst(request) {
    const cached = await caches.match(request);
    if (cached) return cached;
    try {
        const response = await fetch(request);
        if (response.ok) {
            const cache = await caches.open(CACHE_NAME);
            cache.put(request, response.clone());
        }
        return response;
    } catch {
        return new Response('', { status: 503 });
    }
}

async function networkFirst(request) {
    try {
        const response = await fetch(request);
        if (response.ok) {
            const cache = await caches.open(CACHE_NAME);
            cache.put(request, response.clone());
        }
        return response;
    } catch {
        const cached = await caches.match(request);
        return cached ?? new Response('', { status: 503 });
    }
}

async function networkFirstWithOfflineFallback(request) {
    try {
        const response = await fetch(request);
        if (response.ok) {
            const cache = await caches.open(CACHE_NAME);
            cache.put(request, response.clone());
        }
        return response;
    } catch {
        const cached = await caches.match(request);
        if (cached) return cached;
        // Show offline page for navigation
        return caches.match(OFFLINE_URL);
    }
}
