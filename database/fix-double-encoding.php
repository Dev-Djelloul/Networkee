<?php
/**
 * Nettoyage ponctuel des textes doublement encodés en base.
 *
 * Contexte : jusqu'à ce correctif, plusieurs formulaires (offres d'emploi, profil,
 * inscription) passaient les champs par htmlspecialchars() AVANT l'insertion, alors
 * que les vues les ré-échappent à l'affichage. Résultat : "l'application" stocké en
 * "l&#039;application" puis affiché tel quel à l'écran. La source est corrigée
 * (on ne stocke plus que du brut) ; ce script répare les lignes déjà écrites.
 *
 * Idempotent : décode en boucle jusqu'à stabilité, ne touche que les lignes qui
 * changent réellement. Peut être relancé sans risque.
 *
 * Usage : php database/fix-double-encoding.php [--dry-run]
 */

require __DIR__ . '/../config/database.php';

$dryRun = in_array('--dry-run', $argv ?? [], true);

// table => [colonne clé, colonnes texte à décoder]
$targets = [
    'users'      => ['username', 'bio', 'job_title', 'location', 'skills'],
    'job_offers' => ['title', 'company', 'location', 'description'],
];

/** Décode jusqu'à stabilité : un champ a pu être encodé plusieurs fois de suite. */
function decodeFully(string $value): string {
    for ($i = 0; $i < 5; $i++) {
        $decoded = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if ($decoded === $value) {
            return $value;
        }
        $value = $decoded;
    }
    return $value;
}

$totalFixed = 0;

foreach ($targets as $table => $columns) {
    $rows = $pdo->query("SELECT id, " . implode(', ', $columns) . " FROM {$table}")->fetchAll();

    foreach ($rows as $row) {
        $changes = [];
        foreach ($columns as $col) {
            $current = $row[$col];
            if ($current === null || $current === '') {
                continue;
            }
            $fixed = decodeFully((string) $current);
            if ($fixed !== $current) {
                $changes[$col] = $fixed;
            }
        }

        if (!$changes) {
            continue;
        }

        $totalFixed++;
        echo "[{$table}#{$row['id']}] " . implode(', ', array_keys($changes)) . "\n";
        foreach ($changes as $col => $fixed) {
            echo "    {$col}: " . $row[$col] . "  ->  " . $fixed . "\n";
        }

        if ($dryRun) {
            continue;
        }

        $set = implode(', ', array_map(fn($c) => "{$c} = :{$c}", array_keys($changes)));
        $stmt = $pdo->prepare("UPDATE {$table} SET {$set} WHERE id = :id");
        $stmt->execute($changes + ['id' => $row['id']]);
    }
}

echo $dryRun
    ? "\n{$totalFixed} ligne(s) à corriger (dry-run, rien n'a été écrit).\n"
    : "\n{$totalFixed} ligne(s) corrigée(s).\n";
