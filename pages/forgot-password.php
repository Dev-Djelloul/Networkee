<?php
$baseUrl = '../';
$pageTitle = 'Mot de passe oublié — Networkee';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';
session_start();

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    $email = trim($_POST['email'] ?? '');

    // Message générique dans tous les cas : on ne révèle jamais si l'email existe ou non.
    $response['success'] = true;
    $response['message'] = "Si un compte existe pour cet email, un lien de réinitialisation vient d'être envoyé.";

    if ($email !== '') {
        $stmt = $pdo->prepare('SELECT id, username FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user) {
            $token = createPasswordReset((int) $user['id'], $pdo);
            $scheme = (($_SERVER['HTTPS'] ?? '') === 'on' || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') ? 'https' : 'http';
            $resetUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . '/pages/reset-password.php?token=' . $token;

            $html = '<p>Salut ' . htmlspecialchars($user['username']) . ',</p>'
                . '<p>Tu as demandé à réinitialiser ton mot de passe Networkee. Clique sur le lien ci-dessous (valable 1 heure) :</p>'
                . '<p><a href="' . htmlspecialchars($resetUrl) . '">Réinitialiser mon mot de passe</a></p>'
                . '<p>Si tu n\'es pas à l\'origine de cette demande, ignore simplement cet email.</p>';

            sendEmail($email, 'Réinitialise ton mot de passe Networkee', $html);
        }
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
                <h2>Mot de passe oublié</h2>
                <p>Indique ton email, on t'envoie un lien de réinitialisation 🔑</p>

                <div id="message"></div>

                <form id="forgotForm">
                    <div class="form-group">
                        <label class="form-label" for="email">Email</label>
                        <input type="email" id="email" name="email" class="form-input" required placeholder="ton@email.com">
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">Envoyer le lien</button>
                </form>

                <p style="margin-top: 1.25rem; font-size: 0.875rem; color: var(--text-muted);">
                    <a href="login.php" style="color: var(--accent); font-weight: 500;">← Retour à la connexion</a>
                </p>
            </div>
        </div>
    </main>

    <?php include(__DIR__ . '/../includes/footer.php'); ?>

    <script>
        $(document).ready(function() {
            $('#forgotForm').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    url: '',
                    method: 'POST',
                    data: $(this).serialize() + '&ajax=true',
                    dataType: 'json',
                    success: function(response) {
                        $('#message').html(`<div class='alert alert-success'>${response.message}</div>`);
                        $('#forgotForm')[0].reset();
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
