<?php
/**
 * Panneau flottant « Accès rapide », inclus par includes/footer.php et donc présent
 * sur toutes les pages du site.
 *
 * Autonome : il résout lui-même l'avatar de l'utilisateur connecté au lieu de compter
 * sur une variable préparée par la page hôte — sans quoi il faudrait dupliquer cette
 * requête dans chaque page qui l'affiche. Les liens passent tous par $baseUrl, la
 * profondeur variant selon l'emplacement de la page (racine ou pages/).
 *
 * Une page peut s'en passer en posant $hideQuickWidget = true avant le footer : sur
 * les écrans de connexion et d'inscription, le panneau ne ferait que reproposer
 * l'action déjà en cours sur la page.
 */

if (!empty($hideQuickWidget)) {
    return;
}

$widgetBaseUrl = $baseUrl ?? './';
$widgetAvatar  = '';

if (isset($_SESSION['user_id'], $pdo)) {
    try {
        $widgetStmt = $pdo->prepare("SELECT profile_image FROM users WHERE id = :id");
        $widgetStmt->execute(['id' => $_SESSION['user_id']]);
        $widgetImage = $widgetStmt->fetchColumn();
        if (!empty($widgetImage) && $widgetImage !== 'default.png') {
            $widgetAvatar = $widgetBaseUrl . 'uploads/' . $widgetImage;
        }
    } catch (\Exception $e) {
        $widgetAvatar = '';
    }
}

/** Icône monochrome teintée à la couleur d'accent via un masque CSS. */
function widgetIconStyle(string $url): string {
    return "display:inline-block;width:24px;height:24px;vertical-align:middle;"
         . "background-color:var(--accent);"
         . "-webkit-mask:url('{$url}') center / contain no-repeat;"
         . "mask:url('{$url}') center / contain no-repeat;";
}
?>
<div id="quick-widget" class="quick-widget collapsed">
    <button class="widget-tab" onclick="toggleWidget()" aria-label="Ouvrir/fermer le panneau rapide">
        <svg id="widget-icon-open" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
        <svg id="widget-icon-close" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none"><path d="m15 18-6-6 6-6"/></svg>
        <span class="widget-tab-label">Accès rapide</span>
    </button>

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
                <a href="<?php echo $widgetBaseUrl; ?>pages/home.php" class="widget-link"><span role="img" aria-label="rss" style="<?php echo widgetIconStyle('https://img.icons8.com/quill/100/rss.png'); ?>"></span>Le fil</a>
                <a href="<?php echo $widgetBaseUrl; ?>pages/profile.php" class="widget-link"><span role="img" aria-label="conference-call" style="<?php echo widgetIconStyle('https://img.icons8.com/quill/50/conference-call.png'); ?>"></span>Mon profil</a>
                <a href="<?php echo $widgetBaseUrl; ?>pages/jobs.php" class="widget-link"><span role="img" aria-label="teacher-hirring" style="<?php echo widgetIconStyle('https://img.icons8.com/quill/50/teacher-hirring.png'); ?>"></span>Offres d'emploi</a>
                <a href="<?php echo $widgetBaseUrl; ?>pages/saved.php" class="widget-link"><span role="img" aria-label="favoris" style="<?php echo widgetIconStyle($widgetBaseUrl . 'icons/icons8-bookmark-50.png'); ?>"></span>Favoris</a>
            </nav>
            <a href="<?php echo $widgetBaseUrl; ?>pages/logout.php" class="widget-logout">Déconnexion</a>
        <?php else: ?>
            <p class="widget-anon">Tu n'es pas encore connecté !</p>
            <nav class="widget-nav">
                <a href="<?php echo $widgetBaseUrl; ?>pages/login.php" class="widget-link"><span role="img" aria-label="se connecter" style="<?php echo widgetIconStyle('https://img.icons8.com/quill/50/key.png'); ?>"></span>Se connecter</a>
                <a href="<?php echo $widgetBaseUrl; ?>pages/register.php" class="widget-link"><span role="img" aria-label="s'inscrire" style="<?php echo widgetIconStyle($widgetBaseUrl . 'icons/icons8-sign-up-50.png'); ?>"></span>S'inscrire</a>
                <a href="<?php echo $widgetBaseUrl; ?>pages/jobs.php" class="widget-link"><span role="img" aria-label="offres d'emploi" style="<?php echo widgetIconStyle('https://img.icons8.com/quill/50/teacher-hirring.png'); ?>"></span>Offres d'emploi</a>
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
