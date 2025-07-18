/**
 * Benachrichtigungs-Banner Funktionalität
 * Verwaltet die Anzeige und das Schließen des Benachrichtigungs-Banners
 */

class NotificationBanner {
    constructor() {
        this.banner = document.getElementById('notification-banner');
        this.closeButton = document.getElementById('notification-close-banner-button');
        
        this.init();
    }

    init() {
        if (!this.banner) return;

        // Banner anzeigen, wenn es existiert
        this.showBanner();
        
        // Event Listeners hinzufügen
        this.attachEventListeners();
    }

    showBanner() {
        // Prüfen, ob der Banner bereits geschlossen wurde (localStorage)
        const bannerClosed = localStorage.getItem('notification-banner-closed');
        if (bannerClosed) return;

        // Banner anzeigen
        this.banner.style.display = 'flex';
    }

    attachEventListeners() {
        // Schließen-Button
        if (this.closeButton) {
            this.closeButton.addEventListener('click', () => {
                this.closeBanner();
            });
        }
    }

    closeBanner() {
        if (this.banner) {
            this.banner.style.display = 'none';
            // Merken, dass der Banner geschlossen wurde
            localStorage.setItem('notification-banner-closed', 'true');
        }
    }
}

// Banner initialisieren, wenn DOM geladen ist
document.addEventListener('DOMContentLoaded', () => {
    new NotificationBanner();
}); 