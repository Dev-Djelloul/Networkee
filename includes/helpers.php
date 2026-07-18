<?php
/**
 * Helper functions for the modern Networkee UI.
 */

/**
 * Generate deterministic initials and a gradient class from a username.
 */
function getAvatarStyle(string $username): array {
    $clean = trim($username);
    $parts = explode(' ', $clean);
    $initials = '';
    if (isset($parts[0][0])) {
        $initials .= strtoupper($parts[0][0]);
    }
    if (isset($parts[1][0])) {
        $initials .= strtoupper($parts[1][0]);
    }
    if (strlen($initials) === 0) {
        $initials = strtoupper(substr($clean, 0, 2));
    }
    if (strlen($initials) === 0) {
        $initials = '?';
    }

    $gradients = [
        'gradient-teal', 'gradient-blue', 'gradient-rose', 'gradient-amber',
        'gradient-indigo', 'gradient-emerald', 'gradient-violet', 'gradient-slate'
    ];
    $index = array_sum(array_map('ord', str_split($clean))) % count($gradients);
    $gradient = $gradients[$index];

    return ['initials' => $initials, 'gradient' => $gradient];
}

function renderAvatar(string $username, string $size = '', string $imageUrl = '', bool $openToWork = false): string {
    $style = getAvatarStyle($username);
    $class = 'avatar';
    if ($size === 'lg') {
        $class .= ' avatar-lg';
    } elseif ($size === 'sm') {
        $class .= ' avatar-sm';
    }
    if ($imageUrl) {
        $html = '<img src="' . htmlspecialchars($imageUrl) . '" alt="' . htmlspecialchars($username) . '" class="' . $class . '" style="object-fit:cover;">';
    } else {
        $html = '<div class="' . $class . ' ' . $style['gradient'] . '">' . htmlspecialchars($style['initials']) . '</div>';
    }
    if ($openToWork) {
        return '<div class="avatar-wrapper">' . $html . '<span class="otw-badge" title="Open to work">✓</span></div>';
    }
    return $html;
}

/**
 * Construit l'URL publique de l'avatar d'un utilisateur.
 * Renvoie '' si aucune photo (ou valeur par défaut) → renderAvatar retombe sur l'initiale.
 */
function avatarUrl(?string $profileImage, string $baseUrl): string {
    if (empty($profileImage) || $profileImage === 'default.png') {
        return '';
    }
    return $baseUrl . 'uploads/' . $profileImage;
}

function renderIcon(string $name, int $size = 20): string {
    $icons = [
        'heart' => '<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>',
        'message' => '<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>',
        'image' => '<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>',
        'smile' => '<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" x2="9.01" y1="9" y2="9"/><line x1="15" x2="15.01" y1="9" y2="9"/></svg>',
        'send' => '<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m22 2-7 20-4-9-9-4 20-7z"/><path d="M22 2 11 13"/></svg>',
        'more' => '<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/><circle cx="5" cy="12" r="1"/></svg>',
        'chevron-left' => '<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>',
        'chevron-right' => '<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>',
        'briefcase'    => '<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="7" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>',
        'map-pin'      => '<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>',
        'tag'          => '<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z"/><circle cx="7.5" cy="7.5" r="1.5"/></svg>',
        'users'        => '<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
        'bell'         => '<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>',
        'user-plus'    => '<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/></svg>',
        'search'       => '<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>',
        'close'        => '<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" x2="6" y1="6" y2="18"/><line x1="6" x2="18" y1="6" y2="18"/></svg>',
    ];
    return $icons[$name] ?? '';
}

function timeAgo(string $date): string {
    $now = new DateTime();
    $then = new DateTime($date);
    $diff = $now->diff($then);

    if ($diff->y > 0) return 'Il y a ' . $diff->y . ' an' . ($diff->y > 1 ? 's' : '');
    if ($diff->m > 0) return 'Il y a ' . $diff->m . ' mois';
    if ($diff->d > 7) return 'Il y a ' . floor($diff->d / 7) . ' semaine' . (floor($diff->d / 7) > 1 ? 's' : '');
    if ($diff->d > 0) return 'Il y a ' . $diff->d . ' jour' . ($diff->d > 1 ? 's' : '');
    if ($diff->h > 0) return 'Il y a ' . $diff->h . ' heure' . ($diff->h > 1 ? 's' : '');
    if ($diff->i > 0) return 'Il y a ' . $diff->i . ' minute' . ($diff->i > 1 ? 's' : '');
    return 'À l\'instant';
}

