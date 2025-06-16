// Automatische Bildkomprimierung für Upload-Kompatibilität
class ImageCompressor {
    constructor(options = {}) {
        this.maxSizeKB = options.maxSizeKB || 1800; // 1.8MB (unter Server-Limit)
        this.quality = options.quality || 0.8;
        this.maxDimension = options.maxDimension || 1920;
    }

    // Hauptfunktion zur Bildkomprimierung
    async compressImage(file) {
        return new Promise((resolve, reject) => {
            // Prüfe ob es ein Bild ist
            if (!file.type.startsWith('image/')) {
                reject(new Error('Datei ist kein Bild'));
                return;
            }

            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            const img = new Image();
            
            img.onload = () => {
                try {
                    // Berechne neue Dimensionen
                    let { width, height } = img;
                    
                    if (width > this.maxDimension || height > this.maxDimension) {
                        if (width > height) {
                            height = (height * this.maxDimension) / width;
                            width = this.maxDimension;
                        } else {
                            width = (width * this.maxDimension) / height;
                            height = this.maxDimension;
                        }
                    }
                    
                    canvas.width = width;
                    canvas.height = height;
                    
                    // Zeichne Bild auf Canvas
                    ctx.drawImage(img, 0, 0, width, height);
                    
                    // Erste Komprimierung
                    canvas.toBlob((blob) => {
                        if (blob.size <= this.maxSizeKB * 1024) {
                            // Bereits klein genug
                            resolve(this.createFile(blob, file.name));
                        } else {
                            // Weitere Komprimierung nötig
                            this.compressUntilSize(canvas, file.name, resolve);
                        }
                    }, 'image/jpeg', this.quality);
                    
                } catch (error) {
                    reject(error);
                }
            };
            
            img.onerror = () => reject(new Error('Bild konnte nicht geladen werden'));
            img.src = URL.createObjectURL(file);
        });
    }

    // Komprimiere bis Zielgröße erreicht
    compressUntilSize(canvas, fileName, resolve) {
        let quality = this.quality;
        
        const tryCompress = () => {
            canvas.toBlob((blob) => {
                if (blob.size <= this.maxSizeKB * 1024 || quality <= 0.1) {
                    resolve(this.createFile(blob, fileName));
                } else {
                    quality -= 0.1;
                    tryCompress();
                }
            }, 'image/jpeg', quality);
        };
        
        tryCompress();
    }

    // Erstelle File-Objekt vom Blob
    createFile(blob, originalName) {
        const name = originalName.replace(/\.[^/.]+$/, '.jpg'); // Ändere Endung zu .jpg
        return new File([blob], name, {
            type: 'image/jpeg',
            lastModified: Date.now()
        });
    }

    // Benutzerfreundliche Upload-Funktion
    async handleFileInput(fileInput, previewElement, callback) {
        const file = fileInput.files[0];
        if (!file) return null;

        try {
            // Zeige Loading-Indikator
            if (previewElement) {
                previewElement.style.opacity = '0.5';
                previewElement.title = 'Komprimiere Bild...';
            }

            const compressedFile = await this.compressImage(file);
            
            // Aktualisiere File Input mit komprimierter Datei
            const dt = new DataTransfer();
            dt.items.add(compressedFile);
            fileInput.files = dt.files;

            // Zeige Vorschau
            if (previewElement) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    previewElement.src = e.target.result;
                    previewElement.style.opacity = '1';
                    previewElement.title = '';
                };
                reader.readAsDataURL(compressedFile);
            }

            // Callback aufrufen
            if (callback) callback(compressedFile);

            console.log(`Bild komprimiert: ${file.size} → ${compressedFile.size} bytes`);
            return compressedFile;

        } catch (error) {
            console.error('Komprimierung fehlgeschlagen:', error);
            
            // Zeige Fehlerstatus
            if (previewElement) {
                previewElement.style.opacity = '1';
                previewElement.title = 'Komprimierung fehlgeschlagen';
            }
            
            // Fallback: Originaldatei verwenden (falls unter Server-Limit)
            if (file.size <= this.maxSizeKB * 1024) {
                return file;
            } else {
                throw new Error('Bild zu groß und Komprimierung fehlgeschlagen');
            }
        }
    }
}

// Globale Instanz
window.imageCompressor = new ImageCompressor(); 