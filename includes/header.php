<?php
/**
 * Barre de navigation principale.
 * À inclure dans le <body> de chaque page, après includes/head.php.
 */
?>
<nav class="navbar-modern">
    <div class="navbar-inner">
        <a href="<?php echo $baseUrl; ?>main.php" class="logo">
            <div class="logo-mark">N</div>
            <span>Networkee</span>
        </a>

        <div class="nav-links">
            <a href="<?php echo $baseUrl; ?>main.php">Home</a>
            <a href="<?php echo $baseUrl; ?>pages/profile.php">Profil</a>
            <a href="<?php echo $baseUrl; ?>pages/home.php">Le Fil 🌈</a>
            <a href="<?php echo $baseUrl; ?>pages/jobs.php">Offres 💼</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="<?php echo $baseUrl; ?>pages/logout.php" class="logout">Bye 👋</a>
            <?php else: ?>
                <a href="<?php echo $baseUrl; ?>pages/login.php" class="logout">Se connecter</a>
            <?php endif; ?>
        </div>

        <button id="theme-toggle" class="theme-toggle" type="button" aria-label="Changer de thème" title="Changer de thème">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>
        </button>

        <button class="mobile-menu-btn" aria-label="Menu" onclick="document.querySelector('.nav-links').classList.toggle('open')">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="18" y2="18"/></svg>
        </button>
    </div>
</nav>
