<?php
$baseUrl = '../';
$pageTitle = 'Jetons API — Networkee';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = (int) $_SESSION['user_id'];
$newToken = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['generate_token'])) {
        $name = trim($_POST['token_name'] ?? '');
        if ($name === '') {
            $error = 'Donne un nom à ton jeton (ex. "Script de publication").';
        } else {
            $newToken = createApiToken($userId, $name, $pdo);
        }
    } elseif (isset($_POST['revoke_token_id'])) {
        revokeApiToken((int) $_POST['revoke_token_id'], $userId, $pdo);
        header('Location: api-tokens.php');
        exit;
    }
}

$tokens = getApiTokens($userId, $pdo);

include __DIR__ . '/../includes/head.php';
?>
<body>
    <?php include(__DIR__ . '/../includes/header.php'); ?>

    <main class="page-wrapper">
        <div class="card" style="max-width: 640px; margin: 2rem auto;">
            <div class="card-body">
                <h2 style="margin-top: 0; margin-bottom: 0.25rem;">Jetons API</h2>
                <p style="color: var(--text-muted); margin-bottom: 1.5rem;">
                    Publie sur ton fil depuis un script ou une page web externe, sans partager ton mot de passe.
                </p>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php if ($newToken): ?>
                    <div class="alert alert-success">
                        <strong>Jeton créé !</strong> Copie-le maintenant, il ne sera plus jamais affiché :
                        <div style="display: flex; gap: 0.5rem; align-items: stretch; margin-top: 0.5rem;">
                            <input type="text" id="new-token" readonly value="<?php echo htmlspecialchars($newToken); ?>"
                                   onclick="this.select()"
                                   style="flex: 1; padding: 0.75rem; background: var(--bg-secondary, #f1f5f9); border: 1px solid var(--border); border-radius: 0.5rem; font-family: monospace; font-size: 0.875rem;">
                            <button type="button" class="btn btn-secondary" onclick="copyToken(this)">Copier</button>
                        </div>
                    </div>
                <?php endif; ?>

                <form method="post" style="display: flex; gap: 0.5rem; align-items: flex-end; margin-bottom: 2rem;">
                    <div class="form-group" style="flex: 1; margin-bottom: 0;">
                        <label class="form-label" for="token_name">Nom du jeton</label>
                        <input type="text" id="token_name" name="token_name" class="form-input" maxlength="100" placeholder="Ex. Mon site perso" required>
                    </div>
                    <button type="submit" name="generate_token" value="1" class="btn btn-primary">Générer</button>
                </form>

                <h3 style="font-size: 1rem; margin-bottom: 0.75rem;">Jetons actifs</h3>
                <?php if (empty($tokens)): ?>
                    <p style="color: var(--text-muted); font-size: 0.875rem;">Aucun jeton pour l'instant.</p>
                <?php else: ?>
                    <ul style="list-style: none; padding: 0; margin: 0;">
                        <?php foreach ($tokens as $token): ?>
                            <li style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 0; border-bottom: 1px solid var(--border);">
                                <div>
                                    <strong><?php echo htmlspecialchars($token['name']); ?></strong>
                                    <div style="font-size: 0.8125rem; color: var(--text-muted);">
                                        Créé <?php echo timeAgo($token['created_at']); ?>
                                        <?php if ($token['last_used_at']): ?>
                                            · Utilisé <?php echo timeAgo($token['last_used_at']); ?>
                                        <?php else: ?>
                                            · Jamais utilisé
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <form method="post" onsubmit="return confirm('Révoquer ce jeton ? Toute intégration qui l\'utilise cessera de fonctionner.');">
                                    <input type="hidden" name="revoke_token_id" value="<?php echo (int) $token['id']; ?>">
                                    <button type="submit" class="btn btn-secondary btn-sm">Révoquer</button>
                                </form>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--border);">
                    <h3 style="font-size: 1rem;">Comment publier depuis l'extérieur</h3>
                    <p style="font-size: 0.875rem; color: var(--text-muted);">Envoie une requête POST avec ton jeton en en-tête :</p>
                    <pre style="background: var(--bg-secondary, #f1f5f9); padding: 0.75rem; border-radius: 0.5rem; font-size: 0.8125rem; overflow-x: auto;">curl -X POST <?php echo htmlspecialchars(($_SERVER['REQUEST_SCHEME'] ?? 'https') . '://' . ($_SERVER['HTTP_HOST'] ?? 'networkee.up.railway.app')); ?>/api/posts.php \
  -H "Authorization: Bearer nk_xxxxx..." \
  -H "Content-Type: application/json" \
  -d '{"content": "Publié depuis mon site !"}'</pre>
                </div>
            </div>
        </div>
    </main>

    <?php include(__DIR__ . '/../includes/footer.php'); ?>

    <script>
        function copyToken(btn) {
            const input = document.getElementById('new-token');
            input.select();
            navigator.clipboard.writeText(input.value).then(() => {
                const original = btn.textContent;
                btn.textContent = 'Copié ✓';
                setTimeout(() => { btn.textContent = original; }, 1500);
            }).catch(() => {
                // Repli si l'API clipboard est bloquée (contexte non sécurisé).
                document.execCommand('copy');
                btn.textContent = 'Copié ✓';
            });
        }
    </script>
</body>
</html>
