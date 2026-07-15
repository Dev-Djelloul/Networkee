<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>styles/modern.css">
    <title>Networkee</title>
</head>
<body>
    <nav class="navbar-modern">
        <div class="navbar-inner">
            <a href="<?php echo $baseUrl; ?>main.php" class="logo">
                <div class="logo-mark">N</div>
                <span>Networkee</span>
            </a>

            <div class="nav-links">
                <a href="<?php echo $baseUrl; ?>main.php">Home</a>
                <a href="<?php echo $baseUrl; ?>pages/profile.php">Profil</a>
                <a href="<?php echo $baseUrl; ?>pages/home.php" class="active">Le Fil 🌈</a>
                <a href="<?php echo $baseUrl; ?>pages/logout.php" class="logout">Bye 👋</a>
            </div>

            <button class="mobile-menu-btn" aria-label="Menu" onclick="document.querySelector('.nav-links').classList.toggle('open')">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="18" y2="18"/></svg>
            </button>
        </div>
    </nav>
</body>
</html>
