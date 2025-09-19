// Service Worker for Performance Optimization
const CACHE_NAME = 'prime-ems-v1.0.0';
const STATIC_CACHE = 'prime-ems-static-v1.0.0';
const DYNAMIC_CACHE = 'prime-ems-dynamic-v1.0.0';

// Resources to cache immediately - HTTP/2 optimized with connection coalescing
const STATIC_ASSETS = [
    '/',
    '/index.php',
    '/assets/css/theme.css',
    '/assets/css/performance-optimized.css',
    '/assets/js/bundle-manager.js',
    '/assets/js/performance-loader.js',
    '/assets/js/image-optimization.js',
    '/assets/images/logo.png',
    '/assets/images/logo.svg',
    '/assets/images/favicon.ico',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css',
    'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@400;500;600;700;800&display=swap'
];

// HTTP/2 Server Push Resources
const SERVER_PUSH_RESOURCES = [
    '/assets/css/mobile.css',
    '/assets/js/main.js',
    '/assets/js/mobile.js',
    '/assets/js/lazy-load.js'
];

// App Shell Resources for PWA
const APP_SHELL_ASSETS = [
    '/includes/header.php',
    '/includes/footer.php',
    '/includes/navbar.php',
    '/assets/css/style.css'
];

// Install event - cache static assets
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then(cache => {
                return cache.addAll(STATIC_ASSETS);
            })
            .then(() => self.skipWaiting())
    );
});

// Activate event - clean old caches and setup app shell
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames
                    .filter(cacheName => cacheName !== STATIC_CACHE && cacheName !== DYNAMIC_CACHE)
                    .map(cacheName => caches.delete(cacheName))
            );
        })
        .then(() => {
            // Cache app shell components
            return caches.open(STATIC_CACHE).then(cache => {
                return cache.addAll(APP_SHELL_ASSETS);
            });
        })
        .then(() => self.clients.claim())
    );
});

// Fetch event - serve from cache or network with HTTP/2 multiplexing
self.addEventListener('fetch', event => {
    const { request } = event;
    const url = new URL(request.url);

    // Skip non-GET requests and external domains
    if (request.method !== 'GET' || !url.origin.includes('primeems')) {
        return;
    }

    // HTTP/2 Server Push simulation - preload related resources
    if (SERVER_PUSH_RESOURCES.some(resource => request.url.includes(resource))) {
        // Preload dependent resources when main resource is requested
        if (request.url.includes('/assets/js/main.js')) {
            event.waitUntil(
                caches.open(STATIC_CACHE).then(cache => {
                    return Promise.all([
                        cache.add('/assets/css/style.css'),
                        cache.add('/assets/js/mobile.js')
                    ]);
                })
            );
        }
    }

    // Handle static assets - Cache First strategy with HTTP/2 coalescing
    if (STATIC_ASSETS.some(asset => request.url.includes(asset))) {
        event.respondWith(
            caches.match(request)
                .then(response => {
                    return response || fetch(request).then(networkResponse => {
                        return caches.open(STATIC_CACHE).then(cache => {
                            cache.put(request, networkResponse.clone());
                            return networkResponse;
                        });
                    });
                })
        );
        return;
    }

    // Handle API calls - Network First strategy
    if (url.pathname.startsWith('/api/') || url.pathname.startsWith('/admin/')) {
        event.respondWith(
            fetch(request)
                .then(networkResponse => {
                    // Cache successful responses
                    if (networkResponse.ok) {
                        const responseClone = networkResponse.clone();
                        caches.open(DYNAMIC_CACHE).then(cache => {
                            cache.put(request, responseClone);
                        });
                    }
                    return networkResponse;
                })
                .catch(() => {
                    return caches.match(request);
                })
        );
        return;
    }

    // Handle HTML pages - Network First with cache fallback
    if (request.headers.get('accept').includes('text/html')) {
        event.respondWith(
            fetch(request)
                .then(networkResponse => {
                    const responseClone = networkResponse.clone();
                    caches.open(DYNAMIC_CACHE).then(cache => {
                        cache.put(request, responseClone);
                    });
                    return networkResponse;
                })
                .catch(() => {
                    return caches.match(request) || caches.match('/');
                })
        );
        return;
    }

    // Default - Stale While Revalidate strategy with runtime caching
    event.respondWith(
        caches.match(request)
            .then(response => {
                const fetchPromise = fetch(request).then(networkResponse => {
                    // Runtime caching for images and fonts
                    if (request.url.match(/\.(png|jpg|jpeg|svg|webp|woff|woff2)$/)) {
                        caches.open(DYNAMIC_CACHE).then(cache => {
                            // Add expiration for dynamic content
                            const responseClone = networkResponse.clone();
                            const expirationTime = Date.now() + (24 * 60 * 60 * 1000); // 24 hours
                            const cacheItem = {
                                response: responseClone,
                                timestamp: expirationTime
                            };
                            cache.put(request, new Response(JSON.stringify(cacheItem)));
                        });
                    } else {
                        caches.open(DYNAMIC_CACHE).then(cache => {
                            cache.put(request, networkResponse.clone());
                        });
                    }
                    return networkResponse;
                });

                return response || fetchPromise;
            })
    );
});

// Background sync for offline actions
self.addEventListener('sync', event => {
    if (event.tag === 'background-sync') {
        event.waitUntil(doBackgroundSync());
    }
});

async function doBackgroundSync() {
    // Handle offline form submissions here
    try {
        const cache = await caches.open(DYNAMIC_CACHE);
        const requests = await cache.keys();

        // Process cached requests
        for (const request of requests) {
            if (request.url.includes('/contact-form.php') || request.url.includes('/api/')) {
                try {
                    await fetch(request);
                    await cache.delete(request);
                } catch (error) {
                    console.log('Background sync failed for:', request.url);
                }
            }
        }
    } catch (error) {
        console.log('Background sync error:', error);
    }
}

// Push notifications (if enabled)
self.addEventListener('push', event => {
    if (event.data) {
        const data = event.data.json();
        const options = {
            body: data.body,
            icon: '/assets/images/logo.png',
            badge: '/assets/images/favicon.ico',
            vibrate: [100, 50, 100],
            data: {
                dateOfArrival: Date.now(),
                primaryKey: data.primaryKey
            }
        };

        event.waitUntil(
            self.registration.showNotification(data.title, options)
        );
    }
});

// Notification click handler
self.addEventListener('notificationclick', event => {
    event.notification.close();

    event.waitUntil(
        clients.openWindow(event.notification.data.url || '/')
    );
});