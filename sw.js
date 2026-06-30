// sw.js

const CACHE_NAME = 'pulsepos-v1';
// Jo files offline access karni hain unki list
const ASSETS_TO_CACHE = [
    './',
    './index.php',
    './assets/js/db.js',
    './assets/js/app.js',
    // Agar Tailwind local use kar rahe hain to uska path yahan ayega
];

// Install Service Worker and Cache Assets
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            console.log('Caching essential assets...');
            return cache.addAll(ASSETS_TO_CACHE);
        })
    );
});

// Activate Service Worker and Clear Old Caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cache) => {
                    if (cache !== CACHE_NAME) {
                        console.log('Clearing old cache:', cache);
                        return caches.delete(cache);
                    }
                })
            );
        })
    );
});

// Fetch Request (Network first, fallback to cache)
self.addEventListener('fetch', (event) => {
    // API calls ko cache nahi karna, unka logic alag handle hoga
    if (event.request.url.includes('/api/')) {
        return;
    }

    event.respondWith(
        fetch(event.request).catch(() => {
            return caches.match(event.request);
        })
    );
});