<?php
$baseUrl = '../';
$pageTitle = 'Inscription — Networkee';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';
session_start();

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    $username = htmlspecialchars($_POST['username'] ?? '');
    $email = htmlspecialchars($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $response['message'] = 'Tous les champs sont obligatoires.';
    } elseif ($password !== $confirm_password) {
        $response['message'] = 'Les mots de passe ne correspondent pas.';
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);

        if ($stmt->fetchColumn() > 0) {
            $response['message'] = "Cet email est déjà utilisé.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
            if ($stmt->execute(['username' => $username, 'email' => $email, 'password' => $hashed_password])) {
                // Connexion automatique : évite de redemander les identifiants juste après l'inscription.
                $_SESSION['user_id'] = (int) $pdo->lastInsertId();
                $_SESSION['username'] = $username;
                $response['success'] = true;
                $response['message'] = "Bienvenue sur Networkee ! Ton compte est prêt.";
            } else {
                $response['message'] = "Une erreur s'est produite lors de l'inscription.";
            }
        }
    }

    echo json_encode($response);
    exit;
}
?>
<?php include __DIR__ . '/../includes/head.php'; ?>
<body>
    <?php include(__DIR__ . '/../includes/header.php'); ?>

    <main class="page-wrapper">
        <div class="card auth-card">
            <div class="card-body">
                <h2>Créer un compte</h2>
                <p>Rejoins la communauté Networkee en quelques secondes <img src="<?php echo $baseUrl; ?>icons/icons8-star-50.png" alt="" width="30" height="30" style="vertical-align: -5px;"></p>

                <div id="message"></div>

                <form id="registerForm">
                    <div class="form-group">
                        <label class="form-label" for="username">Nom d'utilisateur</label>
                        <input type="text" id="username" name="username" class="form-input" required placeholder="Ton pseudo">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="email">Email</label>
                        <input type="email" id="email" name="email" class="form-input" required placeholder="ton@email.com">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="password">Mot de passe</label>
                        <input type="password" id="password" name="password" class="form-input" required placeholder="••••••••">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="confirm_password">Confirme ton mot de passe</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-input" required placeholder="••••••••">
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">S'inscrire</button>
                </form>

                <p style="margin-top: 1.25rem; font-size: 0.875rem; color: var(--text-muted);">
                    Déjà un compte ? <a href="login.php" style="color: var(--accent); font-weight: 500;">Connecte-toi</a>
                </p>
            </div>
        </div>
    </main>

    <?php include(__DIR__ . '/../includes/footer.php'); ?>

    <script>
        $(document).ready(function() {
            $('#registerForm').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    url: '',
                    method: 'POST',
                    data: $(this).serialize() + '&ajax=true',
                    dataType: 'json',
                    success: function(response) {
                        const messageDiv = $('#message');
                        if (response.success) {
                            messageDiv.html(`<div class='alert alert-success'>${response.message}</div>`);
                            $('#registerForm')[0].reset();
                            setTimeout(() => window.location.href = '../main.php', 1000);
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
</body>
</html>
