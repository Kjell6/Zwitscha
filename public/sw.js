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
    const notification = event.notification;
    const urlToOpen = notification.data.url || '/';
    notification.close();

    // Dieses Promise stellt sicher, dass der Service Worker aktiv bleibt,
    // bis die Interaktion abgeschlossen ist.
    const promiseChain = clients.matchAll({
        type: 'window',
        includeUncontrolled: true
    }).then(windowClients => {
        let matchingClient = null;
        for (let i = 0; i < windowClients.length; i++) {
            const client = windowClients[i];
            // Prüfen, ob der Client sichtbar ist. Dies ist ein guter Indikator
            // für einen Client, den wir wiederverwenden können.
            if (client.visibilityState === 'visible') {
                matchingClient = client;
                break;
            }
        }

        if (matchingClient) {
            // Wenn wir einen Client gefunden haben, navigieren wir ihn zur Ziel-URL
            // und fokussieren ihn.
            return matchingClient.navigate(urlToOpen).then(client => client.focus());
        } else {
            // Wenn kein Client gefunden wurde, öffnen wir ein neues Fenster.
            return clients.openWindow(urlToOpen);
        }
    });

    event.waitUntil(promiseChain);
}); 