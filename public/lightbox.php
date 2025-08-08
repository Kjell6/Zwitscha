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
        z-index: 2000; /* Über mobilem Header (z-index 1200) */
        align-items: center;
        justify-content: center;
        box-sizing: border-box;
    }

    .lightbox-content {
        position: relative;
        max-width: 100%;
        max-height: 100%;
    }

    .lightbox-close {
        position: absolute;
        right: 0;
        background: rgba(0, 0, 0, 0.5);
        border: none;
        color: white;
        font-size: 30px;
        cursor: pointer;
        z-index: 2001; /* Knopf über Lightbox-Inhalt */
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 4px;
        border-radius: 8px;
        transition: background-color 0.2s ease;
    }

    .lightbox-close:hover {
        background-color: rgba(0, 0, 0, 0.8);
        color: white;
    }

    #lightbox-image {
        max-width: calc(100vw - 40px);
        max-height: calc(100vh - 40px);
        width: auto;
        height: auto;
        object-fit: contain;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    }

    /* Mobile Anpassungen */
    @media (max-width: 768px) {
        .lightbox {
            padding: 10px;
        }
        
        #lightbox-image {
            max-width: calc(100vw - 20px);
            max-height: calc(100vh - 20px);
        }
        
        .lightbox-close {
            top: 5px;
            right: 5px;
            width: 35px;
            height: 35px;
            font-size: 20px;
        }
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