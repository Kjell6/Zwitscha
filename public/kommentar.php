<article class="post">
    <a href="Profil.php" class="no-post-details">
        <img
                src="<?= htmlspecialchars($kommentar['profilBild']) ?>"
                alt="Profilbild von <?= htmlspecialchars($kommentar['autor']) ?>"
                class="post-user-image">
    </a>

    <main class="post-main-content">
        <section class="post-user-infos">
            <a href="Profil.php" class="no-post-details">
                <img
                        src="<?= htmlspecialchars($kommentar['profilBild']) ?>"
                        alt="Profilbild von <?= htmlspecialchars($kommentar['autor']) ?>"
                        class="post-user-image-inline">
            </a>

            <div class="post-user-details">
                    <span class="post-author-name">
                        <?= htmlspecialchars($kommentar['autor']) ?>
                    </span>
                <time
                        datetime="<?= htmlspecialchars($kommentar['datumZeit']) ?>"
                        class="post-timestamp">
                    <?= htmlspecialchars($kommentar['time_label']) ?>
                </time>
            </div>
            <?php if ($currentUser === $kommentar['autor']): ?>
                <button
                        class="post-options-button no-post-details"
                        type="button"
                        aria-label="Post-Optionen">
                    <i class="bi bi-trash-fill"></i>
                </button>
            <?php endif; ?>
        </section>

        <div class="post-content">
            <p>
                <?= nl2br(htmlspecialchars($kommentar['text'])) ?>
            </p>
        </div>
    </main>
</article>