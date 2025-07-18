const CACHE_NAME = 'zwitscha-cache-v7'; // Version erhöht, um alles neu zu laden

self.addEventListener('install', event => {
  // Zwingt den neuen Service Worker, sofort mit der Aktivierung zu beginnen.
  event.waitUntil(self.skipWaiting());
});

self.addEventListener('activate', event => {
  // Übernimmt sofort die Kontrolle über alle offenen Clients (Tabs).
  event.waitUntil(self.clients.claim());
});

self.addEventListener('push', event => {
    let data = {};
    if (event.data) {
        data = event.data.json();
    }
    const title = data.title || 'Zwitscha';
    const options = {
        body: data.body || 'Du hast eine neue Benachrichtigung.',
        icon: data.icon || 'assets/ZwitschaIcon.png',
        badge: 'assets/ZwitschaIcon.png',
        data: {
            url: data.url || '/'
        }
    };
    event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', event => {
    event.notification.close();
    const urlToOpen = event.notification.data.url || '/';
    event.waitUntil(clients.openWindow(urlToOpen));
}); 