function hasUserLikedPost(int $postId, int $userId, PDO $pdo): bool {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE post_id = :post_id AND user_id = :user_id");
    $stmt->execute(['post_id' => $postId, 'user_id' => $userId]);
    return $stmt->fetchColumn() > 0;
}

function getLikeCount(int $postId, PDO $pdo): int {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE post_id = :post_id");
    $stmt->execute(['post_id' => $postId]);
    return (int) $stmt->fetchColumn();
}

function getComments(int $postId, PDO $pdo): array {
    $stmt = $pdo->prepare(
        "SELECT c.*, u.username, u.profile_image
         FROM comments c
         JOIN users u ON c.user_id = u.id
         WHERE c.post_id = :post_id
         ORDER BY c.created_at ASC"
    );
    $stmt->execute(['post_id' => $postId]);
    return $stmt->fetchAll();
}

function isFollowing(int $followerId, int $followedId, PDO $pdo): bool {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM follows WHERE follower_id = :follower_id AND followed_id = :followed_id");
    $stmt->execute(['follower_id' => $followerId, 'followed_id' => $followedId]);
    return $stmt->fetchColumn() > 0;
}

function getFollowerCount(int $userId, PDO $pdo): int {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM follows WHERE followed_id = :user_id");
    $stmt->execute(['user_id' => $userId]);
    return (int) $stmt->fetchColumn();
}

function getFollowingCount(int $userId, PDO $pdo): int {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM follows WHERE follower_id = :user_id");
    $stmt->execute(['user_id' => $userId]);
    return (int) $stmt->fetchColumn();
}

/** Utilisateurs qui suivent $userId (les plus récents en premier). */
function getFollowers(int $userId, PDO $pdo, int $limit = 20): array {
    $stmt = $pdo->prepare(
        "SELECT u.id, u.username, u.profile_image
         FROM follows f JOIN users u ON f.follower_id = u.id
         WHERE f.followed_id = :user_id
         ORDER BY f.created_at DESC
         LIMIT $limit"
    );
    $stmt->execute(['user_id' => $userId]);
    return $stmt->fetchAll();
}

/** Utilisateurs que $userId suit (les plus récents en premier). */
function getFollowingList(int $userId, PDO $pdo, int $limit = 20): array {
    $stmt = $pdo->prepare(
        "SELECT u.id, u.username, u.profile_image
         FROM follows f JOIN users u ON f.followed_id = u.id
         WHERE f.follower_id = :user_id
         ORDER BY f.created_at DESC
         LIMIT $limit"
    );
    $stmt->execute(['user_id' => $userId]);
    return $stmt->fetchAll();
}

/** Utilisateurs ayant liké un post donné (les plus récents en premier). */
function getPostLikers(int $postId, PDO $pdo, int $limit = 20): array {
    $stmt = $pdo->prepare(
        "SELECT u.id, u.username, u.profile_image
         FROM likes l JOIN users u ON l.user_id = u.id
         WHERE l.post_id = :post_id
         ORDER BY l.created_at DESC
         LIMIT $limit"
    );
    $stmt->execute(['post_id' => $postId]);
    return $stmt->fetchAll();
}

/**
 * Rend la liste au survol (avatar + nom) utilisée par les popovers hover-stat.
 * $items : lignes avec au minimum id/username/profile_image.
 */
function renderHoverList(array $items, string $emptyText, string $baseUrl): string {
    if (empty($items)) {
        return '<p class="hover-popover-empty">' . htmlspecialchars($emptyText) . '</p>';
    }
    $html = '<ul class="hover-popover-list">';
    foreach ($items as $item) {
        $html .= '<li><a href="' . $baseUrl . 'pages/profile.php?id=' . (int) $item['id'] . '">'
            . renderAvatar($item['username'], 'sm', avatarUrl($item['profile_image'], $baseUrl))
            . '<span>' . htmlspecialchars($item['username']) . '</span>'
            . '</a></li>';
    }
    $html .= '</ul>';
    return $html;
}

