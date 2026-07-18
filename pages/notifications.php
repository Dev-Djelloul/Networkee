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

$notifications = getNotifications($userId, $pdo);
markNotificationsRead($userId, $pdo);

$icons = [
    'follow'      => 'user-plus',
    'like'        => 'heart',
    'comment'     => 'message',
    'application' => 'briefcase',
];
?>
<?php include __DIR__ . '/../includes/head.php'; ?>
<body>
    <?php include(__DIR__ . '/../includes/header.php'); ?>

    <main class="page-wrapper">
        <h2 style="margin-bottom: 1.25rem;">Notifications</h2>

        <?php if (empty($notifications)): ?>
            <div class="card" style="text-align: center; padding: 3rem 1.5rem;">
                <p style="color: var(--text-muted); margin: 0;">Tu n'as pas encore de notification.</p>
            </div>
        <?php else: ?>
            <div class="feed">
                <?php foreach ($notifications as $n): ?>
                <div class="card notification-item <?php echo $n['is_read'] ? '' : 'unread'; ?>">
                    <div class="card-body" style="display: flex; align-items: center; gap: 0.875rem;">
                        <?php echo renderAvatar($n['actor_username'], 'sm', avatarUrl($n['actor_image'], $baseUrl)); ?>
                        <div style="flex: 1;">
                            <p style="margin: 0; font-size: 0.9375rem;">
                                <a href="profile.php?id=<?php echo (int) $n['actor_id']; ?>" style="font-weight: 600;">
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
                                <span class="notif-type-icon" title="Tu suis déjà cette personne"><?php echo renderIcon('user-plus', 18); ?></span>
                            <?php else: ?>
                                <form method="POST" action="notifications.php">
                                    <input type="hidden" name="follow_back_id" value="<?php echo (int) $n['actor_id']; ?>">
                                    <button type="submit" class="notif-type-icon notif-follow-back" title="Suivre en retour">
                                        <?php echo renderIcon('user-plus', 18); ?>
                                    </button>
                                </form>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="notif-type-icon"><?php echo renderIcon($icons[$n['type']] ?? 'bell', 18); ?></span>
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
