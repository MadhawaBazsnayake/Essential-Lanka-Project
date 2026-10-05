// Essential Lanka — Service Worker (PWA Offline Support)
const CACHE_NAME = 'elp-cache-v1';
const STATIC_ASSETS = [
    '/elp/assets/css/style.min.css',
    '/elp/assets/css/pill-alerts.css',
    '/elp/assets/js/main.js',
    '/elp/assets/js/pill-alerts.js',
    '/elp/manifest.json'
];

// Install: Pre-cache static assets
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            return cache.addAll(STATIC_ASSETS).catch(err => {
                console.warn('SW: Some assets failed to cache', err);
            });
        })
    );
    self.skipWaiting();
});

// Activate: Clean old caches
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys => {
            return Promise.all(
                keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k))
            );
        })
    );
    self.clients.claim();
});

// Fetch: Network-first for API/PHP, cache-first for static
self.addEventListener('fetch', event => {
    const url = new URL(event.request.url);

    // Skip non-GET requests
    if (event.request.method !== 'GET') return;

    // Network-first for PHP pages and API calls
    if (url.pathname.endsWith('.html') || false) {
        event.respondWith(
            fetch(event.request).catch(() => {
                return new Response(JSON.stringify({ status: 'error', message: 'You are offline.' }), {
                    headers: { 'Content-Type': 'application/json' }
                });
            })
        );
        return;
    }

    // Cache-first for static assets
    event.respondWith(
        caches.match(event.request).then(cached => {
            return cached || fetch(event.request).then(response => {
                // Cache new static assets
                if (response.ok && (url.pathname.match(/\.(css|js|png|jpg|webp|woff2?)$/))) {
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone));
                }
                return response;
            });
        })
    );
});