/**
 * Crée une notification, sauf si l'acteur et le destinataire sont la même personne.
 */
function createNotification(int $userId, int $actorId, string $type, ?int $postId, PDO $pdo): void {
    if ($userId === $actorId) {
        return;
    }
    $stmt = $pdo->prepare(
        "INSERT INTO notifications (user_id, actor_id, type, post_id, created_at) VALUES (:user_id, :actor_id, :type, :post_id, NOW())"
    );
    $stmt->execute([
        'user_id'  => $userId,
        'actor_id' => $actorId,
        'type'     => $type,
        'post_id'  => $postId,
    ]);
}

function getUnreadNotificationCount(int $userId, PDO $pdo): int {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = :user_id AND is_read = 0");
    $stmt->execute(['user_id' => $userId]);
    return (int) $stmt->fetchColumn();
}

function getNotifications(int $userId, PDO $pdo, int $limit = 30): array {
    // post_id est réutilisé comme job_offer_id pour le type 'application'.
    $stmt = $pdo->prepare(
        "SELECT n.*, u.username AS actor_username, u.profile_image AS actor_image, jo.title AS job_title
         FROM notifications n
         JOIN users u ON n.actor_id = u.id
         LEFT JOIN job_offers jo ON n.type = 'application' AND n.post_id = jo.id
         WHERE n.user_id = :user_id
         ORDER BY n.created_at DESC
         LIMIT $limit"
    );
    $stmt->execute(['user_id' => $userId]);
    return $stmt->fetchAll();
}

function markNotificationsRead(int $userId, PDO $pdo): void {
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = :user_id AND is_read = 0");
    $stmt->execute(['user_id' => $userId]);
}

function notificationText(array $n): string {
    return match ($n['type']) {
        'follow'      => 'a commencé à te suivre.',
        'like'        => 'a aimé ta publication.',
        'comment'     => 'a commenté ta publication.',
        'application' => 'a postulé à ton offre' . (!empty($n['job_title']) ? ' « ' . $n['job_title'] . ' »' : '') . '.',
        default       => 'a interagi avec ton compte.',
    };
}

/**
 * Normalise un texte pour la recherche : minuscules, accents retirés (table
 * manuelle — iconv //TRANSLIT n'est pas fiable sous Alpine/musl, utilisé en
 * production), ponctuation réduite à des espaces.
 */
function normalizeSearchText(string $s): string {
    $s = mb_strtolower($s, 'UTF-8');
    $accents = [
        'à' => 'a', 'â' => 'a', 'ä' => 'a', 'á' => 'a', 'ã' => 'a', 'å' => 'a',
        'ç' => 'c',
        'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
        'î' => 'i', 'ï' => 'i', 'ì' => 'i', 'í' => 'i',
        'ô' => 'o', 'ö' => 'o', 'ò' => 'o', 'ó' => 'o', 'õ' => 'o',
        'ù' => 'u', 'û' => 'u', 'ü' => 'u', 'ú' => 'u',
        'ÿ' => 'y', 'ñ' => 'n', 'œ' => 'oe', 'æ' => 'ae',
    ];
    $s = strtr($s, $accents);
    $s = preg_replace('/[^a-z0-9]+/', ' ', $s);
    return trim(preg_replace('/\s+/', ' ', $s));
}

/**
 * Vrai si chaque mot de la requête normalisée apparaît (comme sous-chaîne)
 * dans le texte normalisé — insensible à la casse, aux accents et à la
 * ponctuation, tolérant sur l'ordre des mots.
 */
function searchTextMatches(string $normalizedHaystack, string $normalizedQuery): bool {
    $words = array_filter(explode(' ', $normalizedQuery));
    if (empty($words)) {
        return false;
    }
    foreach ($words as $word) {
        if (!str_contains($normalizedHaystack, $word)) {
            return false;
        }
    }
    return true;
}

function hasApplied(int $jobOfferId, int $userId, PDO $pdo): bool {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM job_applications WHERE job_offer_id = :job_offer_id AND user_id = :user_id");
    $stmt->execute(['job_offer_id' => $jobOfferId, 'user_id' => $userId]);
    return $stmt->fetchColumn() > 0;
}

