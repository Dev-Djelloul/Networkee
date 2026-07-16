<?php
$baseUrl    = '../';
$pageTitle  = 'Offres d\'emploi — Networkee';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';
session_start();

$types   = ['CDI', 'CDD', 'Freelance', 'Alternance', 'Stage'];
$success = false;
$error   = null;

// ── Nouvelle offre ──────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
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
                <h1 style="margin: 0; font-size: 1.5rem; font-weight: 550; color:rgba(239, 124, 86, 0.86); letter-spacing: -0.025em;">Offres d'emploi</h1>
                <p style="margin: 0.25rem 0 0; color: var(--text-muted); font-size: 0.9375rem;">Opportunités dans le digital</p>
            </div>
            <?php if (isset($_SESSION['user_id'])): ?>
                <button class="btn btn-primary" onclick="toggleForm()">
                    <?php echo renderIcon('briefcase', 16); ?> Publier une offre
                </button>
            <?php else: ?>
                <a href="login.php" class="btn btn-secondary">Se connecter pour publier</a>
            <?php endif; ?>
        </div>

        <!-- Alertes -->
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success" style="margin-bottom: 1rem;">✅ Offre publiée avec succès !</div>
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
                        <button type="submit" class="btn btn-primary"><?php echo renderIcon('send', 16); ?> Publier</button>
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
                <div style="font-size: 3.5rem; margin-bottom: 0.75rem;">💼</div>
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
                <article class="job-card card">
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
                                            <?php echo renderIcon('map-pin', 13); ?>
                                            <?php echo htmlspecialchars($offer['location']); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <!-- Titre & Entreprise -->
                                <h3 style="margin: 0 0 0.125rem; font-size: 1.0625rem; font-weight: 700; line-height: 1.3;">
                                    <?php echo htmlspecialchars($offer['title']); ?>
                                </h3>
                                <p style="margin: 0 0 0.875rem; font-size: 0.9375rem; color: var(--accent); font-weight: 600;">
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
                                $avatarUrl = !empty($offer['profile_image']) ? $baseUrl . 'uploads/' . htmlspecialchars($offer['profile_image']) : '';
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
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <script>
    function toggleForm() {
        const form = document.getElementById('new-offer-form');
        if (!form) return;
        const visible = form.style.display !== 'none';
        form.style.display = visible ? 'none' : 'block';
        if (!visible) form.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
    </script>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
