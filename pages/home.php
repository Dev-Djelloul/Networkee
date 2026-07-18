<?php
$baseUrl = '../';
$pageTitle = 'Le Fil — Networkee';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';
session_start();

$postsPerPage = 5;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $postsPerPage;

$stmt = $pdo->prepare("
    SELECT posts.id, posts.user_id, posts.content, posts.image, posts.video, posts.created_at,
           users.username, users.profile_image,
           NULL AS repost_username, NULL AS repost_user_id, posts.created_at AS sort_date
    FROM posts
    JOIN users ON posts.user_id = users.id
    UNION ALL
    SELECT posts.id, posts.user_id, posts.content, posts.image, posts.video, posts.created_at,
           users.username, users.profile_image,
           reposter.username AS repost_username, reposter.id AS repost_user_id, reposts.created_at AS sort_date
    FROM reposts
    JOIN posts ON reposts.post_id = posts.id
    JOIN users ON posts.user_id = users.id
    JOIN users reposter ON reposts.user_id = reposter.id
    ORDER BY sort_date DESC
    LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':limit', $postsPerPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$posts = $stmt->fetchAll();

$totalPosts = $pdo->query("SELECT COUNT(*) FROM posts")->fetchColumn() + $pdo->query("SELECT COUNT(*) FROM reposts")->fetchColumn();
$totalPages = max(1, ceil($totalPosts / $postsPerPage));

// Ajout de commentaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_content'], $_POST['post_id']) && isset($_SESSION['user_id'])) {
    $comment_content = trim($_POST['comment_content']);
    $post_id = (int) $_POST['post_id'];

    if (!empty($comment_content)) {
        $stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, content, created_at) VALUES (:post_id, :user_id, :content, NOW())");
        $stmt->execute([
            'post_id' => $post_id,
            'user_id' => $_SESSION['user_id'],
            'content' => $comment_content
        ]);

        $authorStmt = $pdo->prepare("SELECT user_id FROM posts WHERE id = :id");
        $authorStmt->execute(['id' => $post_id]);
        $postAuthorId = (int) $authorStmt->fetchColumn();
        if ($postAuthorId) {
            createNotification($postAuthorId, (int) $_SESSION['user_id'], 'comment', $post_id, $pdo);
        }

        header("Location: home.php?page=$page");
        exit;
    } else {
        $commentError = 'Le commentaire ne peut pas être vide.';
    }
}

// Gestion des likes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['like'], $_SESSION['user_id'])) {
    $postId = (int) $_POST['like'];
    $userId = $_SESSION['user_id'];

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE post_id = :post_id AND user_id = :user_id");
    $stmt->execute(['post_id' => $postId, 'user_id' => $userId]);
    $liked = $stmt->fetchColumn() > 0;

    if ($liked) {
        $stmt = $pdo->prepare("DELETE FROM likes WHERE post_id = :post_id AND user_id = :user_id");
        $stmt->execute(['post_id' => $postId, 'user_id' => $userId]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO likes (post_id, user_id) VALUES (:post_id, :user_id)");
        $stmt->execute(['post_id' => $postId, 'user_id' => $userId]);

        $authorStmt = $pdo->prepare("SELECT user_id FROM posts WHERE id = :id");
        $authorStmt->execute(['id' => $postId]);
        $postAuthorId = (int) $authorStmt->fetchColumn();
        if ($postAuthorId) {
            createNotification($postAuthorId, (int) $userId, 'like', $postId, $pdo);
        }
    }
    header("Location: home.php?page=$page");
    exit;
}

// Repartager / annuler le repartage
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['repost'], $_SESSION['user_id'])) {
    $postId = (int) $_POST['repost'];
    $userId = (int) $_SESSION['user_id'];

    if (hasUserReposted($postId, $userId, $pdo)) {
        $pdo->prepare("DELETE FROM reposts WHERE post_id = :post_id AND user_id = :user_id")
            ->execute(['post_id' => $postId, 'user_id' => $userId]);
    } else {
        $pdo->prepare("INSERT INTO reposts (post_id, user_id) VALUES (:post_id, :user_id)")
            ->execute(['post_id' => $postId, 'user_id' => $userId]);

        $authorStmt = $pdo->prepare("SELECT user_id FROM posts WHERE id = :id");
        $authorStmt->execute(['id' => $postId]);
        $postAuthorId = (int) $authorStmt->fetchColumn();
        if ($postAuthorId) {
            createNotification($postAuthorId, $userId, 'repost', $postId, $pdo);
        }
    }
    header("Location: home.php?page=$page");
    exit;
}

