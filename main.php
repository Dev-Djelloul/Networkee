<?php
$baseUrl = './';
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <title>Networkee</title>
</head>
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
