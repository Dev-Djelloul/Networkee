<?php
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
        $name = basename($_FILES['profile_image']['name']);
        $target_dir = __DIR__ . '/../uploads/';
        $target_file = $target_dir . $name;
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];

        if (!in_array($_FILES['profile_image']['type'], $allowed_types)) {
            $upload_error = "Seules les images JPG, PNG ou GIF sont autorisées.";
        } elseif (!move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
            $upload_error = "Erreur lors du déplacement de l'image.";
        } else {
            $profile_image = $name;
        }
    }

    if (!$upload_error) {
        $stmt = $pdo->prepare("UPDATE users SET bio = :bio, profile_image = :profile_image WHERE id = :id");
        $stmt->execute(['bio' => $bio, 'profile_image' => $profile_image, 'id' => $_SESSION['user_id']]);
        header('Location: profile.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/styles/modern.css">
    <title>Modifier le profil — Networkee</title>
</head>
<body>
    <?php include(__DIR__ . '/../includes/header.php'); ?>

    <main class="page-wrapper">
        <div class="card" style="max-width: 520px; margin: 2rem auto;">
            <div class="card-body">
                <h2 style="margin-top: 0; margin-bottom: 0.25rem;">Modifier ton profil</h2>
                <p style="color: var(--text-muted); margin-bottom: 1.5rem;">Quoi de neuf, <?php echo htmlspecialchars($user['username']); ?> ?</p>

                <?php if ($upload_error): ?>
                    <div class="alert alert-danger"><?php echo $upload_error; ?></div>
                <?php endif; ?>

                <form action="edit-profile.php" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label class="form-label" for="bio">Bio</label>
                        <textarea name="bio" id="bio" rows="4" class="form-input" placeholder="Parle-nous de toi..."><?php echo htmlspecialchars($user['bio'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="profile_image">Photo de profil</label>
                        <label class="file-input-wrapper" style="display: block;">
                            <span class="btn btn-secondary" style="width: 100%; justify-content: center;">Choisir une image</span>
                            <input type="file" id="profile_image" name="profile_image" accept="image/jpeg,image/png,image/gif">
                        </label>
                    </div>

                    <div class="composer-actions" style="margin-top: 1.5rem;">
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
