/**
 * WHS4 Service Worker - Offline-First PWA
 *
 * Caching Strategies:
 * - Static Assets: CacheFirst (long-lived, immutable)
 * - API Calls: NetworkFirst (fresh data priority, fallback to cache)
 * - Images: CacheFirst (performance optimization)
 * - Offline Fallback: Custom offline page
 */

// Workbox imports (CDN for simplicity, can switch to npm build later)
importScripts('https://storage.googleapis.com/workbox-cdn/releases/7.0.0/workbox-sw.js');

const { registerRoute } = workbox.routing;
const { CacheFirst, NetworkFirst, StaleWhileRevalidate } = workbox.strategies;
const { CacheableResponsePlugin } = workbox.cacheableResponse;
const { ExpirationPlugin } = workbox.expiration;
const { BackgroundSyncPlugin } = workbox.backgroundSync;
const { precacheAndRoute } = workbox.precaching;

// Service Worker version (increment when updating SW)
const SW_VERSION = '1.0.0';
const CACHE_PREFIX = 'whs4-v1';

// Cache names
const STATIC_CACHE = `${CACHE_PREFIX}-static`;
const API_CACHE = `${CACHE_PREFIX}-api`;
const IMAGE_CACHE = `${CACHE_PREFIX}-images`;
const OFFLINE_CACHE = `${CACHE_PREFIX}-offline`;

console.log(`[SW ${SW_VERSION}] Service Worker initializing...`);

// ============================================
// PRECACHING - Static assets to cache on install
// ============================================
// This will be populated by Workbox build process
// For now, we'll manually define critical assets
const PRECACHE_ASSETS = [
  '/',
  '/offline',
  '/assets/vendor/css/core.css',
  '/assets/vendor/css/theme-default.css',
  '/assets/css/demo.css',
  '/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css',
  '/assets/vendor/js/helpers.js',
  '/assets/vendor/js/template-customizer.js',
  '/assets/js/config.js',
];

// Precache critical assets on install
self.addEventListener('install', (event) => {
  console.log(`[SW ${SW_VERSION}] Installing...`);

  event.waitUntil(
    caches.open(STATIC_CACHE).then((cache) => {
      console.log('[SW] Precaching static assets');
      return cache.addAll(PRECACHE_ASSETS);
    })
  );

  // Force activation immediately
  self.skipWaiting();
});

// ============================================
// ACTIVATION - Clean up old caches
// ============================================
self.addEventListener('activate', (event) => {
  console.log(`[SW ${SW_VERSION}] Activating...`);

  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames
          .filter((cacheName) => {
            // Delete caches that don't match current version prefix
            return !cacheName.startsWith(CACHE_PREFIX);
          })
          .map((cacheName) => {
            console.log('[SW] Deleting old cache:', cacheName);
            return caches.delete(cacheName);
          })
      );
    })
  );

  // Take control of all pages immediately
  return self.clients.claim();
});

// ============================================
// CACHING STRATEGIES
// ============================================

// 1. Static Assets (Vuexy theme, CSS, JS) - CacheFirst
registerRoute(
  ({ request, url }) => {
    return (
      request.destination === 'style' ||
      request.destination === 'script' ||
      request.destination === 'font' ||
      url.pathname.startsWith('/assets/vendor/') ||
      url.pathname.startsWith('/assets/css/') ||
      url.pathname.startsWith('/assets/js/')
    );
  },
  new CacheFirst({
    cacheName: STATIC_CACHE,
    plugins: [
      new CacheableResponsePlugin({
        statuses: [0, 200],
      }),
      new ExpirationPlugin({
        maxEntries: 100,
        maxAgeSeconds: 30 * 24 * 60 * 60, // 30 days
      }),
    ],
  })
);

