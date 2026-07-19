<?php
/**
 * Jeu de données de démonstration pour tester les notifications de bout en bout.
 *
 * Génère, pour un compte cible, une notification de CHAQUE type — avec les données
 * réelles derrière (un vrai follow, un vrai like, une vraie candidature…) et non des
 * lignes de notifications isolées : cliquer dessus mène donc à un contenu qui existe.
 *
 * Crée au besoin les comptes de démo, un post et une offre appartenant à la cible.
 * Idempotent sur les comptes (réutilisés s'ils existent), mais chaque exécution
 * ajoute un nouveau lot de notifications non lues.
 *
 * Usage : php database/seed-demo.php [email|id]   (défaut : le compte le plus ancien)
 *         php database/seed-demo.php --clean      (retire les comptes de démo)
 */

require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/helpers.php';

$arg = $argv[1] ?? null;

// Comptes de démo : le mot de passe est haché comme à l'inscription, ils sont donc
// utilisables pour se connecter et vérifier l'autre bout du parcours.
$demoUsers = [
    ['username' => 'Sophie',  'email' => 'sophie@networkee.test',  'job_title' => 'Product Designer'],
    ['username' => 'Camille', 'email' => 'camille@networkee.test', 'job_title' => 'Développeuse front'],
    ['username' => 'Malik',   'email' => 'malik@networkee.test',   'job_title' => 'Recruteur tech'],
];
const DEMO_PASSWORD = 'demo1234';

if ($arg === '--clean') {
    $emails = array_column($demoUsers, 'email');
    $in = implode(',', array_fill(0, count($emails), '?'));
    // Les posts, likes, notifications… de ces comptes partent en cascade (contraintes FK).
    $stmt = $pdo->prepare("DELETE FROM users WHERE email IN ($in)");
    $stmt->execute($emails);
    echo "Comptes de démo supprimés ({$stmt->rowCount()}).\n";
    return;
}

// ── Compte cible ────────────────────────────────────────────────────────────
if ($arg === null) {
    $target = $pdo->query("SELECT * FROM users ORDER BY id ASC LIMIT 1")->fetch();
} elseif (is_numeric($arg)) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->execute(['id' => (int) $arg]);
    $target = $stmt->fetch();
} else {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute(['email' => $arg]);
    $target = $stmt->fetch();
}

if (!$target) {
    fwrite(STDERR, "Compte cible introuvable : " . var_export($arg, true) . "\n");
    exit(1);
}

$targetId = (int) $target['id'];
echo "Cible : {$target['username']} (#{$targetId})\n\n";

// ── Comptes de démo ─────────────────────────────────────────────────────────
$actorIds = [];
foreach ($demoUsers as $demo) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->execute(['email' => $demo['email']]);
    $id = $stmt->fetchColumn();

    if (!$id) {
        $stmt = $pdo->prepare(
            "INSERT INTO users (username, email, password, job_title) VALUES (:username, :email, :password, :job_title)"
        );
        $stmt->execute([
            'username'  => $demo['username'],
            'email'     => $demo['email'],
            'password'  => password_hash(DEMO_PASSWORD, PASSWORD_DEFAULT),
            'job_title' => $demo['job_title'],
        ]);
        $id = $pdo->lastInsertId();
        echo "  + compte créé : {$demo['username']} ({$demo['email']} / " . DEMO_PASSWORD . ")\n";
    }
    $actorIds[] = (int) $id;
}

if (count($actorIds) < 3) {
    fwrite(STDERR, "Comptes de démo incomplets.\n");
    exit(1);
}
[$sophie, $camille, $malik] = $actorIds;

// ── Un post de la cible, support des like / comment / repost ────────────────
$stmt = $pdo->prepare("SELECT id FROM posts WHERE user_id = :id ORDER BY id DESC LIMIT 1");
$stmt->execute(['id' => $targetId]);
$postId = (int) $stmt->fetchColumn();

