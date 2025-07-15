/**
 * Bildvorschau und -komprimierung Funktionalität
 * Vereinheitlicht die Bildverarbeitung für verschiedene Formulare
 */

/**
 * Bildvorschau-System initialisieren
 * @param {Object} config - Konfigurationsobjekt
 * @param {string} config.inputId - ID des File-Input-Elements
 * @param {string} config.previewId - ID des Vorschau-Bild-Elements
 * @param {string} config.previewContainerId - ID des Vorschau-Containers (optional)
 * @param {string} config.removeButtonId - ID des Entfernen-Buttons (optional)
 * @param {Array} config.allowedTypes - Erlaubte Dateitypen (optional)
 * @param {Function} config.onSuccess - Callback nach erfolgreichem Komprimieren (optional)
 * @param {Function} config.onError - Callback bei Fehler (optional)
 * @param {boolean} config.showPreviewContainer - Ob der Vorschau-Container angezeigt werden soll
 */
function initializeImagePreview(config) {
    // Validierung der erforderlichen Parameter
    if (!config.inputId || !config.previewId) {
        console.error('ImagePreview: inputId und previewId sind erforderlich');
        return;
    }

    const imageInput = document.getElementById(config.inputId);
    const previewImg = document.getElementById(config.previewId);
    const previewContainer = config.previewContainerId ? document.getElementById(config.previewContainerId) : null;
    const removeButton = config.removeButtonId ? document.getElementById(config.removeButtonId) : null;

    if (!imageInput || !previewImg) {
        console.error('ImagePreview: Input oder Vorschau-Element nicht gefunden');
        return;
    }

    // Standard-Konfiguration
    const defaultConfig = {
        allowedTypes: ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'],
        showPreviewContainer: true,
        onSuccess: null,
        onError: null
    };

    const settings = { ...defaultConfig, ...config };

    // Event-Listener für Dateiauswahl
    imageInput.addEventListener('change', async (event) => {
        const file = event.target.files[0];
        
        if (!file) {
            resetPreview();
            return;
        }

        // Dateiformatvalidierung
        if (!isValidFileType(file, settings.allowedTypes)) {
            const errorMessage = 'Nur JPEG, PNG, GIF und WebP Dateien sind erlaubt.';
            alert(errorMessage);
            resetPreview();
            
            if (settings.onError) {
                settings.onError(new Error(errorMessage));
            }
            return;
        }

        try {
            // Vorschau-Container anzeigen
            if (previewContainer && settings.showPreviewContainer) {
                previewContainer.style.display = 'block';
            }

            // Bildkomprimierung und Vorschau-Update
            await window.imageCompressor.handleFileInput(imageInput, previewImg, (compressedFile) => {
                const originalSizeMB = (file.size / (1024 * 1024)).toFixed(2);
                const compressedSizeMB = (compressedFile.size / (1024 * 1024)).toFixed(2);
                console.log(`Komprimierung: ${originalSizeMB}MB → ${compressedSizeMB}MB`);
                
                if (settings.onSuccess) {
                    settings.onSuccess(compressedFile, file);
                }
            });

        } catch (error) {
            const errorMessage = 'Fehler bei der Bildverarbeitung: ' + error.message;
            alert(errorMessage);
            resetPreview();
            
            if (settings.onError) {
                settings.onError(error);
            }
        }
    });

    // Event-Listener für Entfernen-Button
    if (removeButton) {
        removeButton.addEventListener('click', () => {
            resetPreview();
        });
    }

    /**
     * Vorschau zurücksetzen
     */
    function resetPreview() {
        imageInput.value = '';
        previewImg.src = '#';
        
        if (previewContainer) {
            previewContainer.style.display = 'none';
        }
    }

    /**
     * Dateityp validieren
     * @param {File} file - Zu prüfende Datei
     * @param {Array} allowedTypes - Erlaubte Dateitypen
     * @returns {boolean} Ob der Dateityp erlaubt ist
     */
    function isValidFileType(file, allowedTypes) {
        const fileType = file.type.toLowerCase();
        return allowedTypes.some(type =>
            fileType === type ||
            (type === 'image/jpeg' && fileType === 'image/jpg') ||
            (type === 'image/jpg' && fileType === 'image/jpeg')
        );
    }
}

/**
 * Einfache Bildvorschau-Initialisierung für Standard-Fälle
 * @param {string} inputId - ID des File-Inputs
 * @param {string} previewId - ID des Vorschau-Bildes
 * @param {string} previewContainerId - ID des Vorschau-Containers
 * @param {string} removeButtonId - ID des Entfernen-Buttons
 */
function initializeSimpleImagePreview(inputId, previewId, previewContainerId, removeButtonId) {
    initializeImagePreview({
        inputId: inputId,
        previewId: previewId,
        previewContainerId: previewContainerId,
        removeButtonId: removeButtonId
    });
}

/**
 * Avatar-Bildvorschau initialisieren (ohne Entfernen-Button)
 * @param {string} inputId - ID des File-Inputs
 * @param {string} previewId - ID des Vorschau-Bildes
 * @param {Function} onSuccess - Callback nach erfolgreichem Komprimieren
 * @param {Function} onError - Callback bei Fehler
 */
function initializeAvatarImagePreview(inputId, previewId, onSuccess, onError) {
    initializeImagePreview({
        inputId: inputId,
        previewId: previewId,
        showPreviewContainer: false,
        onSuccess: onSuccess,
        onError: onError
    });
} 