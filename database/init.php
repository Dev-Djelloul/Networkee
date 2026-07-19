<?php
/**
 * Initialisation de la base au démarrage du conteneur (Railway / PostgreSQL).
 * Idempotent : crée les tables manquantes et insère les données de démo une seule fois.
 * N'interrompt jamais le démarrage : toute erreur est seulement journalisée.
 *
 * Les instructions sont exécutées UNE PAR UNE, et non en un seul exec() : PostgreSQL
 * enveloppe un envoi multi-instructions dans une transaction implicite, donc une seule
 * instruction en échec annulait tout le lot — y compris les CREATE TABLE déjà passés.
 * Le schéma repartait alors incomplet sans que rien ne le signale, et l'application
 * plantait en production sur une table absente.
 */

// Réutilise la logique de connexion (fournit $pdo). N'exécute rien sur MySQL/local.
if (!getenv('PGHOST')) {
    fwrite(STDOUT, "[init.php] Pas d'environnement PostgreSQL détecté, initialisation ignorée.\n");
    return;
}

require __DIR__ . '/../config/database.php';

if (!isset($pdo) || strncmp($pdo->getAttribute(PDO::ATTR_DRIVER_NAME), 'pgsql', 5) !== 0) {
    fwrite(STDOUT, "[init.php] Connexion non-PostgreSQL, initialisation ignorée.\n");
    return;
}

$sql = @file_get_contents(__DIR__ . '/init.pgsql.sql');
if ($sql === false) {
    fwrite(STDERR, "[init.php] Impossible de lire init.pgsql.sql\n");
    return;
}

/**
 * Découpe le script en instructions. Le fichier ne contient ni bloc $$ ni point-virgule
 * à l'intérieur d'un littéral, un découpage sur ';' est donc sûr ici.
 */
$statements = array_filter(
    array_map('trim', explode(';', $sql)),
    static function (string $s): bool {
        // Ignore les fragments vides et ceux réduits à des commentaires.
        foreach (explode("\n", $s) as $line) {
            $line = trim($line);
            if ($line !== '' && strncmp($line, '--', 2) !== 0) {
                return true;
            }
        }
        return false;
    }
);

$ok = 0;
$failed = 0;

foreach ($statements as $statement) {
    try {
        $pdo->exec($statement);
        $ok++;
    } catch (PDOException $e) {
        $failed++;
        // Première ligne utile de l'instruction, pour identifier laquelle a échoué.
        $label = 'instruction inconnue';
        foreach (explode("\n", $statement) as $line) {
            $line = trim($line);
            if ($line !== '' && strncmp($line, '--', 2) !== 0) {
                $label = mb_substr($line, 0, 90);
                break;
            }
        }
        fwrite(STDERR, "[init.php] ÉCHEC sur « {$label} » : " . $e->getMessage() . "\n");
    }
}

fwrite(STDOUT, "[init.php] Schéma : {$ok} instruction(s) appliquée(s), {$failed} en échec.\n");

// Vérification explicite : une table attendue mais absente doit sauter aux yeux dans
// les logs de démarrage, plutôt que de se manifester par un plantage côté visiteur.
$expected = [
    'users', 'posts', 'likes', 'reposts', 'saved_posts', 'comments', 'follows',
    'notifications', 'job_offers', 'job_applications', 'saved_jobs', 'password_resets',
];

$missing = [];
foreach ($expected as $table) {
    $stmt = $pdo->prepare("SELECT to_regclass(:table) IS NOT NULL");
    $stmt->execute(['table' => 'public.' . $table]);
    if (!$stmt->fetchColumn()) {
        $missing[] = $table;
    }
}

if ($missing) {
    fwrite(STDERR, "[init.php] TABLES MANQUANTES : " . implode(', ', $missing) . "\n");
} else {
    fwrite(STDOUT, "[init.php] Les " . count($expected) . " tables attendues sont présentes.\n");
}
