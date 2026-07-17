<?php
/**
 * Initialisation de la base au démarrage du conteneur (Railway / PostgreSQL).
 * Idempotent : crée les tables manquantes et insère les données de démo une seule fois.
 * N'interrompt jamais le démarrage : toute erreur est seulement journalisée.
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

try {
    $pdo->exec($sql);
    fwrite(STDOUT, "[init.php] Schéma et données initialisés avec succès.\n");
} catch (PDOException $e) {
    fwrite(STDERR, "[init.php] Erreur d'initialisation : " . $e->getMessage() . "\n");
}
