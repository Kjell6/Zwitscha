<!-- Lightbox -->
<div id="lightbox" class="lightbox" onclick="closeLightbox()">
    <div class="lightbox-content" onclick="event.stopPropagation()">
        <button class="lightbox-close" onclick="closeLightbox()">&times;</button>
        <img id="lightbox-image" src="" alt="Vergrößertes Bild">
    </div>
</div>

<style>
    .lightbox {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.8);
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }

    .lightbox-content {
        position: relative;
        max-width: 90%;
        max-height: 90%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .lightbox-close {
        position: absolute;
        top: -40px;
        right: 0;
        background: none;
        border: none;
        color: white;
        font-size: 30px;
        cursor: pointer;
        z-index: 1001;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .lightbox-close:hover {
        color: #ccc;
    }

    #lightbox-image {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
        border-radius: 8px;
    }
</style>

<script>
    function openLightbox(imageSrc) {
        const lightbox = document.getElementById('lightbox');
        const lightboxImage = document.getElementById('lightbox-image');

        lightboxImage.src = imageSrc;
        lightbox.style.display = 'flex';
        document.body.style.overflow = 'hidden'; // Verhindert Scrollen im Hintergrund
    }

    function closeLightbox() {
        const lightbox = document.getElementById('lightbox');
        lightbox.style.display = 'none';
        document.body.style.overflow = 'auto'; // Scrollen wieder aktivieren
    }

    // ESC-Taste zum Schließen
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeLightbox();
        }
    });
</script>