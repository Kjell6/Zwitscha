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

/**
 * Prüft, ob ein Abonnement auch auf dem Server existiert.
 * @param {PushSubscription} subscription - Das Abonnement-Objekt vom Browser.
 * @returns {Promise<boolean>} - True, wenn auf dem Server registriert, sonst false.
 */
async function verifySubscriptionOnServer(subscription) {
    if (!subscription) return false;
    
    try {
        const response = await fetch('php/check_subscription.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ endpoint: subscription.endpoint })
        });
        const data = await response.json();
        return data.is_subscribed === true;
    } catch (error) {
        console.error('Fehler bei der Verifizierung des Abonnements:', error);
        return false; // Bei Fehler annehmen, dass es nicht synchron ist.
    }
}

export function initializeNotificationManager(buttonId, statusId, vapidPublicKey, togglesSelector) {
    const enableNotificationsButton = document.getElementById(buttonId);
    const notificationStatus = document.getElementById(statusId);
    const notificationToggles = document.querySelector(togglesSelector);

    if (!enableNotificationsButton) return;

    /**
     * Aktualisiert die UI basierend auf dem Abonnement-Status im Browser und auf dem Server.
     * @param {PushSubscription|null} subscription - Das Abonnement-Objekt vom Browser.
     */
    const updateUIBasedOnSubscription = async (subscription) => {
        if (subscription) {
            const isVerified = await verifySubscriptionOnServer(subscription);
            
            if (isVerified) {
                // Fall A: Alles ist synchron.
                notificationStatus.textContent = 'Benachrichtigungen sind aktiv.';
                notificationStatus.style.color = 'green';
                enableNotificationsButton.style.display = 'none';
                if (notificationToggles) notificationToggles.style.display = 'block';
            } else {
                // Fall B: "Zombie-Abonnement" gefunden.
                notificationStatus.textContent = 'Synchronisiere... Bitte warten.';
                notificationStatus.style.color = 'orange';
                
                // Altes Abo im Browser löschen und UI zurücksetzen.
                await subscription.unsubscribe();
                
                notificationStatus.textContent = 'Benachrichtigungen sind nicht mehr synchron. Bitte erneut aktivieren.';
                enableNotificationsButton.style.display = 'block';
                if (notificationToggles) notificationToggles.style.display = 'none';
            }
        } else {
            // Kein Abonnement im Browser gefunden.
            notificationStatus.textContent = 'Benachrichtigungen sind nicht eingerichtet.';
            notificationStatus.style.color = '';
            enableNotificationsButton.style.display = 'block';
            if (notificationToggles) notificationToggles.style.display = 'none';
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
                    .then(async () => {
                        // Nach erfolgreichem Abo das UI aktualisieren
                        const reg = await navigator.serviceWorker.ready;
                        const subscription = await reg.pushManager.getSubscription();
                        await updateUIBasedOnSubscription(subscription);
                    });
            } else {
                notificationStatus.textContent = 'Erlaubnis wurde verweigert.';
                notificationStatus.style.color = 'orange';
            }
        });
    });

    // Initialen Status prüfen
    if (navigator.serviceWorker && navigator.serviceWorker.ready) {
        navigator.serviceWorker.ready.then(async reg => {
            const subscription = await reg.pushManager.getSubscription();
            await updateUIBasedOnSubscription(subscription);
        });
    }
} 