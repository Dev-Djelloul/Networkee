<?php
$baseUrl = '../';
$pageTitle = 'Profil — Networkee';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Profil consulté : ?id=X, sinon le sien
$profileId = isset($_GET['id']) && is_numeric($_GET['id']) ? (int) $_GET['id'] : (int) $_SESSION['user_id'];
$isOwner   = $profileId === (int) $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute(['id' => $profileId]);
$user = $stmt->fetch();

// Utilisateur introuvable → retour au fil
if (!$user) {
    header('Location: home.php');
    exit;
}

// Suppression d'un post (auteur uniquement)
if ($isOwner && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_post'])) {
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
    header('Location: profile.php');
    exit;
}

// Ajouter un post (uniquement sur son propre profil)
if ($isOwner && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content'])) {
    $content = trim($_POST['content']);
    $image = null;
    $video = null;

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $target_dir = __DIR__ . '/../uploads/';

        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['image']['type'], $allowed_types)) {
            $upload_error = "Seules les images JPG, PNG ou GIF sont autorisées.";
        } elseif (!is_dir($target_dir) || !is_writable($target_dir)) {
            $upload_error = "Le dossier d'upload n'est pas accessible en écriture.";
        } else {
            $image = uniqid('post_') . '.' . $ext;
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $target_dir . $image)) {
                $upload_error = "Erreur lors du déplacement de l'image (vérifier les permissions du dossier uploads/).";
                $image = null;
            }
        }
    } elseif (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $upload_error = "Erreur upload (code " . $_FILES['image']['error'] . ").";
    }

    if (isset($_FILES['video']) && $_FILES['video']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['video']['name'], PATHINFO_EXTENSION));
        $allowed = ['mp4' => 'video/mp4', 'webm' => 'video/webm', 'ogg' => 'video/ogg', 'mov' => 'video/quicktime'];
        if (array_key_exists($ext, $allowed) && $_FILES['video']['type'] === $allowed[$ext]) {
            $name = uniqid('post_') . '.' . $ext;
            if (move_uploaded_file($_FILES['video']['tmp_name'], __DIR__ . '/../uploads/' . $name)) {
                $video = $name;
            }
        }
    }

    if ($content !== '' || $image !== null || $video !== null) {
        $stmt = $pdo->prepare("INSERT INTO posts (user_id, content, image, video, created_at) VALUES (:user_id, :content, :image, :video, NOW())");
        $stmt->execute([
            'user_id' => $_SESSION['user_id'],
            'content' => $content,
            'image' => $image,
            'video' => $video
        ]);
        header('Location: profile.php');
        exit;
    }
}

