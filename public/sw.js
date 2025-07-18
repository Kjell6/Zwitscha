const CACHE_NAME = 'zwitscha-cache-v5'; // Version erhöhen für sofortige Aktualisierung
const urlsToCache = [];

self.addEventListener('install', event => {
  // Sofort aktivieren ohne auf andere Tabs zu warten
  event.waitUntil(self.skipWaiting());
});

self.addEventListener('fetch', event => {
  // Lass alle POST-Requests durch (wichtig für Login/Logout)
  if (event.request.method !== 'GET') {
    return;
  }

  // Lass alle Navigation-Anfragen durch (Seitenwechsel, Weiterleitungen)
  if (event.request.mode === 'navigate') {
    return;
  }

  // Explizit Login/Logout/Session-bezogene URLs nicht abfangen
  const url = new URL(event.request.url);
  if (url.pathname.includes('Login.php') || 
      url.pathname.includes('logout.php') || 
      url.pathname.includes('Register.php') ||
      url.pathname.includes('session_helper.php') ||
      url.pathname.includes('NutzerVerwaltung.php')) {
    return;
  }

  // Für alle anderen GET-Anfragen: Nur-Netzwerk-Strategie
  event.respondWith(fetch(event.request));
});

self.addEventListener('activate', event => {
  // Sofort die Kontrolle übernehmen
  event.waitUntil(self.clients.claim());
  
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