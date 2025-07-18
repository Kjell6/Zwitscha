function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);
    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
}

async function subscribeUser(statusElement, buttonElement, vapidPublicKey) {
    try {
        statusElement.textContent = '1. Service Worker wird geprüft...';
        console.log('DEBUG: Warte auf navigator.serviceWorker.ready...');
        const registration = await navigator.serviceWorker.ready;
        console.log('DEBUG: Service Worker ist bereit.', registration);

        statusElement.textContent = '2. Abonnement wird erstellt...';
        console.log('DEBUG: Rufe pushManager.subscribe auf...');
        const subscription = await registration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: urlBase64ToUint8Array(vapidPublicKey)
        });
        console.log('DEBUG: Abonnement-Objekt erhalten:', subscription);

        statusElement.textContent = '3. Sende an Server...';
        console.log('DEBUG: Sende Abonnement an den Server...');
        const response = await fetch('php/subscription_handler.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ subscription })
        });
        console.log('DEBUG: Antwort vom Server erhalten.', response);

        const responseData = await response.json();
        if (!response.ok || !responseData.success) {
            throw new Error(responseData.message || 'Server-Antwort war nicht ok.');
        }

        statusElement.textContent = 'Erfolgreich aktiviert!';
        statusElement.style.color = 'green';
        buttonElement.disabled = true;

    } catch (error) {
        console.error('DEBUG: FEHLER im Abonnement-Prozess:', error);
        statusElement.textContent = 'Fehler: ' + error.message;
        statusElement.style.color = 'red';
    }
}

export function initializeNotificationManager(buttonId, statusId, vapidPublicKey, togglesSelector) {
    const enableNotificationsButton = document.getElementById(buttonId);
    const notificationStatus = document.getElementById(statusId);
    const notificationToggles = document.querySelector(togglesSelector);

    if (!enableNotificationsButton) return;

    const updateUIBasedOnSubscription = (subscription) => {
        if (subscription) {
            notificationStatus.textContent = 'Benachrichtigungen sind im Browser aktiviert.';
            notificationStatus.style.color = 'green';
            enableNotificationsButton.style.display = 'none';
            if (notificationToggles) {
                notificationToggles.style.display = 'block';
            }
        } else {
            notificationStatus.textContent = 'Benachrichtigungen sind im Browser blockiert oder nicht eingerichtet.';
            notificationStatus.style.color = 'orange';
            enableNotificationsButton.style.display = 'block';
            if (notificationToggles) {
                notificationToggles.style.display = 'none';
            }
        }
    };

    console.log("DEBUG: Notification Manager wird initialisiert.");

    enableNotificationsButton.addEventListener('click', () => {
        console.log("DEBUG: Aktivierungs-Button geklickt.");
        
        if (!('serviceWorker' in navigator)) {
            console.error("DEBUG: navigator.serviceWorker wird NICHT unterstützt.");
            notificationStatus.textContent = 'ServiceWorker wird nicht unterstützt.';
            notificationStatus.style.color = 'red';
            return;
        }
        
        if (!('PushManager' in window)) {
            console.error("DEBUG: window.PushManager wird NICHT unterstützt.");
            notificationStatus.textContent = 'Push-Benachrichtigungen werden nicht unterstützt.';
            notificationStatus.style.color = 'red';
            return;
        }

        console.log("DEBUG: Fordere Benachrichtigungs-Erlaubnis an...");
        Notification.requestPermission().then(permission => {
            console.log(`DEBUG: Erlaubnis-Status ist: ${permission}`);
            if (permission === 'granted') {
                subscribeUser(notificationStatus, enableNotificationsButton, vapidPublicKey)
                    .then(() => {
                        // Nach erfolgreichem Abo das UI aktualisieren
                        navigator.serviceWorker.ready.then(reg => reg.pushManager.getSubscription().then(updateUIBasedOnSubscription));
                    });
            } else {
                notificationStatus.textContent = 'Erlaubnis wurde verweigert.';
                notificationStatus.style.color = 'orange';
            }
        });
    });

    // Initialen Status prüfen
    if (navigator.serviceWorker && navigator.serviceWorker.ready) {
        navigator.serviceWorker.ready.then(reg => {
            reg.pushManager.getSubscription().then(updateUIBasedOnSubscription);
        });
    }
} 