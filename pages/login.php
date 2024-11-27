<?php
require_once __DIR__ . '/../config/database.php';
session_start();

$response = ['success' => false, 'message' => '']; // Structure par défaut pour la réponse

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    $email = htmlspecialchars($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Vérifier si l'email existe dans la base de données
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email');
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Authentification réussie, stockage des données dans la session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];

        $response['success'] = true;
        $response['message'] = 'Connexion réussie. Redirection...';
    } else {
        $response['message'] = 'Email ou mot de passe incorrect.';
    }

    // Retourner la réponse JSON
    echo json_encode($response);
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/style.css">
    <title>Page de connexion</title>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
</head>
<body>
    <?php include(__DIR__ . '/../includes/header.php'); ?>

    <div class="container mt-5">
        <h2>Vite connecte toi 👅</h2>

        <!-- Zone pour afficher les messages -->
        <div id="message" class="mb-3"></div>

        <!-- Formulaire de connexion -->
        <form id="loginForm">
            <div class="mb-3">
                <label for="email" class="link form-label">Ton email :</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>

            <div class="mb-3">
                <label for="password" class="link form-label">Ton mot de passe :</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>

            <button type="submit" class="btn btn-primary">Je me connecte</button>
        </form>

        <p class="link mt-3">Tu n'as pas encore de compte ? <a class="link" href="register.php">Inscris-toi par ici 😉</a></p>
    </div>

    <?php include(__DIR__ . '/../includes/footer.php'); ?>

    <script>
        $(document).ready(function() {
            // Gestion de la soumission du formulaire avec AJAX
            $('#loginForm').on('submit', function(e) {
                e.preventDefault(); // Empêche le rechargement de la page

                $.ajax({
                    url: '', // Même fichier
                    method: 'POST',
                    data: $(this).serialize() + '&ajax=true', // Ajouter un indicateur AJAX
                    dataType: 'json',
                    success: function(response) {
                        const messageDiv = $('#message');
                        if (response.success) {
                            messageDiv.html(`<div class='alert alert-success'>${response.message}</div>`);
                            setTimeout(() => {
                                window.location.href = '/networkee/main.php'; // Redirection vers le profil après connexion
                            }, 1000);
                        } else {
                            messageDiv.html(`<div class='alert alert-danger'>${response.message}</div>`);
                        }
                    },
                    error: function() {
                        $('#message').html("<div class='alert alert-danger'>Une erreur est survenue. Veuillez réessayer.</div>");
                    }
                });
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
