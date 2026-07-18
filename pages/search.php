<?php
$baseUrl = '../';
$pageTitle = 'Recherche — Networkee';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';
session_start();

$q = trim($_GET['q'] ?? '');
$users = [];
$offers = [];

// Filtrage côté PHP (plutôt que LIKE en SQL) : le résultat doit être identique
// en local (MySQL) et en production (PostgreSQL/Railway), et insensible à la
// casse ET aux accents — deux points sur lesquels LIKE se comporte
// différemment selon le moteur. Le volume de données de l'app reste faible,
// donc récupérer puis filtrer en PHP est largement assez performant.
if ($q !== '') {
    $normalizedQuery = normalizeSearchText($q);

    $allUsers = $pdo->query(
        "SELECT id, username, profile_image, job_title, location, skills, open_to_work FROM users"
    )->fetchAll();
    foreach ($allUsers as $u) {
        $haystack = normalizeSearchText(implode(' ', [
            $u['username'], $u['job_title'] ?? '', $u['skills'] ?? '', $u['location'] ?? '',
        ]));
        if (searchTextMatches($haystack, $normalizedQuery)) {
            $users[] = $u;
        }
    }
    usort($users, fn($a, $b) => strcasecmp($a['username'], $b['username']));

    $allOffers = $pdo->query(
        "SELECT jo.*, u.username, u.profile_image
         FROM job_offers jo
         JOIN users u ON jo.user_id = u.id
         ORDER BY jo.created_at DESC"
    )->fetchAll();
    foreach ($allOffers as $offer) {
        $haystack = normalizeSearchText(implode(' ', [
            $offer['title'], $offer['company'], $offer['description'], $offer['location'] ?? '',
        ]));
        if (searchTextMatches($haystack, $normalizedQuery)) {
            $offers[] = $offer;
        }
    }
}

$hasResults = !empty($users) || !empty($offers);
?>
<?php include __DIR__ . '/../includes/head.php'; ?>
<body>
    <?php include(__DIR__ . '/../includes/header.php'); ?>

    <main class="page-wrapper" style="max-width: 780px;">
        <h2 style="margin-bottom: 1.25rem;">Rechercher</h2>

        <form method="GET" action="search.php" class="search-form">
            <?php echo renderIcon('search', 18); ?>
            <input type="text" name="q" class="form-input" placeholder="Utilisateurs, compétences, offres, entreprises..."
                   value="<?php echo htmlspecialchars($q); ?>" autofocus>
            <button type="submit" class="btn btn-primary">Rechercher</button>
        </form>

        <?php if ($q === ''): ?>
            <p style="color: var(--text-muted); margin-top: 1.5rem;">Tape un nom, un métier, une compétence ou une entreprise pour commencer.</p>

        <?php elseif (!$hasResults): ?>
            <div class="card" style="text-align: center; padding: 3rem 1.5rem; margin-top: 1.5rem;">
                <p style="color: var(--text-muted); margin: 0;">Aucun résultat pour « <?php echo htmlspecialchars($q); ?> ».</p>
            </div>

        <?php else: ?>

            <?php if (!empty($users)): ?>
            <h3 style="margin: 1.75rem 0 1rem; font-size: 1.0625rem; color: var(--text-soft);">
                Personnes (<?php echo count($users); ?>)
            </h3>
            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                <?php foreach ($users as $u): ?>
                <a href="profile.php?id=<?php echo (int) $u['id']; ?>" class="card search-result-card">
                    <div class="card-body" style="display: flex; align-items: center; gap: 0.875rem;">
                        <?php echo renderAvatar($u['username'], 'sm', avatarUrl($u['profile_image'], $baseUrl), !empty($u['open_to_work'])); ?>
                        <div style="flex: 1; min-width: 0;">
                            <p style="margin: 0; font-weight: 500; color: var(--text);">
                                <?php echo htmlspecialchars($u['username']); ?>
                            </p>
                            <?php if (!empty($u['job_title'])): ?>
                                <p style="margin: 0.125rem 0 0; font-size: 0.8125rem; color: var(--text-muted);">
                                    <?php echo htmlspecialchars($u['job_title']); ?>
                                    <?php if (!empty($u['location'])): ?> · <?php echo htmlspecialchars($u['location']); ?><?php endif; ?>
                                </p>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($u['open_to_work'])): ?>
                            <span class="otw-banner" style="margin: 0; font-size: 0.75rem;"><img src="<?php echo $baseUrl; ?>icons/icons8-open-to-work.gif" alt="" width="16" height="16" style="vertical-align: -3px; border-radius: 50%;"> Open to work</span>
                        <?php endif; ?>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($offers)): ?>
            <h3 style="margin: 1.75rem 0 1rem; font-size: 1.0625rem; color: var(--text-soft);">
                Offres d'emploi (<?php echo count($offers); ?>)
            </h3>
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <?php foreach ($offers as $offer): ?>
                <article class="job-card card">
                    <div class="card-body">
                        <div style="display: flex; gap: 1rem; align-items: flex-start;">
                            <div class="company-logo">
                                <?php echo strtoupper(substr($offer['company'], 0, 2)); ?>
                            </div>
                            <div style="flex: 1; min-width: 0;">
                                <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 0.5rem;">
                                    <span class="job-type-badge type-<?php echo strtolower($offer['type']); ?>">
                                        <?php echo htmlspecialchars($offer['type']); ?>
                                    </span>
                                    <?php if (!empty($offer['location'])): ?>
                                        <span class="job-location">
                                            <img src="<?php echo $baseUrl; ?>icons/icons8-location-64.png" alt="" width="30" height="30">
                                            <?php echo htmlspecialchars($offer['location']); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <h3 style="margin: 0 0 0.125rem; font-size: 1.0625rem; font-weight: 500; line-height: 1.3;">
                                    <?php echo htmlspecialchars($offer['title']); ?>
                                </h3>
                                <p style="margin: 0 0 0.875rem; font-size: 0.9375rem; color: var(--accent); font-weight: 500;">
                                    <?php echo htmlspecialchars($offer['company']); ?>
                                </p>
                                <p style="margin: 0; font-size: 0.875rem; color: var(--text-soft); line-height: 1.65;">
                                    <?php
                                    $desc = htmlspecialchars($offer['description']);
                                    echo (mb_strlen($desc) > 220) ? mb_substr($desc, 0, 220) . '…' : $desc;
                                    ?>
                                </p>
                            </div>
                        </div>
                        <div class="job-card-footer">
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <?php echo renderAvatar($offer['username'], 'sm', avatarUrl($offer['profile_image'], $baseUrl)); ?>
                                <span style="font-size: 0.8125rem; font-weight: 500; color: var(--text-soft);">
                                    <?php echo htmlspecialchars($offer['username']); ?>
                                </span>
                            </div>
                            <time style="font-size: 0.75rem; color: var(--text-muted);">
                                <?php echo timeAgo($offer['created_at']); ?>
                            </time>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

        <?php endif; ?>
    </main>

    <?php include(__DIR__ . '/../includes/footer.php'); ?>
</body>
</html>
