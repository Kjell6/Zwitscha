// Minimaler Service Worker - fängt nichts ab
const CACHE_NAME = 'zwitscha-cache-v6';

// Sofort aktivieren
self.addEventListener('install', event => {
  event.waitUntil(self.skipWaiting());
});

// Sofort übernehmen
self.addEventListener('activate', event => {
  event.waitUntil(self.clients.claim());
  
  // Alte Caches löschen
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          return caches.delete(cacheName);
        })
      );
    })
  );
});

// WICHTIG: Keine fetch-Events abfangen!
// Lass alle Requests direkt durch den Browser laufen 