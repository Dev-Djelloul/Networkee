<?php
$baseUrl = '../';
$pageTitle = 'Réinitialiser le mot de passe — Networkee';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';
session_start();

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    $token = $_POST['token'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    $userId = $token !== '' ? validatePasswordResetToken($token, $pdo) : null;

    if (!$userId) {
        $response['message'] = "Ce lien de réinitialisation est invalide ou a expiré.";
    } elseif (empty($password) || empty($confirmPassword)) {
        $response['message'] = 'Merci de remplir les deux champs.';
    } elseif ($password !== $confirmPassword) {
        $response['message'] = 'Les mots de passe ne correspondent pas.';
    } elseif (strlen($password) < 6) {
        $response['message'] = 'Le mot de passe doit contenir au moins 6 caractères.';
    } else {
        $stmt = $pdo->prepare('UPDATE users SET password = :password WHERE id = :id');
        $stmt->execute(['password' => password_hash($password, PASSWORD_DEFAULT), 'id' => $userId]);
        invalidatePasswordResetToken($token, $pdo);

        $userStmt = $pdo->prepare('SELECT username FROM users WHERE id = :id');
        $userStmt->execute(['id' => $userId]);
        $_SESSION['user_id'] = $userId;
        $_SESSION['username'] = $userStmt->fetchColumn();

        $response['success'] = true;
        $response['message'] = 'Ton mot de passe a été mis à jour. Te voilà connecté !';
    }

    echo json_encode($response);
    exit;
}

$token = $_GET['token'] ?? '';
$tokenValid = $token !== '' && validatePasswordResetToken($token, $pdo) !== null;

include __DIR__ . '/../includes/head.php';
?>
<body>
    <?php include(__DIR__ . '/../includes/header.php'); ?>

    <main class="page-wrapper">
        <div class="card auth-card">
            <div class="card-body">
                <?php if (!$tokenValid): ?>
                    <h2>Lien invalide</h2>
                    <p>Ce lien de réinitialisation est invalide ou a expiré.</p>
                    <a href="forgot-password.php" class="btn btn-primary" style="width: 100%; justify-content: center; margin-top: 1rem;">Demander un nouveau lien</a>
                <?php else: ?>
                    <h2>Nouveau mot de passe</h2>
                    <p>Choisis un nouveau mot de passe pour ton compte 🔒</p>

                    <div id="message"></div>

                    <form id="resetForm">
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                        <div class="form-group">
                            <label class="form-label" for="password">Nouveau mot de passe</label>
                            <input type="password" id="password" name="password" class="form-input" required placeholder="••••••••">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="confirm_password">Confirme-le</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-input" required placeholder="••••••••">
                        </div>
                        <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">Réinitialiser le mot de passe</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php include(__DIR__ . '/../includes/footer.php'); ?>

    <script>
        $(document).ready(function() {
            $('#resetForm').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    url: '',
                    method: 'POST',
                    data: $(this).serialize() + '&ajax=true',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#message').html(`<div class='alert alert-success'>${response.message}</div>`);
                            setTimeout(() => window.location.href = '../main.php', 1200);
                        } else {
                            $('#message').html(`<div class='alert alert-danger'>${response.message}</div>`);
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
