<article class="post">
        <a href="Profil.php" class="no-post-details">
            <img
                    src="<?= htmlspecialchars($post['profilBild']) ?>"
                    alt="Profilbild von <?= htmlspecialchars($post['autor']) ?>"
                    class="post-user-image">
        </a>

        <main class="post-main-content">
            <section class="post-user-infos">
                <a href="Profil.php" class="no-post-details">
                    <img
                            src="<?= htmlspecialchars($post['profilBild']) ?>"
                            alt="Profilbild von <?= htmlspecialchars($post['autor']) ?>"
                            class="post-user-image-inline">
                </a>

                <div class="post-user-details">
                    <span class="post-author-name">
                        <?= htmlspecialchars($post['autor']) ?>
                    </span>
                    <time
                            datetime="<?= htmlspecialchars($post['datumZeit']) ?>"
                            class="post-timestamp">
                        <?= htmlspecialchars($post['time_label']) ?>
                    </time>
                </div>
                <?php if ($currentUser === $post['autor']): ?>
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
                    <?= nl2br(htmlspecialchars($post['text'])) ?>
                </p>

                <?php if (!empty($post['bildPfad'])): ?>
                    <div class="post-image-container">
                        <img
                                src="<?= htmlspecialchars($post['bildPfad']) ?>"
                                alt="Post-Bild"
                                class="post-image"
                                onclick="openLightbox('<?= htmlspecialchars($post['bildPfad']) ?>')"
                                style="cursor: pointer;">
                    </div>
                <?php endif; ?>
            </div>

            <div class="post-actions">
                <div class="post-reactions">
                    <?php foreach ($post['reactions'] as $emoji => $count): ?>
                        <button
                                class="reaction-button no-post-details"
                                type="button"
                                data-emoji="<?= htmlspecialchars($emoji) ?>">
                            <?= $emoji ?>
                            <span class="reaction-counter"><?= (int)$count ?></span>
                        </button>
                    <?php endforeach; ?>
                </div>

                <a href="postDetails.php" class="comment-link">
                    <button class="action-button comment-button" type="button">
                        <i class="bi bi-chat-dots-fill"></i>
                        <?= (int)$post['comments'] ?> Kommentar<?= $post['comments'] !== 1 ? 'e' : '' ?>
                    </button>
                </a>
            </div>
        </main>
</article>

<?php include 'lightbox.php'; ?>