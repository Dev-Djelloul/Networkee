<?php
$baseUrl = './';
$pageTitle = 'Networkee';
session_start();
include __DIR__ . '/includes/head.php';
?>
<body>
    <?php include(__DIR__ . '/includes/header.php'); ?>

    <main class="page-wrapper">
        <div class="card hero-card">
            <?php if (isset($_SESSION['user_id'])): ?>
                <h1>Bienvenue, <?php echo htmlspecialchars($_SESSION['username']); ?> 👋</h1>
                <p>Ton fil t'attend. Découvre les dernières publications de la communauté.</p>
                <div class="hero-actions">
                    <a href="pages/home.php" class="btn btn-primary">Voir le fil</a>
                    <a href="pages/profile.php" class="btn btn-secondary">Mon profil</a>
                    <a href="pages/logout.php" class="btn btn-danger">Déconnexion</a>
                </div>
            <?php else: ?>
                <h1>Bienvenue sur Networkee</h1>
                <p>Le mini réseau social où tu peux partager tes moments et découvrir ceux des autres.</p>
                <div class="hero-actions">
                    <a href="pages/home.php" class="btn btn-primary">Découvrir le fil</a>
                    <a href="pages/login.php" class="btn btn-secondary">Se connecter</a>
                    <a href="pages/register.php" class="btn btn-primary">S'inscrire</a>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include(__DIR__ . '/includes/footer.php'); ?>
</body>
</html>
