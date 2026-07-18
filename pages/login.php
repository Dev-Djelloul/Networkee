<?php
$baseUrl = '../';
$pageTitle = 'Connexion — Networkee';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';
session_start();

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    $email = htmlspecialchars($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email');
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $response['success'] = true;
        $response['message'] = 'Connexion réussie. Redirection...';
    } else {
        $response['message'] = 'Email ou mot de passe incorrect.';
    }

    echo json_encode($response);
    exit;
}
include __DIR__ . '/../includes/head.php';
?>
<body>
    <?php include(__DIR__ . '/../includes/header.php'); ?>

    <main class="page-wrapper">
        <div class="card auth-card">
            <div class="card-body">
                <h2>Connexion</h2>
                <p>Content de te revoir sur Networkee <img src="<?php echo $baseUrl; ?>icons/icons8-calendar-app-50.png" alt="" width="35" height="35" style="vertical-align: -5px;"></p>

                <div id="message"></div>

                <form id="loginForm">
                    <div class="form-group">
                        <label class="form-label" for="email">Email</label>
                        <input type="email" id="email" name="email" class="form-input" required placeholder="ton@email.com">
                    </div>
                    <div class="form-group">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <label class="form-label" for="password" style="margin-bottom: 0;">Mot de passe</label>
                            <a href="forgot-password.php" style="font-size: 0.8125rem; color: var(--text-muted);">Mot de passe oublié ?</a>
                        </div>
                        <input type="password" id="password" name="password" class="form-input" required placeholder="••••••••">
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">Je me connecte</button>
                </form>

                <p style="margin-top: 1.25rem; font-size: 0.875rem; color: var(--text-muted);">
                    Pas encore de compte ? <a href="register.php" style="color: var(--accent); font-weight: 500;">Inscris-toi</a>
                </p>
            </div>
        </div>
    </main>

    <?php include(__DIR__ . '/../includes/footer.php'); ?>

    <script>
        $(document).ready(function() {
            $('#loginForm').on('submit', function(e) {
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
                            setTimeout(() => window.location.href = '../main.php', 800);
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
