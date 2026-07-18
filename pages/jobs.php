<?php
$baseUrl    = '../';
$pageTitle  = 'Offres d\'emploi — Networkee';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';
session_start();

$types   = ['CDI', 'CDD', 'Freelance', 'Alternance', 'Stage'];
$success = false;
$error   = null;

// ── Candidature à une offre ─────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_job_id'], $_SESSION['user_id'])) {
    $jobOfferId = (int) $_POST['apply_job_id'];
    $userId     = (int) $_SESSION['user_id'];
    // Stocké brut : applicants.php échappe déjà à l'affichage (un htmlspecialchars ici
    // aurait doublé l'échappement, ex. "m'intéresse" -> "m&#039;intéresse" affiché tel quel).
    $message    = trim($_POST['message'] ?? '');

    $ownerStmt = $pdo->prepare("SELECT user_id FROM job_offers WHERE id = :id");
    $ownerStmt->execute(['id' => $jobOfferId]);
    $offerOwnerId = (int) $ownerStmt->fetchColumn();

    if ($offerOwnerId && $offerOwnerId !== $userId && !hasApplied($jobOfferId, $userId, $pdo)) {
        $stmt = $pdo->prepare(
            "INSERT INTO job_applications (job_offer_id, user_id, message, created_at) VALUES (:job_offer_id, :user_id, :message, NOW())"
        );
        $stmt->execute(['job_offer_id' => $jobOfferId, 'user_id' => $userId, 'message' => $message ?: null]);
        createNotification($offerOwnerId, $userId, 'application', $jobOfferId, $pdo);
    }

    header('Location: jobs.php?applied=1');
    exit;
}

// ── Nouvelle offre ──────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'], $_POST['form_type']) && $_POST['form_type'] === 'create_offer') {
    $title       = htmlspecialchars(trim($_POST['title']       ?? ''), ENT_QUOTES, 'UTF-8');
    $company     = htmlspecialchars(trim($_POST['company']     ?? ''), ENT_QUOTES, 'UTF-8');
    $loc         = htmlspecialchars(trim($_POST['location']    ?? ''), ENT_QUOTES, 'UTF-8');
    $type        = in_array($_POST['type'] ?? '', $types) ? $_POST['type'] : 'CDI';
    $description = htmlspecialchars(trim($_POST['description'] ?? ''), ENT_QUOTES, 'UTF-8');

    if (empty($title) || empty($company) || empty($description)) {
        $error = 'Merci de remplir au moins le titre, l\'entreprise et la description.';
    } else {
        $stmt = $pdo->prepare(
            "INSERT INTO job_offers (user_id, title, company, location, type, description, created_at)
             VALUES (:user_id, :title, :company, :location, :type, :description, NOW())"
        );
        $stmt->execute([
            'user_id'     => $_SESSION['user_id'],
            'title'       => $title,
            'company'     => $company,
            'location'    => $loc,
            'type'        => $type,
            'description' => $description,
        ]);
        header('Location: jobs.php?success=1');
        exit;
    }
}

// ── Filtrage ─────────────────────────────────────────────────────────────────
$filter = $_GET['type'] ?? 'all';
if ($filter !== 'all' && in_array($filter, $types)) {
    $stmt = $pdo->prepare(
        "SELECT jo.*, u.username, u.profile_image
         FROM job_offers jo
         JOIN users u ON jo.user_id = u.id
         WHERE jo.type = :type
         ORDER BY jo.created_at DESC"
    );
    $stmt->execute(['type' => $filter]);
} else {
    $stmt = $pdo->query(
        "SELECT jo.*, u.username, u.profile_image
         FROM job_offers jo
         JOIN users u ON jo.user_id = u.id
         ORDER BY jo.created_at DESC"
    );
}
$offers = $stmt->fetchAll();

