<?php
$baseUrl = '../';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';
session_start();

$postsPerPage = 5;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $postsPerPage;

$stmt = $pdo->prepare("
    SELECT posts.*, users.username, users.id AS user_id
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

function getLikeCount($postId, $pdo) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE post_id = :post_id");
    $stmt->execute(['post_id' => $postId]);
    return $stmt->fetchColumn();
}

function getComments($postId, $pdo) {
    $stmt = $pdo->prepare("
        SELECT comments.*, users.username
        FROM comments
        JOIN users ON comments.user_id = users.id
        WHERE post_id = :post_id
        ORDER BY created_at ASC
    ");
    $stmt->execute(['post_id' => $postId]);
    return $stmt->fetchAll();
}

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
    } else {
        $stmt = $pdo->prepare("INSERT INTO likes (post_id, user_id) VALUES (:post_id, :user_id)");
    }
    $stmt->execute(['post_id' => $postId, 'user_id' => $userId]);
    header("Location: home.php?page=$page");
    exit;
}

$currentUser = $_SESSION['username'] ?? 'Invité';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Le Fil — Networkee</title>
</head>
<body>
    <?php include(__DIR__ . '/../includes/header.php'); ?>

    <main class="page-wrapper">
        <!-- Composer -->
        <?php if (isset($_SESSION['user_id'])): ?>
        <div class="card composer">
            <div class="card-body">
                <div class="composer-row">
                    <?php echo renderAvatar($currentUser); ?>
                    <div class="composer-main">
                        <form action="profile.php" method="post" enctype="multipart/form-data">
                            <textarea name="content" rows="2" placeholder="Quoi de neuf aujourd'hui ?"></textarea>
                            <div class="composer-actions">
                                <div class="composer-tools">
                                    <label class="icon-btn" title="Ajouter une image">
                                        <?php echo renderIcon('image', 20); ?>
                                        <input type="file" name="image" style="display: none;">
                                    </label>
                                    <button type="button" class="icon-btn" title="Emoji">
                                        <?php echo renderIcon('smile', 20); ?>
                                    </button>
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
                        <?php echo renderAvatar($post['username']); ?>
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
                        <?php echo renderAvatar($comment['username'], 'sm'); ?>
                        <div class="comment-bubble">
                            <p class="comment-author"><?php echo htmlspecialchars($comment['username']); ?></p>
                            <p class="comment-text"><?php echo htmlspecialchars($comment['content']); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <?php if (isset($_SESSION['user_id'])): ?>
                    <form method="POST" action="home.php?page=<?php echo $page; ?>" class="comment-form">
                        <?php echo renderAvatar($currentUser, 'sm'); ?>
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
