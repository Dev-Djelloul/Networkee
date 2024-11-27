<?php
require_once __DIR__ . '/../config/database.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Récupérer les informations de l'utilisateur connecté
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute(['id' => $_SESSION['user_id']]);
$user = $stmt->fetch();

// Modifier les informations du profil
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bio = htmlspecialchars($_POST['bio'], ENT_QUOTES, 'UTF-8'); // Corriger l'encodage ici
    $profile_image = null; // Par défaut, pas d'image

    // Si l'utilisateur a téléchargé une image de profil
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $profile_image = $_FILES['profile_image']['name']; // Nom de l'image téléchargée
        $target_dir = __DIR__ . '/../uploads/'; // Le dossier où les images seront stockées
        $target_file = $target_dir . basename($profile_image); // Le chemin complet vers l'image

        // Vérifier si l'image est un type autorisé (optionnel)
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['profile_image']['type'], $allowed_types)) {
            $upload_error = "Seules les images JPG, PNG ou GIF sont autorisées.";
        } else {
            // Vérifier si le fichier existe déjà
            if (file_exists($target_file)) {
                $upload_error = "Le fichier existe déjà. Veuillez renommer l'image.";
            } else {
                // Déplacer l'image téléchargée vers le dossier
                if (!move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
                    $upload_error = "Erreur lors du déplacement de l'image. Veuillez réessayer.";
                }
            }
        }
    } else {
        $profile_image = $user['profile_image']; // Conserver l'ancienne image si aucune nouvelle image n'est téléchargée
    }

    // Mettre à jour les informations de l'utilisateur
    $stmt = $pdo->prepare("UPDATE users SET bio = :bio, profile_image = :profile_image WHERE id = :id");
    $stmt->execute([
        'bio' => $bio,
        'profile_image' => $profile_image,
        'id' => $_SESSION['user_id']
    ]);

    header('Location: profile.php'); // Rediriger vers la page du profil
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../styles/style.css">
    <title>Change le profil</title>
</head>
<body>

    <?php include(__DIR__ . '/../includes/header.php'); ?>

    <div class="container mt-5">
        <h2>Du changement ? <?php echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?></h2>

        <?php if (isset($upload_error)): ?>
            <div class="alert alert-danger"><?php echo $upload_error; ?></div>
        <?php endif; ?>

        <!-- Formulaire pour modifier les informations -->
        <form action="edit-profile.php" method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="bio" class="form-label">Raconte moi</label>
                <textarea name="bio" rows="4" class="form-control">
                    <?php echo htmlspecialchars($user['bio'], ENT_QUOTES, 'UTF-8'); ?>
                </textarea>
            </div>

            <div class="mb-3">
                <label for="profile_image" class="form-label">Une nouvelle photo ?</label>
                <input type="file" name="profile_image" class="form-control">
            </div>

            <button type="submit" class="btn btn-primary">Je mets à jour</button>
        </form>
    </div><br>

    <?php include(__DIR__ . '/../includes/footer.php'); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
