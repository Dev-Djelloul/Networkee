<?php
/**
 * Page de partage "intent" (façon x.com/intent/post ou LinkedIn).
 * Ouverte en popup depuis un site externe avec ?url=...&title=...
 * Pré-remplit le composer avec un aperçu de l'article ; l'utilisateur publie
 * avec sa propre session (aucun jeton requis — chacun partage sur son compte).
 */
$baseUrl = '../';
$pageTitle = 'Partager sur Networkee';
$hideQuickWidget = true;
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';
session_start();

// Paramètres reçus du site externe (stockés bruts, échappés à l'affichage).
$sharedUrl   = trim($_GET['url'] ?? $_POST['shared_url'] ?? '');
$sharedTitle = trim($_GET['title'] ?? $_POST['shared_title'] ?? '');
$sharedImage = trim($_GET['image'] ?? $_POST['shared_image'] ?? '');
// N'accepte qu'une image http(s) pour l'aperçu (évite les URI javascript:/data:).
if ($sharedImage !== '' && !preg_match('#^https?://#i', $sharedImage)) {
    $sharedImage = '';
}

// Texte pré-rempli du post.
$prefill = $sharedTitle !== '' ? $sharedTitle . ' 📖' : '';
if ($sharedUrl !== '') {
    $prefill .= ($prefill !== '' ? "\n" : '') . $sharedUrl;
}

$published = false;

// Publication (session requise).
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content'], $_SESSION['user_id'])) {
    $content = trim($_POST['content']);
    if ($content !== '') {
        $stmt = $pdo->prepare("INSERT INTO posts (user_id, content, created_at) VALUES (:user_id, :content, NOW())");
        $stmt->execute(['user_id' => $_SESSION['user_id'], 'content' => $content]);
        $published = true;
    }
}

$isLoggedIn = isset($_SESSION['user_id']);
$currentUser = $_SESSION['username'] ?? 'Invité';
$currentUserImage = '';
if ($isLoggedIn) {
    $imgStmt = $pdo->prepare("SELECT profile_image FROM users WHERE id = :id");
    $imgStmt->execute(['id' => $_SESSION['user_id']]);
    $currentUserImage = $imgStmt->fetchColumn() ?: '';
}

// Domaine affiché dans l'aperçu.
$sharedHost = $sharedUrl !== '' ? (parse_url($sharedUrl, PHP_URL_HOST) ?: '') : '';

// Carte d'aperçu de l'article (image + titre + domaine), façon LinkedIn.
$previewCard = '';
if ($sharedTitle !== '' || $sharedUrl !== '' || $sharedImage !== '') {
    $previewCard = '<div class="share-preview-card">';
    if ($sharedImage !== '') {
        $previewCard .= '<img class="share-preview-img" src="' . htmlspecialchars($sharedImage)
            . '" alt="" onerror="this.style.display=\'none\'">';
    }
    $previewCard .= '<div class="share-preview-body">';
    if ($sharedTitle !== '') {
        $previewCard .= '<div class="share-preview-title">' . htmlspecialchars($sharedTitle) . '</div>';
    }
    if ($sharedHost !== '') {
        $previewCard .= '<div class="share-preview-host">' . htmlspecialchars($sharedHost) . '</div>';
    }
    $previewCard .= '</div></div>';
}

include __DIR__ . '/../includes/head.php';
?>
<body>
    <?php include(__DIR__ . '/../includes/header.php'); ?>

    <main class="page-wrapper">
        <div class="card" style="max-width: 560px; margin: 2rem auto;">
            <div class="card-body">

                <?php if ($published): ?>
                    <div style="text-align: center; padding: 1rem 0;">
                        <div style="font-size: 2.5rem;">🎉</div>
                        <h2 style="margin: 0.5rem 0;">Publié sur ton fil !</h2>
                        <p style="color: var(--text-muted);">Ton partage est en ligne sur Networkee.</p>
                        <div style="display: flex; gap: 0.75rem; justify-content: center; margin-top: 1.5rem;">
                            <a href="home.php" class="btn btn-primary" target="_blank">Voir mon fil</a>
                            <button type="button" class="btn btn-secondary" onclick="window.close()">Fermer</button>
                        </div>
                    </div>
                    <script>
                        // Ferme automatiquement la popup après un court délai (si ouverte via window.open).
                        setTimeout(() => { window.close(); }, 2500);
                    </script>

                <?php elseif (!$isLoggedIn): ?>
                    <h2 style="margin-top: 0;">Partager sur Networkee</h2>
                    <p style="color: var(--text-muted);">Connecte-toi pour partager cet article sur ton fil.</p>
                    <?php echo $previewCard; ?>
                    <a href="login.php?redirect=<?php echo urlencode('share.php?url=' . urlencode($sharedUrl) . '&title=' . urlencode($sharedTitle) . '&image=' . urlencode($sharedImage)); ?>"
                       class="btn btn-primary" style="width: 100%; justify-content: center; margin-top: 1rem;">Se connecter</a>

                <?php else: ?>
                    <h2 style="margin-top: 0; margin-bottom: 1rem;">Partager sur Networkee</h2>

                    <div class="composer-row">
                        <?php echo renderAvatar($currentUser, '', avatarUrl($currentUserImage, $baseUrl)); ?>
                        <div class="composer-main" style="flex: 1;">
                            <form method="post" class="composer-widget">
                                <textarea name="content" rows="4" placeholder="Ajoute un mot..."><?php echo htmlspecialchars($prefill); ?></textarea>

                                <?php echo $previewCard; ?>

                                <div class="composer-actions" style="margin-top: 1rem;">
                                    <button type="button" class="btn btn-secondary" onclick="window.close()">Annuler</button>
                                    <button type="submit" class="btn btn-primary">
                                        <span>Publier</span>
                                        <img src="<?php echo $baseUrl; ?>icons/icons8-send-50.png" alt="" width="30" height="30">
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </main>

    <?php include(__DIR__ . '/../includes/footer.php'); ?>

    <style>
        .share-preview-card {
            margin-top: 0.75rem;
            border: 1px solid var(--border);
            border-radius: 0.75rem;
            overflow: hidden;
            background: var(--bg-secondary, rgb(255 255 255 / 4%));
        }
        .share-preview-img {
            display: block;
            width: 100%;
            max-height: 200px;
            object-fit: cover;
            background: rgb(255 255 255 / 4%);
        }
        .share-preview-body { padding: 0.875rem 1rem; }
        .share-preview-title { font-weight: 600; line-height: 1.35; }
        .share-preview-host { margin-top: 0.25rem; font-size: 0.8125rem; color: var(--text-muted); }
    </style>
</body>
</html>
