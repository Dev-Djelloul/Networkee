<?php
require_once __DIR__ . '/../config/database.php';

session_start(); // Démarrer les sessions

$response = ['success' => false, 'message' => '']; // Structure par défaut pour la réponse

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    $username = htmlspecialchars($_POST['username'] ?? '');
    $email = htmlspecialchars($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Vérifier que tous les champs sont remplis
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $response['message'] = 'Tous les champs sont obligatoires.';
    } elseif ($password !== $confirm_password) {
        // Vérifier que les mots de passe correspondent
        $response['message'] = 'Les mots de passe ne correspondent pas.';
    } else {
        // Vérifier si l'email est déjà utilisé
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);

        if ($stmt->fetchColumn() > 0) {
            $response['message'] = "Cet email est déjà utilisé.";
        } else {
            // Hashage du mot de passe
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insérer le nouvel utilisateur dans la base de données
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
            if ($stmt->execute(['username' => $username, 'email' => $email, 'password' => $hashed_password])) {
                $response['success'] = true;
                $response['message'] = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
            } else {
                $response['message'] = "Une erreur s'est produite lors de l'inscription. Essayez à nouveau.";
            }
        }
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
    <title>Inscription</title>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
</head>
<body>
    <?php include(__DIR__ . '/../includes/header.php'); ?>

    <div class="container mt-5">
        <h2>Inscris toi dès aujourd'hui!</h2>

        <!-- Zone pour afficher les messages -->
        <div id="message" class="mb-3"></div>

        <!-- Formulaire d'inscription -->
        <form id="registerForm">
            <div class="mb-3">
                <label for="username" class="link form-label">Ton nom d'utilisateur :</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>

            <div class="mb-3">
                <label for="email" class="link form-label">Ton email :</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>

            <div class="mb-3">
                <label for="password" class="link form-label">Ton mot de passe :</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>

            <div class="mb-3">
                <label for="confirm_password" class="link form-label">Confirme ton mot de passe :</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            </div>

            <button type="submit" class="btn btn-primary">Clique ici et deviens membre</button>
        </form>

        <p class="link mt-3">Peut-être as-tu déjà un compte ? <a class="link" href="login.php">Alors connecte-toi par ici 🙄</a></p>
    </div>

    <?php include(__DIR__ . '/../includes/footer.php'); ?>

    <script>
        $(document).ready(function() {
            // Gestion de la soumission du formulaire avec AJAX
            $('#registerForm').on('submit', function(e) {
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
                            $('#registerForm')[0].reset(); // Réinitialiser le formulaire
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
