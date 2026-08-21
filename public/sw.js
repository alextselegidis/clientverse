/* ----------------------------------------------------------------------------
 * Clientverse - Self-Hosted CRM
 *
 * @package     Clientverse
 * @author      A.Tselegidis <alextselegidis@gmail.com>
 * @copyright   Copyright (c) Alex Tselegidis
 * @license     https://opensource.org/licenses/GPL-3.0 - GPLv3
 * @link        https://clientverse.org
 * ---------------------------------------------------------------------------- */

// HTML is never cached: pages embed a CSRF token, and a stale token breaks every
// form submit. Only versioned static assets are cached, plus an offline fallback.

const VERSION = new URL(self.location.href).searchParams.get('v') || 'dev';
const CACHE = 'clientverse-' + VERSION;

const ASSETS = [
    'offline.html',
    'vendor/bootstrap/bootstrap.min.css',
    'vendor/bootstrap/bootstrap.bundle.min.js',
    'vendor/bootstrap-icons/bootstrap-icons.min.css',
    'vendor/bootstrap-icons/fonts/bootstrap-icons.woff2',
    'vendor/pace-js/pace-theme-flat-top.tmpl.css',
    'vendor/pace-js/pace.min.js',
    'styles/clientverse.css?' + VERSION,
    'scripts/clientverse.js?' + VERSION,
    'images/logo.png',
    'images/logo-light.svg',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches
            .open(CACHE)
            .then((cache) => cache.addAll(ASSETS.map((asset) => new URL(asset, self.registration.scope).href)))
            .then(() => self.skipWaiting()),
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches
            .keys()
            .then((keys) => Promise.all(keys.filter((key) => key !== CACHE).map((key) => caches.delete(key))))
            .then(() => self.clients.claim()),
    );
});

self.addEventListener('fetch', (event) => {
    const request = event.request;

    if (request.method !== 'GET' || new URL(request.url).origin !== self.location.origin) {
        return;
    }

    // Navigations always hit the network; fall back to the offline page.
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(() => caches.match(new URL('offline.html', self.registration.scope).href)),
        );
        return;
    }

    // Static assets: answer from cache at once, then refresh it in the background.
    // Vendor files carry no version in their URL, so a plain cache-first strategy
    // would keep serving them until the app version changes.
    event.respondWith(
        caches.match(request).then((cached) => {
            const fetched = fetch(request)
                .then((response) => {
                    if (response.ok && response.type === 'basic') {
                        const copy = response.clone();
                        caches.open(CACHE).then((cache) => cache.put(request, copy));
                    }

                    return response;
                })
                .catch(() => cached);

            return cached || fetched;
        }),
    );
});
