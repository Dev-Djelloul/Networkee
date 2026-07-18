<?php
/**
 * Barre de navigation principale.
 * À inclure dans le <body> de chaque page, après includes/head.php.
 */
?>
<nav class="navbar-modern">
    <div class="navbar-inner">
        <a href="<?php echo $baseUrl; ?>main.php" class="logo">
            <img src="<?php echo $baseUrl; ?>icons/networkee-mark.png" alt="Networkee" class="logo-mark-img">
            <span>Networkee</span>
        </a>

        <div class="nav-links">
            <a href="<?php echo $baseUrl; ?>main.php">Home <img width="25" height="25" src="https://img.icons8.com/plasticine/100/apple-home.png" alt="apple-home"/></a>
            <a href="<?php echo $baseUrl; ?>pages/profile.php">Profil <img width="25" height="25" src="https://img.icons8.com/plasticine/100/conference-call.png" alt="conference-call"/></a>
            <a href="<?php echo $baseUrl; ?>pages/home.php">Le Fil <img width="25" height="25" src="https://img.icons8.com/doodle/48/rss--v1.png" alt="rss"/></a>
            <a href="<?php echo $baseUrl; ?>pages/jobs.php">Offres <img width="25" height="25" src="https://img.icons8.com/plasticine/100/new-job.png" alt="new-job"/></a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="<?php echo $baseUrl; ?>pages/logout.php" class="logout">Bye<img width="25" height="25" src="https://img.icons8.com/external-doodle-color-bomsymbols-/91/external-bye-avatar-basic-colors-doodle-doodle-color-bomsymbols-.png" alt="external-bye-avatar-basic-colors-doodle-doodle-color-bomsymbols-"/></a>
            <?php else: ?>
                <a href="<?php echo $baseUrl; ?>pages/login.php" class="logout">Se connecter</a>
            <?php endif; ?>
        </div>

        <a href="<?php echo $baseUrl; ?>pages/search.php" class="notif-bell" aria-label="Rechercher" title="Rechercher">
            <img src="<?php echo $baseUrl; ?>icons/icons8-search-50.png" alt="Rechercher" width="30" height="30">
        </a>

        <?php if (isset($_SESSION['user_id'])): ?>
            <?php $unreadCount = getUnreadNotificationCount((int) $_SESSION['user_id'], $pdo); ?>
            <a href="<?php echo $baseUrl; ?>pages/notifications.php" class="notif-bell" aria-label="Notifications" title="Notifications">
                <img src="<?php echo $baseUrl; ?>icons/icons8-notification-50.png" alt="Notifications" width="30" height="30">
                <?php if ($unreadCount > 0): ?>
                    <span class="notif-badge"><?php echo $unreadCount > 9 ? '9+' : $unreadCount; ?></span>
                <?php endif; ?>
            </a>
        <?php endif; ?>

        <button id="theme-toggle" class="theme-toggle" type="button" aria-label="Changer de thème" title="Changer de thème">
            <img src="<?php echo $baseUrl; ?>icons/icons8-moon-100.png" alt="" width="20" height="20">
        </button>

        <button class="mobile-menu-btn" aria-label="Menu" onclick="document.querySelector('.nav-links').classList.toggle('open')">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="18" y2="18"/></svg>
        </button>
    </div>
</nav>
