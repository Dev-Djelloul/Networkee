<?php
/**
 * API REST — création de publications depuis l'extérieur du site.
 *
 * Authentification : en-tête "Authorization: Bearer <jeton>" avec un jeton
 * généré sur pages/api-tokens.php. Pas de session/cookie requis, donc
 * utilisable depuis une page web tierce, un script ou une appli externe.
 *
 * POST /api/posts.php
 * Corps JSON ou x-www-form-urlencoded : { "content": "Texte du post" }
 * Réponse 201 : { "success": true, "post": { id, content, created_at, author } }
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';

header('Content-Type: application/json; charset=utf-8');
// Pas de cookies impliqués (auth par jeton) : ouvrir le CORS ne crée pas de
// risque CSRF, et c'est nécessaire pour un appel fetch() depuis un autre site.
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Authorization, Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée. Utilise POST.']);
    exit;
}

// getallheaders() est indisponible sur certaines configs SAPI ; on retombe
// sur les variantes que PHP expose alors dans $_SERVER.
$authHeader = '';
if (function_exists('getallheaders')) {
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
}
if ($authHeader === '') {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
}

if (!preg_match('/^Bearer\s+(.+)$/i', trim($authHeader), $matches)) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Jeton manquant. Ajoute l\'en-tête "Authorization: Bearer <ton_jeton>".']);
    exit;
}

$user = validateApiToken(trim($matches[1]), $pdo);
if (!$user) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Jeton invalide ou révoqué.']);
    exit;
}

// Accepte un corps JSON ou un POST classique x-www-form-urlencoded.
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if (stripos($contentType, 'application/json') !== false) {
    $body = json_decode(file_get_contents('php://input'), true);
    $content = is_array($body) ? trim((string) ($body['content'] ?? '')) : '';
} else {
    $content = trim((string) ($_POST['content'] ?? ''));
}

if ($content === '') {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Le champ "content" est requis et ne peut pas être vide.']);
    exit;
}

if (mb_strlen($content) > 5000) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Le contenu dépasse la limite de 5000 caractères.']);
    exit;
}

try {
    $stmt = $pdo->prepare(
        "INSERT INTO posts (user_id, content, created_at) VALUES (:user_id, :content, NOW())"
    );
    $stmt->execute([
        'user_id' => $user['id'],
        'content' => $content,
    ]);
    $postId = (int) $pdo->lastInsertId();

    http_response_code(201);
    echo json_encode([
        'success' => true,
        'post' => [
            'id'         => $postId,
            'content'    => $content,
            'created_at' => date('c'),
            'author'     => $user['username'],
        ],
    ]);
} catch (PDOException $e) {
    error_log('[api/posts.php] Échec insertion post : ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur serveur, réessaie plus tard.']);
}