// Suppression d'un post (auteur uniquement)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_post'], $_SESSION['user_id'])) {
    $deleteId = (int) $_POST['delete_post'];
    $stmt = $pdo->prepare("SELECT user_id, image, video FROM posts WHERE id = :id");
    $stmt->execute(['id' => $deleteId]);
    $postToDelete = $stmt->fetch();

    if ($postToDelete && (int) $postToDelete['user_id'] === (int) $_SESSION['user_id']) {
        $pdo->prepare("DELETE FROM posts WHERE id = :id")->execute(['id' => $deleteId]);
        foreach (['image', 'video'] as $mediaField) {
            if (!empty($postToDelete[$mediaField])) {
                $mediaPath = __DIR__ . '/../uploads/' . $postToDelete[$mediaField];
                if (is_file($mediaPath)) {
                    @unlink($mediaPath);
                }
            }
        }
    }
    header("Location: home.php?page=$page");
    exit;
}

// Nouveau post depuis le fil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content'], $_SESSION['user_id']) && !isset($_POST['comment_content']) && !isset($_POST['like'])) {
    $content = htmlspecialchars(trim($_POST['content']), ENT_QUOTES, 'UTF-8');
    $image = null;
    $video = null;

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif'];
        if (array_key_exists($ext, $allowed) && $_FILES['image']['type'] === $allowed[$ext]) {
            $name = uniqid('post_') . '.' . $ext;
            $target = __DIR__ . '/../uploads/' . $name;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                $image = $name;
            }
        }
    }

    if (isset($_FILES['video']) && $_FILES['video']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['video']['name'], PATHINFO_EXTENSION));
        $allowed = ['mp4' => 'video/mp4', 'webm' => 'video/webm', 'ogg' => 'video/ogg', 'mov' => 'video/quicktime'];
        if (array_key_exists($ext, $allowed) && $_FILES['video']['type'] === $allowed[$ext]) {
            $name = uniqid('post_') . '.' . $ext;
            $target = __DIR__ . '/../uploads/' . $name;
            if (move_uploaded_file($_FILES['video']['tmp_name'], $target)) {
                $video = $name;
            }
        }
    }

    // Un post est valide s'il contient du texte OU un média
    if ($content !== '' || $image !== null || $video !== null) {
        $stmt = $pdo->prepare("INSERT INTO posts (user_id, content, image, video, created_at) VALUES (:user_id, :content, :image, :video, NOW())");
        $stmt->execute(['user_id' => $_SESSION['user_id'], 'content' => $content, 'image' => $image, 'video' => $video]);
    }
    header("Location: home.php?page=1");
    exit;
}

$currentUser = $_SESSION['username'] ?? 'Invité';

