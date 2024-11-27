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
    if (isset($_POST['bio'])) {
        // Modification du profil (bio et image de profil)
        $bio = $_POST['bio'];
        $profile_image = null;

        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $profile_image = $_FILES['profile_image']['name'];
            $target_dir = __DIR__ . '/../uploads/';
            $target_file = $target_dir . basename($profile_image);

            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($_FILES['profile_image']['type'], $allowed_types)) {
                $upload_error = "Seules les images JPG, PNG ou GIF sont autorisées.";
            } else {
                if (file_exists($target_file)) {
                    $upload_error = "Le fichier existe déjà. Veuillez renommer l'image.";
                } else {
                    if (!move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
                        $upload_error = "Erreur lors du déplacement de l'image.";
                    }
                }
            }
        } else {
            $profile_image = $user['profile_image'];
        }

        // Mettre à jour les informations de l'utilisateur
        $stmt = $pdo->prepare("UPDATE users SET bio = :bio, profile_image = :profile_image WHERE id = :id");
        $stmt->execute([
            'bio' => $bio,
            'profile_image' => $profile_image,
            'id' => $_SESSION['user_id']
        ]);

        header('Location: profile.php');
        exit;
    }

    // Ajouter un post
    if (isset($_POST['content']) && empty($_POST['comment_content'])) {
        $content = $_POST['content'];
        $image = null;

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $image = $_FILES['image']['name'];
            $target_dir = "../uploads/";
            $target_file = $target_dir . basename($image);

            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            if (in_array($_FILES['image']['type'], $allowed_types)) {
                if (!file_exists($target_file)) {
                    if (!move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                        echo '<p style="color: red;">Erreur lors du déplacement de l\'image.</p>';
                    }
                } else {
                    echo '<p style="color: red;">Le fichier existe déjà. Veuillez renommer l\'image.</p>';
                }
            } else {
                echo '<p style="color: red;">Seules les images JPG, PNG ou GIF sont autorisées.</p>';
            }
        }

        if (!empty($content)) {
            $stmt = $pdo->prepare("INSERT INTO posts (user_id, content, image, created_at) VALUES (:user_id, :content, :image, NOW())");
            $stmt->execute([
                'user_id' => $_SESSION['user_id'],
                'content' => $content,
                'image' => $image
            ]);
            header('Location: profile.php'); // Rediriger vers la page de profil après l'ajout du post
            exit;
        } else {
            echo '<p style="color: red;">Le contenu du post ne peut pas être vide.</p>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../styles/style.css">
    <title>Profil</title>
</head>
<body>

    <?php include(__DIR__ . '/../includes/header.php'); ?>

    <div class="container mt-5">
        <h2>Te revoilà <?php echo $user['username']; ?> 😵‍💫</h2><br>
        <p><a href="edit-profile.php" class="btn btn-warning">Change ton profil</a></p>


        <div class="text-center">
            <img src="../uploads/<?php echo $user['profile_image']; ?>" alt="Profil" class="profile-img">
        </div>
       
        <p>
        <div class="text-center">
            <p>
        <?php echo $user['bio']; ?></p>


        </div>
       
        <h3 class="mt-4">Fais nous partager 🥱</h3>
        <form action="profile.php" method="post" enctype="multipart/form-data" class="form-group">
            <textarea name="content" rows="4" class="form-control" placeholder="Quoi de neuf ?"></textarea><br>
            <label for="image">Ajoute ton image</label>
            <input type="file" name="image" class="form-control-file"><br><br>
            <input type="submit" value="C'est parti !" class="btn btn-primary">
        </form>
        

        <h3 class="mt-4">Ta guideline</h3>
        <div class="gallery-container">
            <?php
            // Récupérer les publications de l'utilisateur
            $stmt = $pdo->prepare("SELECT * FROM posts WHERE user_id = :user_id ORDER BY created_at DESC");
            $stmt->execute(['user_id' => $_SESSION['user_id']]);
            $posts = $stmt->fetchAll();

            foreach ($posts as $post):
            ?>
                <div class="post-container">
                    <p><strong><?php echo $post['content']; ?></strong></p>
                    <?php if ($post['image']): ?>
                        <img src="../uploads/<?php echo $post['image']; ?>" alt="Image du post">
                    <?php endif; ?>
                    <p><small>Publié le <?php echo date('d/m/Y H:i', strtotime($post['created_at'])); ?></small></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php include(__DIR__ . '/../includes/footer.php'); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