function getApplicationCount(int $jobOfferId, PDO $pdo): int {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM job_applications WHERE job_offer_id = :job_offer_id");
    $stmt->execute(['job_offer_id' => $jobOfferId]);
    return (int) $stmt->fetchColumn();
}

function getApplicants(int $jobOfferId, PDO $pdo): array {
    $stmt = $pdo->prepare(
        "SELECT ja.*, u.username, u.profile_image, u.job_title, u.location
         FROM job_applications ja
         JOIN users u ON ja.user_id = u.id
         WHERE ja.job_offer_id = :job_offer_id
         ORDER BY ja.created_at DESC"
    );
    $stmt->execute(['job_offer_id' => $jobOfferId]);
    return $stmt->fetchAll();
}

/**
 * Crée une demande de réinitialisation de mot de passe et retourne le token brut
 * (à insérer dans le lien envoyé par email). Seul son hash est stocké en base.
 * Invalide les demandes précédentes de cet utilisateur au passage.
 */
function createPasswordReset(int $userId, PDO $pdo): string {
    $pdo->prepare("DELETE FROM password_resets WHERE user_id = :user_id")->execute(['user_id' => $userId]);

    $rawToken = bin2hex(random_bytes(32));
    $stmt = $pdo->prepare(
        "INSERT INTO password_resets (user_id, token_hash, expires_at, created_at)
         VALUES (:user_id, :token_hash, :expires_at, NOW())"
    );
    $stmt->execute([
        'user_id'    => $userId,
        'token_hash' => hash('sha256', $rawToken),
        'expires_at' => date('Y-m-d H:i:s', time() + 3600),
    ]);

    return $rawToken;
}

/**
 * Retourne l'id utilisateur associé à un token de reset valide (non expiré), ou null.
 */
function validatePasswordResetToken(string $rawToken, PDO $pdo): ?int {
    $stmt = $pdo->prepare(
        "SELECT user_id FROM password_resets WHERE token_hash = :token_hash AND expires_at > NOW()"
    );
    $stmt->execute(['token_hash' => hash('sha256', $rawToken)]);
    $userId = $stmt->fetchColumn();
    return $userId !== false ? (int) $userId : null;
}

function invalidatePasswordResetToken(string $rawToken, PDO $pdo): void {
    $stmt = $pdo->prepare("DELETE FROM password_resets WHERE token_hash = :token_hash");
    $stmt->execute(['token_hash' => hash('sha256', $rawToken)]);
}

/**
 * Envoie un email via l'API REST de Resend (https://resend.com). Nécessite la variable
 * d'environnement RESEND_API_KEY. Retourne true si Resend a accepté l'envoi.
 * RESEND_FROM_EMAIL permet de personnaliser l'expéditeur une fois un domaine vérifié
 * sur Resend ; par défaut on utilise leur adresse de test partagée.
 */
function sendEmail(string $to, string $subject, string $html): bool {
    $apiKey = getenv('RESEND_API_KEY');
    if (!$apiKey) {
        error_log('[sendEmail] RESEND_API_KEY manquante, email non envoyé à ' . $to);
        return false;
    }

    $from = getenv('RESEND_FROM_EMAIL') ?: 'Networkee <onboarding@resend.dev>';

    $ch = curl_init('https://api.resend.com/emails');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
        ],
        CURLOPT_POSTFIELDS => json_encode([
            'from'    => $from,
            'to'      => [$to],
            'subject' => $subject,
            'html'    => $html,
        ]),
        CURLOPT_TIMEOUT => 10,
    ]);
    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($status < 200 || $status >= 300) {
        error_log('[sendEmail] Échec Resend (HTTP ' . $status . ') : ' . $response);
        return false;
    }
    return true;
}

function renderSkillTags(string $skills): string {
    if (empty(trim($skills))) return '';
    $tags = array_filter(array_map('trim', explode(',', $skills)));
    if (empty($tags)) return '';
    $html = '<div class="skills-list">';
    foreach ($tags as $tag) {
        $html .= '<span class="skill-tag">' . htmlspecialchars($tag) . '</span>';
    }
    $html .= '</div>';
    return $html;
}