// Suivre / ne plus suivre (uniquement sur le profil d'un autre utilisateur)
if (!$isOwner && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_follow'])) {
    $currentUserId = (int) $_SESSION['user_id'];

    if (isFollowing($currentUserId, $profileId, $pdo)) {
        $stmt = $pdo->prepare("DELETE FROM follows WHERE follower_id = :follower_id AND followed_id = :followed_id");
        $stmt->execute(['follower_id' => $currentUserId, 'followed_id' => $profileId]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO follows (follower_id, followed_id) VALUES (:follower_id, :followed_id)");
        $stmt->execute(['follower_id' => $currentUserId, 'followed_id' => $profileId]);
        createNotification($profileId, $currentUserId, 'follow', null, $pdo);
    }
    header('Location: profile.php?id=' . $profileId);
    exit;
}

$isFollowing    = !$isOwner && isFollowing((int) $_SESSION['user_id'], $profileId, $pdo);
$followerCount  = getFollowerCount($profileId, $pdo);
$followingCount = getFollowingCount($profileId, $pdo);
$followers      = getFollowers($profileId, $pdo);
$followingList  = getFollowingList($profileId, $pdo);

// Likes reçus par ce profil
$likeCountStmt = $pdo->prepare("SELECT COUNT(*) FROM likes l JOIN posts p ON l.post_id = p.id WHERE p.user_id = :user_id");
$likeCountStmt->execute(['user_id' => $profileId]);
$likesReceived = (int) $likeCountStmt->fetchColumn();
$postLikers    = getLikersOfUserPosts($profileId, $pdo);

// Publications du profil
$stmt = $pdo->prepare("SELECT * FROM posts WHERE user_id = :user_id ORDER BY created_at DESC");
$stmt->execute(['user_id' => $profileId]);
$posts = $stmt->fetchAll();

$pageTitle = htmlspecialchars($user['username']) . ' — Networkee';
?>
<?php include __DIR__ . '/../includes/head.php'; ?>
<body>
    <?php include(__DIR__ . '/../includes/header.php'); ?>

    <main class="page-wrapper">
        <div class="profile-header">
            <?php
            $avatarUrl = avatarUrl($user['profile_image'], $baseUrl);
            echo renderAvatar($user['username'], 'lg', $avatarUrl);
            ?>

            <?php if (!empty($user['open_to_work'])): ?>
                <div class="otw-banner" style="margin-top: 0.625rem;"><img src="<?php echo $baseUrl; ?>icons/icons8-open-to-work.gif" alt="" width="18" height="18" style="vertical-align: -4px; border-radius: 50%;"> Open to work</div>
            <?php endif; ?>

            <h2><?php echo htmlspecialchars($user['username']); ?></h2>

            <?php if ($isOwner): ?>
                <p style="margin: 0.125rem 0 0; font-size: 0.8125rem; color: rgba(239, 124, 86, 0.86);">
                    <?php echo htmlspecialchars($user['email']); ?>
                </p>
            <?php endif; ?>

            <?php if (!empty($user['job_title'])): ?>
                <p class="profile-job-title"><img src="<?php echo $baseUrl; ?>icons/icons8-job-seeker-100.png" alt="" width="35" height="35" style="vertical-align: -4px;"> <?php echo htmlspecialchars($user['job_title']); ?></p>
            <?php endif; ?>

            <?php if (!empty($user['location'])): ?>
                <p class="profile-location"><img src="<?php echo $baseUrl; ?>icons/icons8-location-50.png" alt="" width="35" height="35" style="vertical-align: -4px;"> <?php echo htmlspecialchars($user['location']); ?></p>
            <?php endif; ?>

            <?php if (!empty($user['bio'])): ?>
                <p class="profile-bio"><?php echo htmlspecialchars($user['bio']); ?></p>
            <?php endif; ?>

            <?php if (!empty($user['skills'])): ?>
                <?php echo renderSkillTags($user['skills']); ?>
            <?php endif; ?>

            <?php if ($isOwner): ?>
                <a href="edit-profile.php" class="btn btn-secondary btn-sm" style="margin-top: 1rem;">Modifier le profil</a>
            <?php else: ?>
                <form method="POST" action="profile.php?id=<?php echo $profileId; ?>" style="margin-top: 1rem;">
                    <input type="hidden" name="toggle_follow" value="1">
                    <button type="submit" class="btn <?php echo $isFollowing ? 'btn-secondary' : 'btn-primary'; ?> btn-sm">
                        <?php echo $isFollowing ? 'Abonné ✓' : '+ Suivre'; ?>
                    </button>
                </form>
            <?php endif; ?>

            <div class="profile-stats">
                <div class="stat">
                    <div class="stat-value"><?php echo count($posts); ?></div>
                    <div class="stat-label">Posts</div>
                </div>
                <div class="stat hover-stat">
                    <div class="stat-value"><?php echo $likesReceived; ?></div>
                    <div class="stat-label">Likes reçus</div>
                    <div class="hover-popover">
                        <?php echo renderHoverList($postLikers, 'Aucun like reçu pour le moment.', $baseUrl); ?>
                    </div>
                </div>
                <div class="stat hover-stat">
                    <div class="stat-value"><?php echo $followerCount; ?></div>
                    <div class="stat-label">Abonnés</div>
                    <div class="hover-popover">
                        <?php echo renderHoverList($followers, 'Aucun abonné pour le moment.', $baseUrl); ?>
                    </div>
                </div>
                <div class="stat hover-stat">
                    <div class="stat-value"><?php echo $followingCount; ?></div>
                    <div class="stat-label">Abonnements</div>
                    <div class="hover-popover">
                        <?php echo renderHoverList($followingList, 'Ne suit personne pour le moment.', $baseUrl); ?>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($isOwner): ?>
        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="card-body">
                <h3 style="margin-top: 0; margin-bottom: 1rem; font-size: 1.125rem;">Nouvelle publication</h3>
                <form action="profile.php" method="post" enctype="multipart/form-data" class="composer-widget">
                    <textarea name="content" rows="3" placeholder="Quoi de neuf ?"></textarea>

                    <div class="composer-media-preview" hidden>
                        <img class="composer-preview-img" alt="Aperçu de l'image" hidden>
                        <video class="composer-preview-video" controls hidden></video>
                        <button type="button" class="composer-media-remove" aria-label="Retirer le média">✕</button>
                        <div class="composer-media-label"></div>
                    </div>
                    <p class="composer-media-error" hidden></p>

                    <div class="composer-actions">
                        <div class="composer-tools">
                            <div class="composer-emoji-wrapper">
                                <button type="button" class="icon-btn composer-emoji-btn" title="Ajouter un emoji">
                                    <img src="<?php echo $baseUrl; ?>icons/icons8-smiley-50.png" alt="Emoji" width="28" height="28">
                                </button>
                                <div class="emoji-picker"></div>
                            </div>

                            <button type="button" class="icon-btn composer-image-btn" title="Ajouter une image">
                                <img src="<?php echo $baseUrl; ?>icons/icons8-picture-50.png" alt="Image" width="28" height="28">
                            </button>
                            <input type="file" class="composer-image-input" name="image" accept="image/jpeg,image/png,image/gif" hidden>

                            <button type="button" class="icon-btn composer-video-btn" title="Ajouter une vidéo">
                                <img src="<?php echo $baseUrl; ?>icons/icons8-video-50.png" alt="Vidéo" width="28" height="28">
                            </button>
                            <input type="file" class="composer-video-input" name="video" accept="video/mp4,video/webm,video/ogg,video/quicktime" hidden>
                        </div>
                        <button type="submit" class="btn btn-primary"><span>Publier</span><img src="<?php echo $baseUrl; ?>icons/icons8-send-50.png" alt="" width="16" height="16"></button>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <h3 style="margin-bottom: 1rem; font-size: 1.125rem; color: var(--text-soft);">
            <?php echo $isOwner ? 'Tes publications' : 'Publications de ' . htmlspecialchars($user['username']); ?>
        </h3>
        <div class="feed">
            <?php $profileAvatar = avatarUrl($user['profile_image'], $baseUrl); ?>
            <?php foreach ($posts as $post): ?>
            <article class="post" id="post-<?php echo $post['id']; ?>">
                <div class="post-header">
                    <div class="post-author">
                        <?php echo renderAvatar($user['username'], '', $profileAvatar); ?>
                        <div class="post-meta">
                            <h3><a href="profile.php?id=<?php echo (int) $user['id']; ?>"><?php echo htmlspecialchars($user['username']); ?></a></h3>
                            <time><?php echo timeAgo($post['created_at']); ?></time>
                        </div>
                    </div>
                    <div class="post-menu-wrapper">
                        <button type="button" class="post-menu" aria-label="Options" onclick="togglePostMenu(this)">
                            <?php echo renderIcon('more', 20); ?>
                        </button>
                        <div class="post-menu-dropdown">
                            <button type="button" class="post-menu-item" onclick="copyPostLink(<?php echo $post['id']; ?>)">
                                <img src="<?php echo $baseUrl; ?>icons/icons8-link-50.png" alt="" width="30" height="30"> Copier le lien
                            </button>
                            <?php if ($isOwner): ?>
                                <form method="POST" action="profile.php" class="confirm-form" data-confirm-message="Supprimer définitivement cette publication ? Cette action est irréversible.">
                                    <input type="hidden" name="delete_post" value="<?php echo $post['id']; ?>">
                                    <button type="submit" class="post-menu-item post-menu-item-danger">
                                        <img src="<?php echo $baseUrl; ?>icons/icons8-delete-50.png" alt="" width="30" height="30"> Supprimer
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="post-content" style="padding: 1.25rem;">
                    <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                </div>
                <?php if ($post['image']): ?>
                    <img src="<?php echo htmlspecialchars(postMediaUrl($post['image'], $baseUrl)); ?>" alt="Image du post" class="post-image">
                <?php elseif (!empty($post['video'])): ?>
                    <video src="<?php echo $baseUrl; ?>uploads/<?php echo htmlspecialchars($post['video']); ?>" class="post-image" controls></video>
                <?php endif; ?>
                <div class="post-actions">
                    <span class="action-btn hover-stat">
                        <img src="<?php echo $baseUrl; ?>icons/icons8-like-heart-50.png" alt="" width="30" height="30">
                        <span><?php echo getLikeCount($post['id'], $pdo); ?></span>
                        <div class="hover-popover">
                            <?php echo renderHoverList(getPostLikers((int) $post['id'], $pdo), 'Aucun like pour le moment.', $baseUrl); ?>
                        </div>
                    </span>
                    <span class="action-btn">
                        <img src="<?php echo $baseUrl; ?>icons/icons8-comment-50.png" alt="" width="30" height="30">
                        <span><?php echo count(getComments($post['id'], $pdo)); ?></span>
                    </span>
                    <span class="action-btn" style="margin-left: auto; font-size: 0.75rem; color: var(--text-muted);">
                        <?php echo date('d/m/Y H:i', strtotime($post['created_at'])); ?>
                    </span>
                </div>
            </article>
            <?php endforeach; ?>

            <?php if (count($posts) === 0): ?>
                <div class="card" style="text-align: center; padding: 3rem 1.5rem;">
                    <?php if ($isOwner): ?>
                        <p style="color: var(--text-muted); margin-bottom: 1rem;">Tu n'as pas encore publié. C'est le moment ! 🌟</p>
                        <a href="home.php" class="btn btn-primary">Découvrir le fil</a>
                    <?php else: ?>
                        <p style="color: var(--text-muted); margin: 0;"><?php echo htmlspecialchars($user['username']); ?> n'a pas encore publié.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include __DIR__ . '/../includes/confirm-modal.php'; ?>

    <?php include(__DIR__ . '/../includes/footer.php'); ?>
</body>
</html>
