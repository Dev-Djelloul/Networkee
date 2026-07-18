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
    SELECT posts.*, users.username, users.id AS user_id, users.profile_image
    FROM posts
    JOIN users ON posts.user_id = users.id
    ORDER BY created_at DESC
    LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':limit', $postsPerPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$posts = $stmt->fetchAll();

$totalPosts = $pdo->query("SELECT COUNT(*) FROM posts")->fetchColumn();
$totalPages = max(1, ceil($totalPosts / $postsPerPage));

// Ajout de commentaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_content'], $_POST['post_id']) && isset($_SESSION['user_id'])) {
    $comment_content = htmlspecialchars($_POST['comment_content'], ENT_QUOTES, 'UTF-8');
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

// Nouveau post depuis le fil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content'], $_SESSION['user_id']) && !isset($_POST['comment_content']) && !isset($_POST['like'])) {
    $content = htmlspecialchars(trim($_POST['content']), ENT_QUOTES, 'UTF-8');
    $image = null;

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

    // Un post est valide s'il contient du texte OU une image
    if ($content !== '' || $image !== null) {
        $stmt = $pdo->prepare("INSERT INTO posts (user_id, content, image, created_at) VALUES (:user_id, :content, :image, NOW())");
        $stmt->execute(['user_id' => $_SESSION['user_id'], 'content' => $content, 'image' => $image]);
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
                        <form action="home.php" method="post" enctype="multipart/form-data">
                            <textarea name="content" rows="2" placeholder="Quoi de neuf aujourd'hui ?"></textarea>
                            <div class="composer-actions">
                                <div class="composer-tools">
                                    <button type="button" class="icon-btn" title="Ajouter une image" onclick="document.getElementById('home-image-input').click()">
                                        <?php echo renderIcon('image', 20); ?>
                                    </button>
                                    <input type="file" id="home-image-input" name="image" accept="image/jpeg,image/png,image/gif" style="display:none;" onchange="var l=document.getElementById('home-image-label');l.textContent=this.files[0]?this.files[0].name:''">
                                    <span id="home-image-label" style="font-size:0.75rem;color:var(--text-muted);max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"></span>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <span>Publier</span>
                                    <?php echo renderIcon('send', 16); ?>
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
            <article class="post">
                <div class="post-header">
                    <div class="post-author">
                        <?php echo renderAvatar($post['username'], '', avatarUrl($post['profile_image'], $baseUrl)); ?>
                        <div class="post-meta">
                            <h3><a href="profile.php?id=<?php echo $post['user_id']; ?>"><?php echo htmlspecialchars($post['username']); ?></a></h3>
                            <time><?php echo timeAgo($post['created_at']); ?></time>
                        </div>
                    </div>
                    <button class="post-menu" aria-label="Options">
                        <?php echo renderIcon('more', 20); ?>
                    </button>
                </div>

                <div class="post-content">
                    <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                </div>

                <?php if ($post['image']): ?>
                    <img src="<?php echo $baseUrl; ?>uploads/<?php echo htmlspecialchars($post['image']); ?>" alt="Image du post" class="post-image">
                <?php endif; ?>

                <div class="post-actions">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <form method="POST" action="home.php?page=<?php echo $page; ?>" style="display: inline;">
                            <button type="submit" name="like" value="<?php echo $post['id']; ?>" class="action-btn <?php echo $userLiked ? 'active' : ''; ?>">
                                <?php echo renderIcon('heart', 20); ?>
                                <span><?php echo $likeCount; ?></span>
                            </button>
                        </form>
                    <?php else: ?>
                        <span class="action-btn">
                            <?php echo renderIcon('heart', 20); ?>
                            <span><?php echo $likeCount; ?></span>
                        </span>
                    <?php endif; ?>
                    <span class="action-btn">
                        <?php echo renderIcon('message', 20); ?>
                        <span><?php echo count($comments); ?></span>
                    </span>
                </div>

                <?php if (count($comments) > 0 || isset($_SESSION['user_id'])): ?>
                <div class="comments-section">
                    <?php foreach ($comments as $comment): ?>
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
                        <textarea name="comment_content" rows="1" placeholder="Laisse ton commentaire..."></textarea>
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

    <?php include(__DIR__ . '/../includes/footer.php'); ?>
</body>
</html>