// Photo de l'utilisateur connecté (pour le composer et son commentaire)
$currentUserImage = '';
if (isset($_SESSION['user_id'])) {
    $imgStmt = $pdo->prepare("SELECT profile_image FROM users WHERE id = :id");
    $imgStmt->execute(['id' => $_SESSION['user_id']]);
    $currentUserImage = $imgStmt->fetchColumn() ?: '';
}
?>
<?php include __DIR__ . '/../includes/head.php'; ?>
<body>
    <?php include(__DIR__ . '/../includes/header.php'); ?>

    <main class="page-wrapper">
        <!-- Composer -->
        <?php if (isset($_SESSION['user_id'])): ?>
        <div class="card composer">
            <div class="card-body">
                <div class="composer-row">
                    <?php echo renderAvatar($currentUser, '', avatarUrl($currentUserImage, $baseUrl)); ?>
                    <div class="composer-main">
                        <form action="home.php" method="post" enctype="multipart/form-data" class="composer-widget">
                            <textarea name="content" rows="2" placeholder="Quoi de neuf aujourd'hui ?"></textarea>

                            <div class="composer-media-preview" hidden>
                                <img class="composer-preview-img" alt="Aperçu de l'image" hidden>
                                <video class="composer-preview-video" controls hidden></video>
                                <button type="button" class="composer-media-remove" aria-label="Retirer le média">✕</button>
                                <div class="composer-media-label"></div>
                            </div>

                            <div class="composer-actions">
                                <div class="composer-tools">
                                    <button type="button" class="icon-btn composer-image-btn" title="Ajouter une image">
                                        <img src="<?php echo $baseUrl; ?>icons/icons8-picture-50.png" alt="Image" width="26" height="26">
                                    </button>
                                    <input type="file" class="composer-image-input" name="image" accept="image/jpeg,image/png,image/gif" hidden>

                                    <button type="button" class="icon-btn composer-video-btn" title="Ajouter une vidéo">
                                        <img src="<?php echo $baseUrl; ?>icons/icons8-video-50.png" alt="Vidéo" width="26" height="26">
                                    </button>
                                    <input type="file" class="composer-video-input" name="video" accept="video/mp4,video/webm,video/ogg,video/quicktime" hidden>

                                    <div class="composer-emoji-wrapper">
                                        <button type="button" class="icon-btn composer-emoji-btn" title="Ajouter un emoji">😊</button>
                                        <div class="emoji-picker"></div>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <span>Publier</span>
                                    <img src="<?php echo $baseUrl; ?>icons/icons8-send-50.png" alt="" width="30" height="30">
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Feed -->
        <div class="feed">
            <?php foreach ($posts as $post): ?>
            <?php
                $comments = getComments($post['id'], $pdo);
                $likeCount = getLikeCount($post['id'], $pdo);
                $userLiked = isset($_SESSION['user_id']) && hasUserLikedPost($post['id'], $_SESSION['user_id'], $pdo);
                $postStyle = getAvatarStyle($post['username']);
            ?>
            <article class="post" id="post-<?php echo $post['id']; ?>">
                <?php if (!empty($post['repost_username'])): ?>
                <div class="post-repost-banner">
                    <img src="<?php echo $baseUrl; ?>icons/icons8-repost-64.png" alt="" width="14" height="14">
                    <a href="profile.php?id=<?php echo (int) $post['repost_user_id']; ?>"><?php echo htmlspecialchars($post['repost_username']); ?></a> a repartagé
                </div>
                <?php endif; ?>
                <div class="post-header">
                    <div class="post-author">
                        <?php echo renderAvatar($post['username'], '', avatarUrl($post['profile_image'], $baseUrl)); ?>
                        <div class="post-meta">
                            <h3><a href="profile.php?id=<?php echo $post['user_id']; ?>"><?php echo htmlspecialchars($post['username']); ?></a></h3>
                            <time><?php echo timeAgo($post['created_at']); ?></time>
                        </div>
                    </div>
                    <div class="post-menu-wrapper">
                        <button type="button" class="post-menu" aria-label="Options" onclick="togglePostMenu(this)">
                            <?php echo renderIcon('more', 20); ?>
                        </button>
                        <div class="post-menu-dropdown">
                            <button type="button" class="post-menu-item" onclick="copyPostLink(<?php echo $post['id']; ?>)">
                                <img src="<?php echo $baseUrl; ?>icons/icons8-link-50.png" alt="" width="26" height="26"> Copier le lien
                            </button>
                            <?php if (isset($_SESSION['user_id']) && (int) $post['user_id'] === (int) $_SESSION['user_id']): ?>
                                <form method="POST" action="home.php?page=<?php echo $page; ?>" class="confirm-form" data-confirm-message="Supprimer définitivement cette publication ? Cette action est irréversible.">
                                    <input type="hidden" name="delete_post" value="<?php echo $post['id']; ?>">
                                    <button type="submit" class="post-menu-item post-menu-item-danger">
                                        <img src="<?php echo $baseUrl; ?>icons/icons8-delete-50.png" alt="" width="26" height="26"> Supprimer
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="post-content">
                    <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                </div>

                <?php if ($post['image']): ?>
                    <img src="<?php echo $baseUrl; ?>uploads/<?php echo htmlspecialchars($post['image']); ?>" alt="Image du post" class="post-image">
                <?php elseif (!empty($post['video'])): ?>
                    <video src="<?php echo $baseUrl; ?>uploads/<?php echo htmlspecialchars($post['video']); ?>" class="post-image" controls></video>
                <?php endif; ?>

                <div class="post-actions">
                    <span class="hover-stat" style="display: inline-flex;">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <form id="like-form-<?php echo $post['id']; ?>" method="POST" action="home.php?page=<?php echo $page; ?>" style="display: inline;">
                                <button type="submit" name="like" value="<?php echo $post['id']; ?>" class="action-btn <?php echo $userLiked ? 'active' : ''; ?>">
                                    <img src="<?php echo $baseUrl; ?>icons/icons8-like-heart-50.png" alt="" width="30" height="30">
                                    <span><?php echo $likeCount; ?></span>
                                </button>
                            </form>
                        <?php else: ?>
                            <button type="button" class="action-btn" onclick="openLoginModal('like', <?php echo $post['id']; ?>)">
                                <img src="<?php echo $baseUrl; ?>icons/icons8-like-heart-50.png" alt="" width="30" height="30">
                                <span><?php echo $likeCount; ?></span>
                            </button>
                        <?php endif; ?>
                        <div class="hover-popover">
                            <?php echo renderHoverList(getPostLikers((int) $post['id'], $pdo), 'Aucun like pour le moment.', $baseUrl); ?>
                        </div>
                    </span>
                    <button type="button" class="action-btn" onclick="<?php echo isset($_SESSION['user_id']) ? "focusComment({$post['id']})" : "openLoginModal('comment', {$post['id']})"; ?>">
                        <img src="<?php echo $baseUrl; ?>icons/icons8-comment-50.png" alt="" width="30" height="30">
                        <span><?php echo count($comments); ?></span>
                    </button>
                    <?php $userReposted = isset($_SESSION['user_id']) && hasUserReposted((int) $post['id'], (int) $_SESSION['user_id'], $pdo); ?>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <form id="repost-form-<?php echo $post['id']; ?>" method="POST" action="home.php?page=<?php echo $page; ?>" style="display: inline;">
                            <button type="submit" name="repost" value="<?php echo $post['id']; ?>" class="action-btn <?php echo $userReposted ? 'active' : ''; ?>" title="<?php echo $userReposted ? 'Annuler le repartage' : 'Repartager'; ?>">
                                <img src="<?php echo $baseUrl; ?>icons/icons8-repost-64.png" alt="" width="30" height="30">
                                <span><?php echo getRepostCount((int) $post['id'], $pdo); ?></span>
                            </button>
                        </form>
                    <?php else: ?>
                        <button type="button" class="action-btn" onclick="openLoginModal('repost', <?php echo $post['id']; ?>)" title="Repartager">
                            <img src="<?php echo $baseUrl; ?>icons/icons8-repost-64.png" alt="" width="30" height="30">
                            <span><?php echo getRepostCount((int) $post['id'], $pdo); ?></span>
                        </button>
                    <?php endif; ?>
                    <div class="post-menu-wrapper" style="margin-left: auto;">
                        <button type="button" class="action-btn" aria-label="Partager" onclick="togglePostMenu(this)">
                            <img src="<?php echo $baseUrl; ?>icons/icons8-upload-50.png" alt="" width="30" height="30">
                        </button>
                        <div class="post-menu-dropdown post-share-dropdown">
                            <?php
                                $shareUrl = urlencode((!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '#post-' . $post['id']);
                                $shareText = urlencode(mb_substr($post['content'], 0, 100));
                            ?>
                            <a class="post-menu-item" target="_blank" rel="noopener" href="https://twitter.com/intent/tweet?url=<?php echo $shareUrl; ?>&text=<?php echo $shareText; ?>">
                                <img src="<?php echo $baseUrl; ?>icons/icons8-x-50.png" alt="" width="28" height="28"> X (Twitter)
                            </a>
                            <a class="post-menu-item" target="_blank" rel="noopener" href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo $shareUrl; ?>">
                                <img src="<?php echo $baseUrl; ?>icons/icons8-linkedin-50.png" alt="" width="28" height="28"> LinkedIn
                            </a>
                            <a class="post-menu-item" target="_blank" rel="noopener" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $shareUrl; ?>">
                                <img src="<?php echo $baseUrl; ?>icons/icons8-facebook-50.png" alt="" width="28" height="28"> Facebook
                            </a>
                            <a class="post-menu-item" target="_blank" rel="noopener" href="https://wa.me/?text=<?php echo $shareText . '%20' . $shareUrl; ?>">
                                <img src="<?php echo $baseUrl; ?>icons/icons8-whatsapp-50.png" alt="" width="28" height="28"> WhatsApp
                            </a>
                            <a class="post-menu-item" href="mailto:?subject=<?php echo $shareText; ?>&body=<?php echo $shareUrl; ?>">
                                <img src="<?php echo $baseUrl; ?>icons/icons8-email-50.png" alt="" width="28" height="28"> E-mail
                            </a>
                            <button type="button" class="post-menu-item" onclick="copyPostLink(<?php echo $post['id']; ?>)">
                                <img src="<?php echo $baseUrl; ?>icons/icons8-link-50.png" alt="" width="28" height="28"> Copier le lien
                            </button>
                        </div>
                    </div>
                </div>

                <?php if (count($comments) > 0 || isset($_SESSION['user_id'])): ?>
                <div class="comments-section">
                    <?php
                        $commentTotal = count($comments);
                        $visibleComments = $commentTotal > 2 ? array_slice($comments, -2) : $comments;
                        $hiddenComments = $commentTotal > 2 ? array_slice($comments, 0, $commentTotal - 2) : [];
                    ?>
                    <?php if (!empty($hiddenComments)): ?>
                    <button type="button" class="comments-toggle" onclick="toggleComments(<?php echo $post['id']; ?>)">
                        Voir les <?php echo count($hiddenComments); ?> commentaire<?php echo count($hiddenComments) > 1 ? 's' : ''; ?> précédent<?php echo count($hiddenComments) > 1 ? 's' : ''; ?>
                    </button>
                    <div id="comments-hidden-<?php echo $post['id']; ?>" class="comments-hidden">
                        <?php foreach ($hiddenComments as $comment): ?>
                        <div class="comment">
                            <?php echo renderAvatar($comment['username'], 'sm', avatarUrl($comment['profile_image'], $baseUrl)); ?>
                            <div class="comment-bubble">
                                <p class="comment-author"><?php echo htmlspecialchars($comment['username']); ?></p>
                                <p class="comment-text"><?php echo htmlspecialchars($comment['content']); ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    <?php foreach ($visibleComments as $comment): ?>
                    <div class="comment">
                        <?php echo renderAvatar($comment['username'], 'sm', avatarUrl($comment['profile_image'], $baseUrl)); ?>
                        <div class="comment-bubble">
                            <p class="comment-author"><?php echo htmlspecialchars($comment['username']); ?></p>
                            <p class="comment-text"><?php echo htmlspecialchars($comment['content']); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <?php if (isset($_SESSION['user_id'])): ?>
                    <form method="POST" action="home.php?page=<?php echo $page; ?>" class="comment-form">
                        <?php echo renderAvatar($currentUser, 'sm', avatarUrl($currentUserImage, $baseUrl)); ?>
                        <textarea id="comment-input-<?php echo $post['id']; ?>" name="comment_content" rows="1" placeholder="Laisse ton commentaire..."></textarea>
                        <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                        <button type="submit" class="btn btn-primary btn-sm">Envoyer</button>
                    </form>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </article>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination-modern">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>" class="pagination-link">
                    <?php echo renderIcon('chevron-left', 16); ?>
                    <span>Précédent</span>
                </a>
            <?php else: ?>
                <span class="pagination-link disabled">
                    <?php echo renderIcon('chevron-left', 16); ?>
                    <span>Précédent</span>
                </span>
            <?php endif; ?>

            <div class="pagination-pages">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php if ($i === $page): ?>
                        <span class="current"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
            </div>

            <?php if ($page < $totalPages): ?>
                <a href="?page=<?php echo $page + 1; ?>" class="pagination-link">
                    <span>Suivant</span>
                    <?php echo renderIcon('chevron-right', 16); ?>
                </a>
            <?php else: ?>
                <span class="pagination-link disabled">
                    <span>Suivant</span>
                    <?php echo renderIcon('chevron-right', 16); ?>
                </span>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </main>

    <?php include __DIR__ . '/../includes/auth-modal.php'; ?>
    <?php include __DIR__ . '/../includes/confirm-modal.php'; ?>

    <script src="<?php echo $baseUrl; ?>scripts/auth-modal.js"></script>
    <script>
    function focusComment(postId) {
        const textarea = document.getElementById('comment-input-' + postId);
        if (!textarea) return;
        textarea.scrollIntoView({ behavior: 'smooth', block: 'center' });
        textarea.focus();
    }

    function toggleComments(postId) {
        const hidden = document.getElementById('comments-hidden-' + postId);
        if (hidden) hidden.classList.toggle('is-visible');
    }

    // Reprend l'action interrompue (liker / commenter) après une connexion via la modale.
    document.addEventListener('DOMContentLoaded', function () {
        const raw = sessionStorage.getItem('networkee_after_login');
        if (!raw) return;
        sessionStorage.removeItem('networkee_after_login');
        const intent = JSON.parse(raw);
        if (!intent.id) return;

        const post = document.getElementById('post-' + intent.id);
        if (post) post.scrollIntoView({ behavior: 'smooth', block: 'center' });

        if (intent.action === 'like') {
            // .click() sur le bouton plutôt que form.requestSubmit() : plus fiable pour
            // déclencher une vraie soumission (avec le couple name/value du bouton) sur tous les navigateurs.
            const likeButton = document.querySelector('#like-form-' + intent.id + ' button[type=submit]');
            if (likeButton) likeButton.click();
        } else if (intent.action === 'comment') {
            focusComment(intent.id);
        } else if (intent.action === 'repost') {
            const repostButton = document.querySelector('#repost-form-' + intent.id + ' button[type=submit]');
            if (repostButton) repostButton.click();
        }
    });
    </script>

    <?php include(__DIR__ . '/../includes/footer.php'); ?>
</body>
</html>
