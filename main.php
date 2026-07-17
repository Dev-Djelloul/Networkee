<?php
$baseUrl = './';
$pageTitle = 'Networkee — Le réseau des professionnels du digital';
session_start();
require_once __DIR__ . '/config/database.php';

// Stats communauté
try {
    $statUsers  = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $statPosts  = $pdo->query("SELECT COUNT(*) FROM posts")->fetchColumn();
    $statOffers = $pdo->query("SELECT COUNT(*) FROM job_offers")->fetchColumn();
} catch (\Exception $e) {
    $statUsers = $statPosts = $statOffers = '—';
}

// Photo de l'utilisateur connecté (pour le widget d'accès rapide)
$widgetAvatar = '';
if (isset($_SESSION['user_id'])) {
    try {
        $s = $pdo->prepare("SELECT profile_image FROM users WHERE id = :id");
        $s->execute(['id' => $_SESSION['user_id']]);
        $pi = $s->fetchColumn();
        if (!empty($pi) && $pi !== 'default.png') {
            $widgetAvatar = $baseUrl . 'uploads/' . $pi;
        }
    } catch (\Exception $e) {
        $widgetAvatar = '';
    }
}

include __DIR__ . '/includes/head.php';
?>
<body>
    <?php include __DIR__ . '/includes/header.php'; ?>

    <!-- ── HERO ──────────────────────────────────────────────────────────── -->
    <section class="main-hero">
        <div class="hero-bg-grid"></div>

        <div class="hero-inner">
            <!-- Texte gauche -->
            <div class="hero-content">
                <div class="hero-badge">✦ Réseau · Digital · Emploi</div>

                <h1 class="hero-title">
                    Connecte ton talent<br>
                    <span class="hero-accent">au bon recruteur.</span>
                </h1>

                <p class="hero-subtitle">
                    Networkee réunit les professionnels du digital, les chefs de projet,
                    les UX designers et tous les métiers du web dans un seul endroit
                    pour partager, apprendre et trouver des opportunités.
                </p>

                <!-- Stats -->
                <div class="hero-stats">
                    <div class="h-stat">
                        <span class="h-stat-val"><?php echo $statUsers; ?></span>
                        <span class="h-stat-lbl">Membres</span>
                    </div>
                    <div class="h-stat-sep"></div>
                    <div class="h-stat">
                        <span class="h-stat-val"><?php echo $statPosts; ?></span>
                        <span class="h-stat-lbl">Publications</span>
                    </div>
                    <div class="h-stat-sep"></div>
                    <div class="h-stat">
                        <span class="h-stat-val"><?php echo $statOffers; ?></span>
                        <span class="h-stat-lbl">Offres d'emploi</span>
                    </div>
                </div>

                <?php if (!isset($_SESSION['user_id'])): ?>
                <div class="hero-cta">
                    <a href="pages/register.php" class="btn btn-primary btn-lg">Rejoindre gratuitement</a>
                    <a href="pages/login.php"    class="btn btn-ghost btn-lg">Se connecter</a>
                </div>
                <?php else: ?>
                <div class="hero-cta">
                    <a href="pages/home.php" class="btn btn-primary btn-lg">Voir le fil</a>
                    <a href="pages/jobs.php" class="btn btn-ghost btn-lg">Offres d'emploi</a>
                </div>
                <?php endif; ?>
            </div>

            <!-- Illustration CSS réseau -->
            <div class="hero-visual" aria-hidden="true">
                <div class="net-canvas">
                    <!-- Nœuds -->
                    <div class="net-node n1"><span>UX</span></div>
                    <div class="net-node n2"><span>PM</span></div>
                    <div class="net-node n3 main-node"><span>N</span></div>
                    <div class="net-node n4"><span>SEO</span></div>
                    <div class="net-node n5"><span>Dev</span></div>
                    <div class="net-node n6"><span>Data</span></div>
                    <!-- Lignes SVG -->
                    <svg class="net-lines" viewBox="0 0 340 300" fill="none">
                        <line x1="170" y1="150" x2="60"  y2="70"  stroke="currentColor" stroke-width="1.5" opacity="0.3"/>
                        <line x1="170" y1="150" x2="280" y2="70"  stroke="currentColor" stroke-width="1.5" opacity="0.3"/>
                        <line x1="170" y1="150" x2="40"  y2="190" stroke="currentColor" stroke-width="1.5" opacity="0.3"/>
                        <line x1="170" y1="150" x2="300" y2="210" stroke="currentColor" stroke-width="1.5" opacity="0.3"/>
                        <line x1="170" y1="150" x2="90"  y2="260" stroke="currentColor" stroke-width="1.5" opacity="0.3"/>
                        <line x1="170" y1="150" x2="250" y2="260" stroke="currentColor" stroke-width="1.5" opacity="0.3"/>
                        <circle cx="170" cy="150" r="60" stroke="currentColor" stroke-width="1" opacity="0.1" stroke-dasharray="6 4"/>
                        <circle cx="170" cy="150" r="110" stroke="currentColor" stroke-width="1" opacity="0.06" stroke-dasharray="6 4"/>
                    </svg>
                </div>
            </div>
        </div>
    </section>

    <!-- ── FEATURE CARDS ─────────────────────────────────────────────────── -->
    <section class="features-section">
        <div class="features-inner">
            <a href="pages/home.php" class="feature-card">
                <div class="feature-icon"><span role="img" aria-label="rss" style="display:inline-block;width:40px;height:40px;background-color:var(--accent);-webkit-mask:url('https://img.icons8.com/quill/100/rss.png') center / contain no-repeat;mask:url('https://img.icons8.com/quill/100/rss.png') center / contain no-repeat;"></span></div>
                <h3>Le Fil</h3>
                <p>Suis l'actualité de la communauté, commente et partage tes projets.</p>
            </a>
            <a href="pages/profile.php" class="feature-card">
                <div class="feature-icon"><span role="img" aria-label="conference-call" style="display:inline-block;width:40px;height:40px;background-color:var(--accent);-webkit-mask:url('https://img.icons8.com/quill/50/conference-call.png') center / contain no-repeat;mask:url('https://img.icons8.com/quill/50/conference-call.png') center / contain no-repeat;"></span></div>
                <h3>Mon Profil</h3>
                <p>Affiche ton titre, tes compétences et active le badge <em>Open to work</em>.</p>
            </a>
            <a href="pages/jobs.php" class="feature-card">
                <div class="feature-icon"><span role="img" aria-label="teacher-hirring" style="display:inline-block;width:40px;height:40px;background-color:var(--accent);-webkit-mask:url('https://img.icons8.com/quill/50/teacher-hirring.png') center / contain no-repeat;mask:url('https://img.icons8.com/quill/50/teacher-hirring.png') center / contain no-repeat;"></span></div>
                <h3>Offres d'emploi</h3>
                <p>Parcours les CDI, freelance, alternances et stages dans le digital.</p>
            </a>
        </div>
    </section>

    <?php include __DIR__ . '/includes/footer.php'; ?>

    <!-- ── WIDGET FLOTTANT ───────────────────────────────────────────────── -->
    <div id="quick-widget" class="quick-widget collapsed">
        <!-- Tab de bascule -->
        <button class="widget-tab" onclick="toggleWidget()" aria-label="Ouvrir/fermer le panneau rapide">
            <svg id="widget-icon-open" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
            <svg id="widget-icon-close" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none"><path d="m15 18-6-6 6-6"/></svg>
            <span class="widget-tab-label">Accès rapide</span>
        </button>

        <!-- Contenu du widget -->
        <div class="widget-body">
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="widget-greeting">
                    <?php if ($widgetAvatar): ?>
                        <img class="widget-avatar" src="<?php echo htmlspecialchars($widgetAvatar); ?>" alt="<?php echo htmlspecialchars($_SESSION['username']); ?>" style="object-fit:cover;">
                    <?php else: ?>
                        <div class="widget-avatar"><?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?></div>
                    <?php endif; ?>
                    <div>
                        <div class="widget-name"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
                        <div class="widget-status">Connecté ✓</div>
                    </div>
                </div>
                <nav class="widget-nav">
                    <a href="pages/home.php"    class="widget-link"><span role="img" aria-label="rss" style="display:inline-block;width:24px;height:24px;vertical-align:middle;background-color:var(--accent);-webkit-mask:url('https://img.icons8.com/quill/100/rss.png') center / contain no-repeat;mask:url('https://img.icons8.com/quill/100/rss.png') center / contain no-repeat;"></span>Le fil</a>
                    <a href="pages/profile.php" class="widget-link"><span role="img" aria-label="conference-call" style="display:inline-block;width:24px;height:24px;vertical-align:middle;background-color:var(--accent);-webkit-mask:url('https://img.icons8.com/quill/50/conference-call.png') center / contain no-repeat;mask:url('https://img.icons8.com/quill/50/conference-call.png') center / contain no-repeat;"></span>Mon profil</a>
                    <a href="pages/jobs.php"    class="widget-link"><span role="img" aria-label="teacher-hirring" style="display:inline-block;width:24px;height:24px;vertical-align:middle;background-color:var(--accent);-webkit-mask:url('https://img.icons8.com/quill/50/teacher-hirring.png') center / contain no-repeat;mask:url('https://img.icons8.com/quill/50/teacher-hirring.png') center / contain no-repeat;"></span>Offres d'emploi</a>
                    <a href="pages/edit-profile.php" class="widget-link"><span role="img" aria-label="create-new" style="display:inline-block;width:24px;height:24px;vertical-align:middle;background-color:var(--accent);-webkit-mask:url('https://img.icons8.com/quill/50/create-new.png') center / contain no-repeat;mask:url('https://img.icons8.com/quill/50/create-new.png') center / contain no-repeat;"></span>Modifier le profil</a>
                </nav>
                <a href="pages/logout.php" class="widget-logout">Déconnexion</a>
            <?php else: ?>
                <p class="widget-anon">Tu n'es pas connecté.</p>
                <nav class="widget-nav">
                    <a href="pages/login.php"    class="widget-link"><span role="img" aria-label="se connecter" style="display:inline-block;width:24px;height:24px;vertical-align:middle;background-color:var(--accent);-webkit-mask:url('https://img.icons8.com/quill/50/key.png') center / contain no-repeat;mask:url('https://img.icons8.com/quill/50/key.png') center / contain no-repeat;"></span>Se connecter</a>
                    <a href="pages/register.php" class="widget-link"><span role="img" aria-label="s'inscrire" style="display:inline-block;width:24px;height:24px;vertical-align:middle;background-color:var(--accent);-webkit-mask:url('https://img.icons8.com/quill/50/rocket.png') center / contain no-repeat;mask:url('https://img.icons8.com/quill/50/rocket.png') center / contain no-repeat;"></span>S'inscrire</a>
                    <a href="pages/jobs.php"     class="widget-link"><span role="img" aria-label="offres d'emploi" style="display:inline-block;width:24px;height:24px;vertical-align:middle;background-color:var(--accent);-webkit-mask:url('https://img.icons8.com/quill/50/teacher-hirring.png') center / contain no-repeat;mask:url('https://img.icons8.com/quill/50/teacher-hirring.png') center / contain no-repeat;"></span>Offres d'emploi</a>
                </nav>
            <?php endif; ?>
        </div>
    </div>

    <script>
    function toggleWidget() {
        const w = document.getElementById('quick-widget');
        const open  = document.getElementById('widget-icon-open');
        const close = document.getElementById('widget-icon-close');
        const isOpen = !w.classList.contains('collapsed');
        w.classList.toggle('collapsed', isOpen);
        open.style.display  = isOpen ? '' : 'none';
        close.style.display = isOpen ? 'none' : '';
    }
    </script>
</body>
</html>
