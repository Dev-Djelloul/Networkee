<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (isset($_SESSION['user_id'])) {
    // Si connecté, afficher le contenu de la page d'accueil
    $content = "
        <h1 class='text-center mb-4'>Welcome " . htmlspecialchars($_SESSION['username']) . " !</h1>
        <div class='text-center'>
            <a href='pages/home.php' class='btn btn-primary me-2'>Fil d'actualité 🤓</a>
            <a href='pages/profile.php' class='btn btn-secondary me-2'>Ton profil 😎</a>
            <a href='pages/logout.php' class='btn btn-danger'>Bye 👋</a>
        </div>";
} else {
    // Si non connecté, afficher les options pour se connecter ou s'inscrire
    $content = "
        <h1 class='text-center mb-4'>Welcome 🤢🥴</h1>
        <div class='text-center'>
            <a href='pages/home.php' class='btn btn-primary me-2'>Fil d'actualité 🤓</a>
            <a href='pages/login.php' class='btn btn-success me-2'>Connecte-toi 📲</a>
            <a href='pages/register.php' class='btn btn-info'>Ou inscris-toi 🫶</a>
        </div>";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <title>My Networkee</title>
    <style>
        /* Appliquer une image de fond */
        body {
            font-family: Impact, Haettenschweiler, 'Arial Narrow Bold', sans-serif;
            margin: 0;
            padding: 0;
            background: url('images/pexels-pixabay-2156.jpeg') no-repeat fixed;
            background-size: cover;
            color: #fff;
        }

        /* Centrer le contenu */
        .container {
            background-color: rgba(0, 0, 0, 0.6); /* Ajouter une couche sombre */
            padding: 20px;
            border-radius: 10px;
            max-width: 600px;
            margin: 100px auto;
        }

        h1 {
            font-size: 2.5rem;
        }

        .btn {
            margin: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php echo $content; ?>
    </div>
</body>
</html>
