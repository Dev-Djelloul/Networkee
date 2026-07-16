<?php
$baseUrl = '../';
$pageTitle = 'Modifier le profil — Networkee';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute(['id' => $_SESSION['user_id']]);
$user = $stmt->fetch();

$upload_error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bio = htmlspecialchars($_POST['bio'] ?? '', ENT_QUOTES, 'UTF-8');
    $profile_image = $user['profile_image'];

    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];

        if (!in_array($ext, $allowed_exts) || !in_array($_FILES['profile_image']['type'], $allowed_types)) {
            $upload_error = "Seules les images JPG, PNG ou GIF sont autorisées.";
        } else {
            $name = uniqid('avatar_') . '.' . $ext;
            $target_dir = __DIR__ . '/../uploads/';
            $target_file = $target_dir . $name;

            if (!is_dir($target_dir) || !is_writable($target_dir)) {
                $upload_error = "Le dossier uploads/ n'est pas accessible en écriture.";
            } elseif (!move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
                $upload_error = "Erreur lors du déplacement de l'image.";
            } else {
                $profile_image = $name;
            }
        }
    } elseif (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $upload_error = "Erreur upload (code " . $_FILES['profile_image']['error'] . ").";
    }

    if (!$upload_error) {
        $stmt = $pdo->prepare("UPDATE users SET bio = :bio, profile_image = :profile_image WHERE id = :id");
        $stmt->execute(['bio' => $bio, 'profile_image' => $profile_image, 'id' => $_SESSION['user_id']]);
        header('Location: profile.php');
        exit;
    }
}
include __DIR__ . '/../includes/head.php';
?>
<body>
    <?php include(__DIR__ . '/../includes/header.php'); ?>

    <main class="page-wrapper">
        <div class="card" style="max-width: 520px; margin: 2rem auto;">
            <div class="card-body">
                <h2 style="margin-top: 0; margin-bottom: 0.25rem;">Modifier ton profil</h2>
                <p style="color: var(--text-muted); margin-bottom: 1.5rem;">Quoi de neuf, <?php echo htmlspecialchars($user['username']); ?> ?</p>

                <?php if ($upload_error): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($upload_error); ?></div>
                <?php endif; ?>

                <form action="edit-profile.php" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label class="form-label" for="bio">Bio</label>
                        <textarea name="bio" id="bio" rows="4" class="form-input" placeholder="Parle-nous de toi..."><?php echo htmlspecialchars($user['bio'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="profile_image">Photo de profil</label>
                        <?php if ($user['profile_image']): ?>
                            <div style="margin-bottom: 0.75rem;">
                                <img src="<?php echo $baseUrl; ?>uploads/<?php echo htmlspecialchars($user['profile_image']); ?>"
                                     alt="Photo actuelle"
                                     style="width: 5rem; height: 5rem; border-radius: 1rem; object-fit: cover; border: 2px solid var(--border);">
                            </div>
                        <?php endif; ?>
                        <input type="file"
                               id="profile_image"
                               name="profile_image"
                               accept="image/jpeg,image/png,image/gif"
                               class="form-input-file">
                        <p style="font-size: 0.8125rem; color: var(--text-muted); margin: 0.375rem 0 0;">JPG, PNG ou GIF — max 40 Mo</p>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1.5rem; gap: 0.75rem;">
                        <a href="profile.php" class="btn btn-secondary">Annuler</a>
                        <button type="submit" class="btn btn-primary">Mettre à jour</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <?php include(__DIR__ . '/../includes/footer.php'); ?>
</body>
</html>