if (!$postId) {
    $stmt = $pdo->prepare("INSERT INTO posts (user_id, content, created_at) VALUES (:user_id, :content, NOW())");
    $stmt->execute([
        'user_id' => $targetId,
        'content' => "Première publication de test : si tu lis ceci, le fil fonctionne. Les notifications qui suivent pointent toutes vers ce post.",
    ]);
    $postId = (int) $pdo->lastInsertId();
    echo "  + post créé (#{$postId})\n";
}

// ── Une offre de la cible, support de la candidature ────────────────────────
$stmt = $pdo->prepare("SELECT id FROM job_offers WHERE user_id = :id ORDER BY id DESC LIMIT 1");
$stmt->execute(['id' => $targetId]);
$offerId = (int) $stmt->fetchColumn();

if (!$offerId) {
    $stmt = $pdo->prepare(
        "INSERT INTO job_offers (user_id, title, company, location, type, description, created_at)
         VALUES (:user_id, :title, :company, :location, :type, :description, NOW())"
    );
    $stmt->execute([
        'user_id'     => $targetId,
        'title'       => 'Intégrateur·rice web',
        'company'     => 'Studio Démo',
        'location'    => 'Remote',
        'type'        => 'CDD',
        // Apostrophes volontaires : vérifie au passage que l'encodage reste propre.
        'description' => "Mission d'intégration front sur l'app d'un client. C'est un test.",
    ]);
    $offerId = (int) $pdo->lastInsertId();
    echo "  + offre créée (#{$offerId})\n";
}

echo "\nInteractions générées :\n";

/** Insère en ignorant les doublons (contraintes UNIQUE) pour rester rejouable. */
$insertIgnore = function (string $sql, array $params) use ($pdo): bool {
    try {
        $pdo->prepare($sql)->execute($params);
        return true;
    } catch (PDOException $e) {
        return false; // déjà présent
    }
};

// 1. Suivi
if ($insertIgnore(
    "INSERT INTO follows (follower_id, followed_id) VALUES (:follower_id, :followed_id)",
    ['follower_id' => $sophie, 'followed_id' => $targetId]
)) {
    echo "  · Sophie te suit\n";
}
createNotification($targetId, $sophie, 'follow', null, $pdo);

// 2. Like
$insertIgnore(
    "INSERT INTO likes (post_id, user_id) VALUES (:post_id, :user_id)",
    ['post_id' => $postId, 'user_id' => $camille]
);
createNotification($targetId, $camille, 'like', $postId, $pdo);
echo "  · Camille a aimé ton post\n";

// 3. Commentaire
$pdo->prepare("INSERT INTO comments (post_id, user_id, content, created_at) VALUES (:post_id, :user_id, :content, NOW())")
    ->execute(['post_id' => $postId, 'user_id' => $camille, 'content' => "Très clair, merci pour le partage !"]);
createNotification($targetId, $camille, 'comment', $postId, $pdo);
echo "  · Camille a commenté ton post\n";

// 4. Repartage
$insertIgnore(
    "INSERT INTO reposts (post_id, user_id) VALUES (:post_id, :user_id)",
    ['post_id' => $postId, 'user_id' => $sophie]
);
createNotification($targetId, $sophie, 'repost', $postId, $pdo);
echo "  · Sophie a repartagé ton post\n";

// 5. Candidature — post_id sert de job_offer_id pour ce type (voir getNotifications).
$insertIgnore(
    "INSERT INTO job_applications (job_offer_id, user_id, message, created_at) VALUES (:job_offer_id, :user_id, :message, NOW())",
    ['job_offer_id' => $offerId, 'user_id' => $malik, 'message' => "Bonjour, l'offre m'intéresse beaucoup. Disponible dès l'automne."]
);
createNotification($targetId, $malik, 'application', $offerId, $pdo);
echo "  · Malik a postulé à ton offre\n";

$unread = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = :id AND is_read = 0");
$unread->execute(['id' => $targetId]);

echo "\n{$unread->fetchColumn()} notification(s) non lue(s) pour {$target['username']}.\n";
echo "Connecte-toi et ouvre pages/notifications.php pour les voir.\n";
