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

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute(['id' => $_SESSION['user_id']]);
$user = $stmt->fetch();

 $postCountStmt = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE user_id = :user_id");
$postCountStmt->execute(['user_id' => $_SESSION['user_id']]);
$likeCountStmt = $pdo->prepare("SELECT COUNT(*) FROM likes l JOIN posts p ON l.post_id = p.id WHERE p.user_id = :user_id");
$likeCountStmt->execute(['user_id' => $_SESSION['user_id']]);

// Ajouter un post
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content'])) {
    $content = htmlspecialchars($_POST['content'], ENT_QUOTES, 'UTF-8');
    $image = null;

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image = basename($_FILES['image']['name']);
        $target_dir = __DIR__ . '/../uploads/';
        $target_file = $target_dir . $image;

        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['image']['type'], $allowed_types)) {
            $upload_error = "Seules les images JPG, PNG ou GIF sont autorisées.";
        } elseif (!is_dir($target_dir) || !is_writable($target_dir)) {
            $upload_error = "Le dossier d'upload n'est pas accessible en écriture.";
        } elseif (!move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $upload_error = "Erreur lors du déplacement de l'image (vérifier les permissions du dossier uploads/).";
            $image = null;
        }
    } elseif (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $upload_error = "Erreur upload (code " . $_FILES['image']['error'] . ").";
    }

    if (!empty($content)) {
        $stmt = $pdo->prepare("INSERT INTO posts (user_id, content, image, created_at) VALUES (:user_id, :content, :image, NOW())");
        $stmt->execute([
            'user_id' => $_SESSION['user_id'],
            'content' => $content,
            'image' => $image
        ]);
        header('Location: profile.php');
        exit;
    }
}

$stmt = $pdo->prepare("SELECT * FROM posts WHERE user_id = :user_id ORDER BY created_at DESC");
$stmt->execute(['user_id' => $_SESSION['user_id']]);
$posts = $stmt->fetchAll();
?>
<?php include __DIR__ . '/../includes/head.php'; ?>
<body>
    <?php include(__DIR__ . '/../includes/header.php'); ?>

    <main class="page-wrapper">
        <div class="profile-header">
            <?php
            $avatarUrl = !empty($user['profile_image']) ? $baseUrl . 'uploads/' . htmlspecialchars($user['profile_image']) : '';
            echo renderAvatar($user['username'], 'lg', $avatarUrl);
            ?>
            <h2><?php echo htmlspecialchars($user['username']); ?></h2>
            <?php if (!empty($user['bio'])): ?>
                <p class="profile-bio"><?php echo htmlspecialchars($user['bio']); ?></p>
            <?php endif; ?>
            <a href="edit-profile.php" class="btn btn-secondary btn-sm">Modifier le profil</a>

            <div class="profile-stats">
                <div class="stat">
                    <div class="stat-value"><?php echo count($posts); ?></div>
                    <div class="stat-label">Posts</div>
                </div>
                <div class="stat">
                    <div class="stat-value"><?php echo $likeCountStmt->fetchColumn(); ?></div>
                    <div class="stat-label">Likes reçus</div>
                </div>
            </div>
        </div>

        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="card-body">
                <h3 style="margin-top: 0; margin-bottom: 1rem; font-size: 1.125rem;">Nouvelle publication</h3>
                <form action="profile.php" method="post" enctype="multipart/form-data">
                    <textarea name="content" rows="3" placeholder="Quoi de neuf ?" class="form-input" style="resize: vertical; margin-bottom: 0.75rem;"></textarea>
                    <div class="composer-actions">
                        <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('profile-post-image').click()">📎 Ajouter une image</button>
                            <input type="file" id="profile-post-image" name="image" accept="image/jpeg,image/png,image/gif" style="display: none;" onchange="document.getElementById('profile-post-label').textContent = this.files[0] ? this.files[0].name : ''">
                            <span id="profile-post-label" style="font-size: 0.8125rem; color: var(--text-muted);"></span>
                        </div>
                        <button type="submit" class="btn btn-primary">Publier</button>
                    </div>
                </form>
            </div>
        </div>

        <h3 style="margin-bottom: 1rem; font-size: 1.125rem; color: var(--text-soft);">Tes publications</h3>
        <div class="feed">
            <?php foreach ($posts as $post): ?>
            <article class="post">
                <div class="post-content" style="padding: 1.25rem;">
                    <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                </div>
                <?php if ($post['image']): ?>
                    <img src="<?php echo $baseUrl; ?>uploads/<?php echo htmlspecialchars($post['image']); ?>" alt="Image du post" class="post-image">
                <?php endif; ?>
                <div class="post-actions">
                    <span class="action-btn">
                        <?php echo renderIcon('heart', 20); ?>
                        <span><?php echo getLikeCount($post['id'], $pdo); ?></span>
                    </span>
                    <span class="action-btn">
                        <?php echo renderIcon('message', 20); ?>
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
                    <p style="color: var(--text-muted); margin-bottom: 1rem;">Tu n'as pas encore publié. C'est le moment ! 🌟</p>
                    <a href="home.php" class="btn btn-primary">Découvrir le fil</a>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include(__DIR__ . '/../includes/footer.php'); ?>
</body>
</html>
