<?php
$baseUrl = '../';
$pageTitle = 'Candidatures — Networkee';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$jobId = isset($_GET['job_id']) && is_numeric($_GET['job_id']) ? (int) $_GET['job_id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM job_offers WHERE id = :id");
$stmt->execute(['id' => $jobId]);
$offer = $stmt->fetch();

// Offre introuvable, ou candidatures visibles uniquement par son auteur
if (!$offer || (int) $offer['user_id'] !== (int) $_SESSION['user_id']) {
    header('Location: jobs.php');
    exit;
}

$applicants = getApplicants($jobId, $pdo);
?>
<?php include __DIR__ . '/../includes/head.php'; ?>
<body>
    <?php include(__DIR__ . '/../includes/header.php'); ?>

    <main class="page-wrapper" style="max-width: 780px;">
        <p style="margin: 0 0 0.25rem;"><a href="jobs.php" style="color: var(--text-muted); font-size: 0.875rem;">← Retour aux offres</a></p>
        <h2 style="margin: 0 0 0.25rem;">Candidatures</h2>
        <p style="margin: 0 0 1.5rem; color: var(--text-muted);">
            Pour l'offre <strong><?php echo htmlspecialchars($offer['title']); ?></strong> — <?php echo htmlspecialchars($offer['company']); ?>
        </p>

        <?php if (empty($applicants)): ?>
            <div class="card" style="text-align: center; padding: 3rem 1.5rem;">
                <p style="color: var(--text-muted); margin: 0;">Aucune candidature pour le moment.</p>
            </div>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 0.875rem;">
                <?php foreach ($applicants as $a): ?>
                <div class="card">
                    <div class="card-body">
                        <div style="display: flex; align-items: flex-start; gap: 0.875rem;">
                            <a href="profile.php?id=<?php echo (int) $a['user_id']; ?>">
                                <?php echo renderAvatar($a['username'], 'sm', avatarUrl($a['profile_image'], $baseUrl)); ?>
                            </a>
                            <div style="flex: 1; min-width: 0;">
                                <p style="margin: 0;">
                                    <a href="profile.php?id=<?php echo (int) $a['user_id']; ?>" style="font-weight: 600; color: var(--text);">
                                        <?php echo htmlspecialchars($a['username']); ?>
                                    </a>
                                </p>
                                <?php if (!empty($a['job_title'])): ?>
                                    <p style="margin: 0.125rem 0 0; font-size: 0.8125rem; color: var(--text-muted);">
                                        <?php echo htmlspecialchars($a['job_title']); ?>
                                        <?php if (!empty($a['location'])): ?> · <?php echo htmlspecialchars($a['location']); ?><?php endif; ?>
                                    </p>
                                <?php endif; ?>
                                <?php if (!empty($a['message'])): ?>
                                    <p style="margin: 0.75rem 0 0; font-size: 0.875rem; color: var(--text-soft); line-height: 1.6;">
                                        <?php echo nl2br(htmlspecialchars($a['message'])); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                            <time style="font-size: 0.75rem; color: var(--text-muted); white-space: nowrap;">
                                <?php echo timeAgo($a['created_at']); ?>
                            </time>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <?php include(__DIR__ . '/../includes/footer.php'); ?>
</body>
</html>