include __DIR__ . '/../includes/head.php';
?>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <main class="page-wrapper" style="max-width: 780px;">

        <!-- En-tête page -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
            <div>
                <h1 style="margin: 0; font-size: 1.5rem; font-weight: 450; color:rgba(239, 124, 86, 0.86); letter-spacing: -0.025em;">Offres d'emploi</h1>
                <p style="margin: 0.25rem 0 0; color: var(--text-muted); font-size: 0.9375rem;">Opportunités dans le digital</p>
            </div>
            <?php if (isset($_SESSION['user_id'])): ?>
                <button class="btn btn-primary" onclick="toggleForm()">
                    <img src="<?php echo $baseUrl; ?>icons/icons8-job-seeker-100.png" alt="" width="30" height="30" style="vertical-align: -8px;"> Publier une offre
                </button>
            <?php else: ?>
                <button type="button" class="btn btn-secondary" onclick="openLoginModal('publish')">Se connecter pour publier</button>
            <?php endif; ?>
        </div>

        <!-- Alertes -->
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success" style="margin-bottom: 1rem;">✅ Offre publiée avec succès !</div>
        <?php endif; ?>
        <?php if (isset($_GET['applied'])): ?>
            <div class="alert alert-success" style="margin-bottom: 1rem;">✅ Ta candidature a bien été envoyée !</div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger" style="margin-bottom: 1rem;"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Formulaire nouvelle offre -->
        <?php if (isset($_SESSION['user_id'])): ?>
        <div id="new-offer-form" class="card" style="margin-bottom: 1.5rem; display: <?php echo $error ? 'block' : 'none'; ?>;">
            <div class="card-body">
                <h3 style="margin: 0 0 1.25rem; font-size: 1.125rem;">Nouvelle offre d'emploi</h3>
                <form action="jobs.php" method="post">
                    <input type="hidden" name="form_type" value="create_offer">
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label">Intitulé du poste *</label>
                            <input type="text" name="title" class="form-input" placeholder="Chef de projet digital" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Entreprise *</label>
                            <input type="text" name="company" class="form-input" placeholder="Agence XYZ" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Localisation</label>
                            <input type="text" name="location" class="form-input" placeholder="Paris, 75 / Remote">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Type de contrat</label>
                            <select name="type" class="form-input">
                                <?php foreach ($types as $t): ?>
                                    <option value="<?php echo $t; ?>"><?php echo $t; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Description *</label>
                        <textarea name="description" class="form-input" rows="4"
                            placeholder="Décrivez le poste, les missions, le profil recherché..."
                            style="resize: vertical;" required></textarea>
                    </div>
                    <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 0.5rem;">
                        <button type="button" class="btn btn-secondary" onclick="toggleForm()">Annuler</button>
                        <button type="submit" class="btn btn-primary"><img src="<?php echo $baseUrl; ?>icons/icons8-send-50.png" alt="" width="16" height="16"> Publier</button>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <!-- Filtres -->
        <div class="jobs-filter">
            <a href="jobs.php" class="filter-pill <?php echo ($filter === 'all') ? 'active' : ''; ?>">Tous (<?php echo count($offers); ?>)</a>
            <?php foreach ($types as $t):
                $count = count(array_filter($offers, fn($o) => $o['type'] === $t));
            ?>
                <a href="jobs.php?type=<?php echo $t; ?>"
                   class="filter-pill type-pill-<?php echo strtolower($t); ?> <?php echo ($filter === $t) ? 'active' : ''; ?>">
                    <?php echo $t; ?><?php if ($count): ?> <span class="pill-count"><?php echo $count; ?></span><?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Liste des offres -->
        <?php if (empty($offers)): ?>
            <div class="card" style="text-align: center; padding: 3.5rem 1.5rem; margin-top: 1rem;">
                <div style="font-size: 3.5rem; margin-bottom: 0.75rem;"><img width="75" height="75" src="https://img.icons8.com/plasticine/100/new-job.png" alt="new-job"/></div>
                <p style="color: var(--text-muted); margin: 0 0 1.25rem; font-size: 1rem;">Aucune offre
                    <?php echo ($filter !== 'all') ? 'pour le type "' . htmlspecialchars($filter) . '"' : 'pour le moment'; ?>.
                </p>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <button class="btn btn-primary" onclick="toggleForm()">Sois le premier à publier !</button>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 1rem; margin-top: 1rem;">
                <?php foreach ($offers as $offer): ?>
                <article class="job-card card" id="job-<?php echo (int) $offer['id']; ?>">
                    <div class="card-body">
                        <div style="display: flex; gap: 1rem; align-items: flex-start;">
                            <!-- Logo entreprise placeholder -->
                            <div class="company-logo">
                                <?php echo strtoupper(substr($offer['company'], 0, 2)); ?>
                            </div>
                            <div style="flex: 1; min-width: 0;">
                                <!-- Badges & lieu -->
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

                                <!-- Titre & Entreprise -->
                                <h3 style="margin: 0 0 0.125rem; font-size: 1.0625rem; font-weight: 500; line-height: 1.3;">
                                    <?php echo htmlspecialchars($offer['title']); ?>
                                </h3>
                                <p style="margin: 0 0 0.875rem; font-size: 0.9375rem; color: var(--accent); font-weight: 500;">
                                    <?php echo htmlspecialchars($offer['company']); ?>
                                </p>

                                <!-- Description -->
                                <p style="margin: 0; font-size: 0.875rem; color: var(--text-soft); line-height: 1.65;">
                                    <?php
                                    $desc = htmlspecialchars($offer['description']);
                                    echo (mb_strlen($desc) > 220) ? mb_substr($desc, 0, 220) . '…' : $desc;
                                    ?>
                                </p>
                            </div>
                        </div>

                        <!-- Footer carte -->
                        <div class="job-card-footer">
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <?php
                                $avatarUrl = avatarUrl($offer['profile_image'], $baseUrl);
                                echo renderAvatar($offer['username'], 'sm', $avatarUrl);
                                ?>
                                <span style="font-size: 0.8125rem; font-weight: 500; color: var(--text-soft);">
                                    <?php echo htmlspecialchars($offer['username']); ?>
                                </span>
                            </div>
                            <time style="font-size: 0.75rem; color: var(--text-muted);">
                                <?php echo timeAgo($offer['created_at']); ?>
                            </time>
                        </div>

                        <!-- Candidature -->
                        <?php if (!isset($_SESSION['user_id'])): ?>
                            <div style="margin-top: 0.875rem;">
                                <button type="button" class="btn btn-secondary btn-sm" onclick="openLoginModal('apply', <?php echo (int) $offer['id']; ?>)">Se connecter pour postuler</button>
                            </div>
                        <?php elseif ((int) $offer['user_id'] === (int) $_SESSION['user_id']): ?>
                            <?php $appCount = getApplicationCount((int) $offer['id'], $pdo); ?>
                            <div style="margin-top: 0.875rem;">
                                <span class="hover-stat" style="display: inline-flex;">
                                    <a href="applicants.php?job_id=<?php echo (int) $offer['id']; ?>" class="btn btn-secondary btn-sm">
                                        <?php echo renderIcon('users', 15); ?>
                                        <?php echo $appCount; ?> candidature<?php echo $appCount > 1 ? 's' : ''; ?>
                                    </a>
                                    <div class="hover-popover">
                                        <?php echo renderHoverList(getApplicants((int) $offer['id'], $pdo), 'Aucune candidature pour le moment.', $baseUrl); ?>
                                    </div>
                                </span>
                            </div>
                        <?php elseif (hasApplied((int) $offer['id'], (int) $_SESSION['user_id'], $pdo)): ?>
                            <div style="margin-top: 0.875rem;">
                                <span class="btn btn-secondary btn-sm" style="cursor: default; opacity: 0.75;">Candidature envoyée ✓</span>
                            </div>
                        <?php else: ?>
                            <div style="margin-top: 0.875rem;">
                                <button type="button" class="btn btn-primary btn-sm" onclick="toggleApply(<?php echo (int) $offer['id']; ?>)">
                                    Postuler
                                </button>
                                <form id="apply-form-<?php echo (int) $offer['id']; ?>" method="POST" action="jobs.php" style="display: none; margin-top: 0.75rem;">
                                    <input type="hidden" name="apply_job_id" value="<?php echo (int) $offer['id']; ?>">
                                    <textarea name="message" rows="2" class="form-input" style="resize: vertical; margin-bottom: 0.5rem;"
                                              placeholder="Un message pour accompagner ta candidature (optionnel)"></textarea>
                                    <button type="submit" class="btn btn-primary btn-sm">Envoyer ma candidature</button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <?php include __DIR__ . '/../includes/auth-modal.php'; ?>

    <script src="<?php echo $baseUrl; ?>scripts/auth-modal.js"></script>
    <script>
    // Reprend l'action interrompue (postuler / publier) après une connexion via la modale.
    document.addEventListener('DOMContentLoaded', function () {
        const raw = sessionStorage.getItem('networkee_after_login');
        if (!raw) return;
        sessionStorage.removeItem('networkee_after_login');
        const intent = JSON.parse(raw);

        if (intent.action === 'publish') {
            toggleForm();
        } else if (intent.action === 'apply' && intent.id) {
            const card = document.getElementById('job-' + intent.id);
            if (card) {
                card.scrollIntoView({ behavior: 'smooth', block: 'center' });
                toggleApply(intent.id);
            }
        }
    });

    function toggleForm() {
        const form = document.getElementById('new-offer-form');
        if (!form) return;
        const visible = form.style.display !== 'none';
        form.style.display = visible ? 'none' : 'block';
        if (!visible) form.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function toggleApply(jobId) {
        const form = document.getElementById('apply-form-' + jobId);
        if (!form) return;
        form.style.display = form.style.display !== 'none' ? 'none' : 'block';
    }
    </script>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
