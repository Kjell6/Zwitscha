const CACHE_NAME = 'zwitscha-cache-v3'; // Version erhöht, um neuen SW zu erzwingen
const urlsToCache = [];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => {
      console.log('Cache geöffnet, keine Dateien zur Vorkonditionierung.');
      return cache.addAll(urlsToCache);
    })
  );
});

self.addEventListener('fetch', event => {
  // Ignoriere alle Anfragen, die keine GET-Anfragen sind.
  // Das lässt Formular-Submits (POST) und andere Anfragen direkt zum Browser durch,
  // um Weiterleitungen korrekt zu handhaben.
  if (event.request.method !== 'GET') {
    return; // Service Worker tut nichts, Browser übernimmt.
  }

  // Für alle GET-Anfragen, fahre mit der "Nur-Netzwerk"-Strategie fort.
  event.respondWith(fetch(event.request));
});

self.addEventListener('activate', event => {
  const cacheWhitelist = [CACHE_NAME];
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheWhitelist.indexOf(cacheName) === -1) {
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
}); 