// 2. API Calls - NetworkFirst (fresh data priority)
registerRoute(
  ({ url }) => {
    return (
      url.pathname.startsWith('/incidents') ||
      url.pathname.startsWith('/risk-assessments') ||
      url.pathname.startsWith('/emergencies') ||
      url.pathname.startsWith('/vehicles') ||
      url.pathname.startsWith('/inspections') ||
      url.pathname.startsWith('/maintenance') ||
      url.pathname.startsWith('/journeys') ||
      url.pathname.startsWith('/capa') ||
      url.pathname.startsWith('/safety-inspections') ||
      url.pathname.startsWith('/contractors') ||
      url.pathname.startsWith('/training') ||
      url.pathname.startsWith('/warehouse-equipment') ||
      url.pathname.startsWith('/documents') ||
      url.pathname.startsWith('/compliance-reporting') ||
      url.pathname.startsWith('/team')
    );
  },
  new NetworkFirst({
    cacheName: API_CACHE,
    networkTimeoutSeconds: 5, // Fallback to cache after 5s
    plugins: [
      new CacheableResponsePlugin({
        statuses: [0, 200],
      }),
      new ExpirationPlugin({
        maxEntries: 50,
        maxAgeSeconds: 5 * 60, // 5 minutes
      }),
    ],
  })
);

// 3. Images (incident photos, user avatars) - CacheFirst
registerRoute(
  ({ request, url }) => {
    return (
      request.destination === 'image' ||
      url.pathname.startsWith('/storage/incidents/') ||
      url.pathname.startsWith('/storage/vehicles/') ||
      url.pathname.startsWith('/storage/equipment/') ||
      url.pathname.startsWith('/images/pwa/')
    );
  },
  new CacheFirst({
    cacheName: IMAGE_CACHE,
    plugins: [
      new CacheableResponsePlugin({
        statuses: [0, 200],
      }),
      new ExpirationPlugin({
        maxEntries: 200,
        maxAgeSeconds: 7 * 24 * 60 * 60, // 7 days
      }),
    ],
  })
);

// 4. HTML Pages - NetworkFirst
registerRoute(
  ({ request }) => request.mode === 'navigate',
  new NetworkFirst({
    cacheName: `${CACHE_PREFIX}-pages`,
    plugins: [
      new CacheableResponsePlugin({
        statuses: [0, 200],
      }),
    ],
  })
);

// ============================================
// OFFLINE FALLBACK
// ============================================
self.addEventListener('fetch', (event) => {
  if (event.request.mode === 'navigate') {
    event.respondWith(
      fetch(event.request).catch(() => {
        return caches.match('/offline');
      })
    );
  }
});

// ============================================
// BACKGROUND SYNC - Queue failed requests
// ============================================
const bgSyncPlugin = new BackgroundSyncPlugin('offlineQueue', {
  maxRetentionTime: 24 * 60, // Retry for 24 hours
  onSync: async ({ queue }) => {
    let entry;
    while ((entry = await queue.shiftRequest())) {
      try {
        await fetch(entry.request);
        console.log('[SW] Background sync: Request successful', entry.request.url);
      } catch (error) {
        console.error('[SW] Background sync: Request failed', error);
        await queue.unshiftRequest(entry);
        throw error;
      }
    }
  },
});

// Register background sync for POST/PUT/DELETE requests
registerRoute(
  ({ request }) => {
    return (
      (request.method === 'POST' || request.method === 'PUT' || request.method === 'DELETE') &&
      (request.url.includes('/incidents') ||
        request.url.includes('/emergencies') ||
        request.url.includes('/journeys') ||
        request.url.includes('/inspections'))
    );
  },
  new NetworkFirst({
    cacheName: API_CACHE,
    plugins: [bgSyncPlugin],
  }),
  'POST'
);

