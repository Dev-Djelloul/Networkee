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

function renderAvatar(string $username, string $size = '', string $imageUrl = ''): string {
    $style = getAvatarStyle($username);
    $class = 'avatar';
    if ($size === 'lg') {
        $class .= ' avatar-lg';
    } elseif ($size === 'sm') {
        $class .= ' avatar-sm';
    }
    if ($imageUrl) {
        return '<img src="' . htmlspecialchars($imageUrl) . '" alt="' . htmlspecialchars($username) . '" class="' . $class . '" style="object-fit:cover;">';
    }
    return '<div class="' . $class . ' ' . $style['gradient'] . '">' . htmlspecialchars($style['initials']) . '</div>';
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
