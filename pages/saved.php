<?php
$baseUrl   = '../';
$pageTitle = 'Posts enregistrés — Networkee';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = (int) $_SESSION['user_id'];

// Retirer un post des enregistrements depuis cette page
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unsave'])) {
    $pdo->prepare("DELETE FROM saved_posts WHERE post_id = :post_id AND user_id = :user_id")
        ->execute(['post_id' => (int) $_POST['unsave'], 'user_id' => $userId]);
    header('Location: saved.php');
    exit;
}

// Trié par date d'enregistrement (et non de publication) : le dernier enregistré en haut.
$stmt = $pdo->prepare("
    SELECT posts.id, posts.user_id, posts.content, posts.image, posts.video, posts.created_at,
           users.username, users.profile_image, saved_posts.created_at AS saved_at
    FROM saved_posts
    JOIN posts ON saved_posts.post_id = posts.id
    JOIN users ON posts.user_id = users.id
    WHERE saved_posts.user_id = :user_id
    ORDER BY saved_posts.created_at DESC
");
$stmt->execute(['user_id' => $userId]);
$savedPosts = $stmt->fetchAll();
?>
<?php include __DIR__ . '/../includes/head.php'; ?>
<body>
    <?php include(__DIR__ . '/../includes/header.php'); ?>

    <main class="page-wrapper" style="max-width: 780px;">

        <div style="margin-bottom: 1.5rem;">
            <h1 style="margin: 0; font-size: 1.5rem; font-weight: 450; color:rgba(239, 124, 86, 0.86); letter-spacing: -0.025em;">Posts enregistrés</h1>
            <p style="margin: 0.25rem 0 0; color: var(--text-muted); font-size: 0.9375rem;">
                <?php echo count($savedPosts); ?> publication<?php echo count($savedPosts) > 1 ? 's' : ''; ?> mise<?php echo count($savedPosts) > 1 ? 's' : ''; ?> de côté
            </p>
        </div>

        <?php if (empty($savedPosts)): ?>
            <div class="card" style="text-align: center; padding: 3rem 1.5rem;">
                <p style="color: var(--text-muted); margin: 0 0 0.5rem;">Tu n'as encore enregistré aucune publication.</p>
                <p style="color: var(--text-muted); margin: 0; font-size: 0.9375rem;">
                    Depuis le menu « … » d'un post du <a href="home.php">Fil</a>, choisis « Enregistrer le post » pour le retrouver ici.
                </p>
            </div>
        <?php else: ?>
            <div class="feed">
                <?php foreach ($savedPosts as $post): ?>
                <article class="post">
                    <div class="post-header">
                        <div class="post-author">
                            <?php echo renderAvatar($post['username'], '', avatarUrl($post['profile_image'], $baseUrl)); ?>
                            <div class="post-meta">
                                <h3>
                                    <a href="profile.php?id=<?php echo (int) $post['user_id']; ?>">
                                        <?php echo htmlspecialchars($post['username']); ?>
                                    </a>
                                </h3>
                                <time>Enregistré <?php echo timeAgo($post['saved_at']); ?></time>
                            </div>
                        </div>
                        <form method="POST" action="saved.php">
                            <input type="hidden" name="unsave" value="<?php echo (int) $post['id']; ?>">
                            <button type="submit" class="post-menu" aria-label="Retirer des enregistrements" title="Retirer des enregistrements">
                                <img src="<?php echo $baseUrl; ?>icons/icons8-delete-50.png" alt="" width="22" height="22">
                            </button>
                        </form>
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
                        <a class="action-btn" href="home.php#post-<?php echo (int) $post['id']; ?>">
                            <img src="<?php echo $baseUrl; ?>icons/icons8-comment-50.png" alt="" width="30" height="30">
                            <span>Voir dans le fil</span>
                        </a>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <?php include(__DIR__ . '/../includes/footer.php'); ?>
</body>
</html>