// ============================================
// MESSAGE HANDLING - Communication with main thread
// ============================================
self.addEventListener('message', (event) => {
  if (event.data && event.data.type === 'SKIP_WAITING') {
    console.log('[SW] Received SKIP_WAITING message');
    self.skipWaiting();
  }

  if (event.data && event.data.type === 'CACHE_URLS') {
    const urlsToCache = event.data.payload;
    event.waitUntil(
      caches.open(STATIC_CACHE).then((cache) => cache.addAll(urlsToCache))
    );
  }

  if (event.data && event.data.type === 'CLEAR_CACHE') {
    event.waitUntil(
      caches.keys().then((cacheNames) => {
        return Promise.all(
          cacheNames.map((cacheName) => caches.delete(cacheName))
        );
      })
    );
  }

  // Trigger sync from main thread
  if (event.data && event.data.type === 'SYNC_NOW') {
    console.log('[SW] Received SYNC_NOW message');
    event.waitUntil(
      self.registration.sync.register('syncOfflineData')
        .then(() => console.log('[SW] Background sync registered'))
        .catch((error) => console.error('[SW] Background sync registration failed:', error))
    );
  }

  // Get sync status
  if (event.data && event.data.type === 'GET_SYNC_STATUS') {
    console.log('[SW] Received GET_SYNC_STATUS message');
    event.ports[0].postMessage({
      type: 'SYNC_STATUS',
      status: 'ready'
    });
  }
});

// ============================================
// BACKGROUND SYNC - Sync event handler
// ============================================
self.addEventListener('sync', (event) => {
  console.log('[SW] Sync event fired:', event.tag);

  if (event.tag === 'syncOfflineData') {
    event.waitUntil(
      syncOfflineData()
        .then(() => {
          console.log('[SW] Background sync completed successfully');

          // Notify all clients
          return self.clients.matchAll().then((clients) => {
            clients.forEach((client) => {
              client.postMessage({
                type: 'SYNC_COMPLETE',
                success: true
              });
            });
          });
        })
        .catch((error) => {
          console.error('[SW] Background sync failed:', error);

          // Notify all clients of failure
          return self.clients.matchAll().then((clients) => {
            clients.forEach((client) => {
              client.postMessage({
                type: 'SYNC_FAILED',
                error: error.message
              });
            });
          });

          throw error; // Rethrow to schedule retry
        })
    );
  }
});

/**
 * Sync offline data from IndexedDB
 * Note: This is a placeholder - actual sync logic is in sync-manager.js
 * The SW can trigger the sync, but the main thread handles IndexedDB access
 */
async function syncOfflineData() {
  console.log('[SW] Syncing offline data...');

  // Post message to main thread to trigger sync
  const clients = await self.clients.matchAll();
  if (clients.length > 0) {
    clients[0].postMessage({
      type: 'TRIGGER_SYNC',
      timestamp: Date.now()
    });
  }

  // Wait a bit for sync to complete
  // In real implementation, we'd use MessageChannel for two-way communication
  await new Promise((resolve) => setTimeout(resolve, 5000));

  return true;
}

// ============================================
// PUSH NOTIFICATIONS (Future implementation)
// ============================================
self.addEventListener('push', (event) => {
  console.log('[SW] Push notification received', event);

  const data = event.data ? event.data.json() : {};
  const title = data.title || 'WHS4 Notification';
  const options = {
    body: data.body || 'New safety alert',
    icon: '/images/pwa/icon-192x192.png',
    badge: '/images/pwa/icon-96x96.png',
    tag: data.tag || 'whs4-notification',
    data: data,
    actions: data.actions || [],
  };

  event.waitUntil(
    self.registration.showNotification(title, options)
  );
});

self.addEventListener('notificationclick', (event) => {
  console.log('[SW] Notification clicked', event);

  event.notification.close();

  const urlToOpen = event.notification.data?.url || '/';

  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true })
      .then((clientList) => {
        // If app is already open, focus it
        for (let i = 0; i < clientList.length; i++) {
          const client = clientList[i];
          if (client.url === urlToOpen && 'focus' in client) {
            return client.focus();
          }
        }
        // Otherwise open new window
        if (clients.openWindow) {
          return clients.openWindow(urlToOpen);
        }
      })
  );
});

console.log(`[SW ${SW_VERSION}] Service Worker loaded successfully`);
