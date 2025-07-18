const CACHE_NAME = 'zwitscha-cache-v2'; // Version erhöht, um alten Cache zu invalidieren
const urlsToCache = [
  // Das Caching von App-Shell-Dateien wird hier deaktiviert.
];

self.addEventListener('install', event => {
  // Der 'install'-Schritt kann leer bleiben oder für zukünftige Logik beibehalten werden.
  // Wir führen keine Caching-Operationen mehr aus.
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => {
      console.log('Cache geöffnet, aber keine Dateien zur Vorkonditionierung.');
      return cache.addAll(urlsToCache);
    })
  );
});

self.addEventListener('fetch', event => {
  // Diese Strategie versucht immer, aus dem Netzwerk zu laden.
  // Wenn sie fehlschlägt, wird der Standard-Browserfehler für Offline angezeigt.
  event.respondWith(fetch(event.request));
});

self.addEventListener('activate', event => {
  const cacheWhitelist = [CACHE_NAME];
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheWhitelist.indexOf(cacheName) === -1) {
            // Löscht alle alten Caches, die nicht auf der Whitelist stehen.
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
}); 