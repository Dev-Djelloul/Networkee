<?php
$baseUrl = '../';
$pageTitle = 'Notifications — Networkee';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = (int) $_SESSION['user_id'];

// Suivre en retour, directement depuis la notification "X a commencé à te suivre".
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['follow_back_id'])) {
    $targetId = (int) $_POST['follow_back_id'];
    if ($targetId && $targetId !== $userId && !isFollowing($userId, $targetId, $pdo)) {
        $stmt = $pdo->prepare("INSERT INTO follows (follower_id, followed_id) VALUES (:follower_id, :followed_id)");
        $stmt->execute(['follower_id' => $userId, 'followed_id' => $targetId]);
        createNotification($targetId, $userId, 'follow', null, $pdo);
    }
    header('Location: notifications.php');
    exit;
}

// Tout marquer comme lu, sans avoir à ouvrir chaque notification une par une.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_all_read'])) {
    markAllNotificationsRead($userId, $pdo);
    header('Location: notifications.php');
    exit;
}

// Clic sur une notification : elle passe en "lue", puis on redirige vers sa cible.
// Le marquage se fait ici et non au chargement de la page : ouvrir la liste ne doit
// plus suffire à tout marquer comme lu, seul le clic sur une notification la consomme.
if (isset($_GET['read']) && is_numeric($_GET['read'])) {
    $notificationId = (int) $_GET['read'];

    $stmt = $pdo->prepare("SELECT * FROM notifications WHERE id = :id AND user_id = :user_id");
    $stmt->execute(['id' => $notificationId, 'user_id' => $userId]);
    $notification = $stmt->fetch();

    if (!$notification) {
        header('Location: notifications.php');
        exit;
    }

    markNotificationRead($notificationId, $userId, $pdo);

    header('Location: ' . notificationLink($notification));
    exit;
}

$notifications = getNotifications($userId, $pdo);

// Compté sur la liste affichée : le bouton n'apparaît que s'il y a matière à marquer.
$unreadOnPage = count(array_filter($notifications, fn($n) => !$n['is_read']));

$customIcons = [
    'follow'      => 'icons8-add-user-50.png',
    'like'        => 'icons8-like-heart-50.png',
    'comment'     => 'icons8-comment-50.png',
    'repost'      => 'icons8-repost-64.png',
    'application' => 'icons8-job-seeker-100.png',
];
?>
<?php include __DIR__ . '/../includes/head.php'; ?>
<body>
    <?php include(__DIR__ . '/../includes/header.php'); ?>

    <main class="page-wrapper">
        <div style="display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.25rem;">
            <h2 style="margin: 0;">Notifications</h2>
            <?php if ($unreadOnPage > 0): ?>
                <form method="POST" action="notifications.php">
                    <input type="hidden" name="mark_all_read" value="1">
                    <button type="submit" class="btn btn-secondary">
                        <img src="<?php echo $baseUrl; ?>icons/icons8-checkmark-50.png" alt="" width="24" height="24" style="vertical-align: -6px;">
                        Tout marquer comme lu (<?php echo $unreadOnPage; ?>)
                    </button>
                </form>
            <?php endif; ?>
        </div>

        <?php if (empty($notifications)): ?>
            <div class="card" style="text-align: center; padding: 3rem 1.5rem;">
                <p style="color: var(--text-muted); margin: 0;">Tu n'as pas encore de notification.</p>
            </div>
        <?php else: ?>
            <div class="feed">
                <?php foreach ($notifications as $n): ?>
                <div class="card notification-item <?php echo $n['is_read'] ? '' : 'unread'; ?>">
                    <?php // Lien étiré : couvre toute la carte sans imbriquer de <a> dans le lien profil ou le formulaire. ?>
                    <a class="notif-stretched-link" href="notifications.php?read=<?php echo (int) $n['id']; ?>"
                       aria-label="Ouvrir la notification<?php echo $n['is_read'] ? '' : ' (non lue)'; ?>"></a>
                    <div class="card-body" style="display: flex; align-items: center; gap: 0.875rem;">
                        <?php echo renderAvatar($n['actor_username'], 'sm', avatarUrl($n['actor_image'], $baseUrl)); ?>
                        <div style="flex: 1;">
                            <p style="margin: 0; font-size: 0.9375rem;">
                                <a href="profile.php?id=<?php echo (int) $n['actor_id']; ?>" style="font-weight: 500;" class="notif-inline-link">
                                    <?php echo htmlspecialchars($n['actor_username']); ?>
                                </a>
                                <?php echo notificationText($n); ?>
                            </p>
                            <time style="font-size: 0.8125rem; color: var(--text-muted);">
                                <?php echo timeAgo($n['created_at']); ?>
                            </time>
                        </div>
                        <?php if ($n['type'] === 'follow'): ?>
                            <?php $alreadyFollowing = isFollowing($userId, (int) $n['actor_id'], $pdo); ?>
                            <?php if ($alreadyFollowing): ?>
                                <span class="notif-type-icon" title="Tu suis déjà cette personne"><img src="<?php echo $baseUrl; ?>icons/icons8-add-user-50.png" alt="" width="35" height="35"></span>
                            <?php else: ?>
                                <form method="POST" action="notifications.php">
                                    <input type="hidden" name="follow_back_id" value="<?php echo (int) $n['actor_id']; ?>">
                                    <button type="submit" class="notif-type-icon notif-follow-back" title="Suivre en retour">
                                        <img src="<?php echo $baseUrl; ?>icons/icons8-add-user-50.png" alt="" width="35" height="35">
                                    </button>
                                </form>
                            <?php endif; ?>
                        <?php elseif (isset($customIcons[$n['type']])): ?>
                            <span class="notif-type-icon"><img src="<?php echo $baseUrl; ?>icons/<?php echo $customIcons[$n['type']]; ?>" alt="" width="35" height="35"></span>
                        <?php else: ?>
                            <span class="notif-type-icon"><?php echo renderIcon('bell', 30); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <?php include(__DIR__ . '/../includes/footer.php'); ?>
</body>
</html>
