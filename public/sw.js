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
    
    event.waitUntil(
        clients.matchAll({
            type: 'window',
            includeUncontrolled: true
        }).then(clientList => {
            // Prüfe, ob bereits ein Tab mit der PWA offen ist
            for (let i = 0; i < clientList.length; i++) {
                const client = clientList[i];
                // Prüfe, ob es sich um unsere PWA handelt (gleiche Origin)
                if (client.url.includes(self.location.origin)) {
                    // Navigiere den bestehenden Tab zur gewünschten URL
                    client.navigate(urlToOpen);
                    // Fokussiere den Tab
                    return client.focus();
                }
            }
            // Falls kein bestehender Tab gefunden wurde, öffne einen neuen
            return clients.openWindow(urlToOpen);
        })
    );
}); 