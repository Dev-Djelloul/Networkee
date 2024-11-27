<?php
require_once __DIR__ . '/../config/database.php';
session_start();

// Nombre de posts par page
$postsPerPage = 3;

// Page actuelle
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;

// Calcul de l'offset
$offset = ($page - 1) * $postsPerPage;

// Récupération des posts avec limite et offset
$stmt = $pdo->prepare("
    SELECT posts.*, users.username, users.id AS user_id 
    FROM posts 
    JOIN users ON posts.user_id = users.id 
    ORDER BY created_at DESC 
    LIMIT :offset, :limit
");
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $postsPerPage, PDO::PARAM_INT);
$stmt->execute();
$posts = $stmt->fetchAll();

// Calcul du nombre total de posts
$totalPostsStmt = $pdo->query("SELECT COUNT(*) FROM posts");
$totalPosts = $totalPostsStmt->fetchColumn();

// Calcul du nombre total de pages
$totalPages = ceil($totalPosts / $postsPerPage);

// Fonction pour obtenir le nombre de likes d'un post
function getLikeCount($postId, $pdo) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE post_id = :post_id");
    $stmt->execute(['post_id' => $postId]);
    return $stmt->fetchColumn();
}

// Ajout de commentaire (sans échappement)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_content'], $_POST['post_id']) && isset($_SESSION['user_id'])) {
    $comment_content = $_POST['comment_content']; // Aucun traitement
    $post_id = (int) $_POST['post_id'];

    if (!empty($comment_content)) {
        $stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, content, created_at) VALUES (:post_id, :user_id, :content, NOW())");
        $stmt->execute([
            'post_id' => $post_id,
            'user_id' => $_SESSION['user_id'],
            'content' => $comment_content
        ]);
        header("Location: home.php?page=$page");
        exit;
    } else {
        $commentError = 'Le commentaire ne peut pas être vide.';
    }
}

// Gestion des likes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['like'], $_SESSION['user_id'])) {
    $postId = (int) $_POST['like'];
    $userId = $_SESSION['user_id'];

    // Vérifier si l'utilisateur a déjà liké ce post
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE post_id = :post_id AND user_id = :user_id");
    $stmt->execute(['post_id' => $postId, 'user_id' => $userId]);
    $liked = $stmt->fetchColumn() > 0;

    if ($liked) {
        // Si déjà liké, on supprime le like
        $stmt = $pdo->prepare("DELETE FROM likes WHERE post_id = :post_id AND user_id = :user_id");
        $stmt->execute(['post_id' => $postId, 'user_id' => $userId]);
    } else {
        // Sinon, on ajoute le like
        $stmt = $pdo->prepare("INSERT INTO likes (post_id, user_id) VALUES (:post_id, :user_id)");
        $stmt->execute(['post_id' => $postId, 'user_id' => $userId]);
    }
    header("Location: home.php?page=$page");  // Redirige pour éviter la soumission multiple
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
    <title>Fil de publication</title>
</head>
<body>
    <?php include(__DIR__ . '/../includes/header.php'); ?>

    <div class="container mt-4">
        <h5>Les derniers posts</h5>
        <?php foreach ($posts as $post): ?>
            <div class="post-container">
                <div class="post">
                    <h1>
                        <a class="nav-link" href="profile.php?id=<?php echo $post['user_id']; ?>"><?php echo $post['username']; ?></a>
                    </h1>
                    <p><?php echo nl2br($post['content']); ?></p>
                    <?php if ($post['image']): ?>
                        <img src="../uploads/<?php echo $post['image']; ?>" alt="Image">
                    <?php endif; ?>
                    
                    <!-- Bouton Like et compteur -->
                    <div class="like-section">
                        <form method="POST" action="home.php?page=<?php echo $page; ?>">
                            <button class="like-button" name="like" value="<?php echo $post['id']; ?>" type="submit">
                                <span class="like-icon"><img src="/networkee/icons/icons8-like-24 (1).png"
                                alt="Icône" style="width: 20px; height: 20px; margin:5px; ">J'aime</span>
                            </button>
                        </form>
                        <span class="like-counter" id="like-counter-<?php echo $post['id']; ?>"><?php echo getLikeCount($post['id'], $pdo); ?></span>
                    </div>
                </div>
                
                <div class="comments">
                    <?php
                    $stmt = $pdo->prepare("SELECT comments.*, users.username FROM comments JOIN users ON comments.user_id = users.id WHERE post_id = :post_id ORDER BY created_at DESC");
                    $stmt->execute(['post_id' => $post['id']]);
                    $comments = $stmt->fetchAll();
                    foreach ($comments as $comment): ?>
                        <div class="comment">
                            <strong><?php echo $comment['username']; ?>:</strong>
                            <p><?php echo nl2br($comment['content']); ?></p>
                        </div>
                    <?php endforeach; ?>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <!-- Formulaire de commentaire -->
                        <form method="POST" action="home.php?page=<?php echo $page; ?>">
                            <textarea name="comment_content" rows="2" class="form-control"></textarea>
                            <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>"><br>
                            <button type="submit" class="btn btn-sm btn-secondary mt-2">Laisse ton commentaire</button>
                        </form>
                    <?php else: ?>
                        <!-- Message pour inviter à se connecter -->
                        <p><a href="login.php">Connecte-toi</a> pour laisser un commentaire.</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Pagination -->
        <div class="pagination mt-4">
            <nav>
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>">Précédent</a>
                        </li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php if ($i === $page) echo 'active'; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>">Suivant</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </div>

    <?php include(__DIR__ . '/../includes/footer.php'); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="like.js"></script>
</body>
</html>
