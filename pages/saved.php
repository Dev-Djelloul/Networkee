<?php
$baseUrl   = '../';
$pageTitle = 'Enregistrés — Networkee';
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

// Retirer une offre des enregistrements
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unsave_job'])) {
    $pdo->prepare("DELETE FROM saved_jobs WHERE job_offer_id = :job_offer_id AND user_id = :user_id")
        ->execute(['job_offer_id' => (int) $_POST['unsave_job'], 'user_id' => $userId]);
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

$jobStmt = $pdo->prepare("
    SELECT job_offers.id, job_offers.title, job_offers.company, job_offers.location,
           job_offers.type, job_offers.description, saved_jobs.created_at AS saved_at
    FROM saved_jobs
    JOIN job_offers ON saved_jobs.job_offer_id = job_offers.id
    WHERE saved_jobs.user_id = :user_id
    ORDER BY saved_jobs.created_at DESC
");
$jobStmt->execute(['user_id' => $userId]);
$savedJobs = $jobStmt->fetchAll();

$totalSaved = count($savedPosts) + count($savedJobs);
?>
<?php include __DIR__ . '/../includes/head.php'; ?>
<body>
    <?php include(__DIR__ . '/../includes/header.php'); ?>

    <main class="page-wrapper" style="max-width: 780px;">

        <div style="margin-bottom: 1.5rem;">
            <h1 style="margin: 0; font-size: 1.5rem; font-weight: 450; color:rgba(239, 124, 86, 0.86); letter-spacing: -0.025em;">Enregistrés</h1>
            <p style="margin: 0.25rem 0 0; color: var(--text-muted); font-size: 0.9375rem;">
                <?php echo $totalSaved; ?> élément<?php echo $totalSaved > 1 ? 's' : ''; ?> mis de côté
            </p>
        </div>

        <?php if ($totalSaved === 0): ?>
            <div class="card" style="text-align: center; padding: 3rem 1.5rem;">
                <p style="color: var(--text-muted); margin: 0 0 0.5rem;">Tu n'as encore rien enregistré.</p>
                <p style="color: var(--text-muted); margin: 0; font-size: 0.9375rem;">
                    Depuis le menu « … » d'un post du <a href="home.php">Fil</a> ou d'une <a href="jobs.php">offre d'emploi</a>,
                    choisis « Enregistrer » pour le retrouver ici.
                </p>
            </div>
        <?php endif; ?>

        <?php if (!empty($savedJobs)): ?>
            <h2 style="font-size: 1.0625rem; font-weight: 500; margin: 0 0 0.875rem;">
                Offres d'emploi (<?php echo count($savedJobs); ?>)
            </h2>
            <div style="display: flex; flex-direction: column; gap: 1rem; margin-bottom: 2rem;">
                <?php foreach ($savedJobs as $job): ?>
                <article class="job-card card">
                    <div class="card-body">
                        <div style="display: flex; gap: 1rem; align-items: flex-start;">
                            <div class="company-logo"><?php echo strtoupper(substr($job['company'], 0, 2)); ?></div>
                            <div style="flex: 1; min-width: 0;">
                                <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 0.5rem;">
                                    <span class="job-type-badge type-<?php echo strtolower($job['type']); ?>">
                                        <?php echo htmlspecialchars($job['type']); ?>
                                    </span>
                                    <?php if (!empty($job['location'])): ?>
                                        <span class="job-location">
                                            <img src="<?php echo $baseUrl; ?>icons/icons8-location-50.png" alt="" width="30" height="30">
                                            <?php echo htmlspecialchars($job['location']); ?>
                                        </span>
                                    <?php endif; ?>
                                    <form method="POST" action="saved.php" style="margin-left: auto;">
                                        <input type="hidden" name="unsave_job" value="<?php echo (int) $job['id']; ?>">
                                        <button type="submit" class="post-menu" aria-label="Retirer des enregistrements" title="Retirer des enregistrements">
                                            <img src="<?php echo $baseUrl; ?>icons/icons8-delete-50.png" alt="" width="22" height="22">
                                        </button>
                                    </form>
                                </div>

                                <h3 style="margin: 0 0 0.125rem; font-size: 1.0625rem; font-weight: 500; line-height: 1.3;">
                                    <?php echo htmlspecialchars($job['title']); ?>
                                </h3>
                                <p style="margin: 0 0 0.875rem; font-size: 0.9375rem; color: var(--accent); font-weight: 500;">
                                    <?php echo htmlspecialchars($job['company']); ?>
                                </p>
                                <p style="margin: 0 0 0.875rem; font-size: 0.875rem; color: var(--text-soft); line-height: 1.65;">
                                    <?php
                                    $desc = htmlspecialchars($job['description']);
                                    echo (mb_strlen($desc) > 220) ? mb_substr($desc, 0, 220) . '…' : $desc;
                                    ?>
                                </p>
                                <a href="jobs.php#job-<?php echo (int) $job['id']; ?>" class="btn btn-secondary btn-sm">Voir l'offre</a>
                            </div>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($savedPosts)): ?>
            <?php if (!empty($savedJobs)): ?>
                <h2 style="font-size: 1.0625rem; font-weight: 500; margin: 0 0 0.875rem;">
                    Publications (<?php echo count($savedPosts); ?>)
                </h2>
            <?php endif; ?>
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
