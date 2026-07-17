<?php
/**
 * Connexion à la base de données.
 * Railway : PostgreSQL via les variables d'environnement PG*.
 * Local (XAMPP) : MySQL.
 */

// Détection de l'environnement Railway
if (getenv('PGHOST') && getenv('PGDATABASE')) {

    // ===== Railway / PostgreSQL =====
    $host     = getenv('PGHOST');
    $port     = getenv('PGPORT') ?: '5432';
    $dbname   = getenv('PGDATABASE');
    $user     = getenv('PGUSER');
    $password = getenv('PGPASSWORD');

    $dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";

} else {

    // ===== Local / XAMPP / MySQL =====
    $host     = 'localhost';
    $dbname   = 'networkee';
    $user     = 'root';
    $password = '';

    $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
}

try {
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    // Seulement pour MySQL
    if (str_starts_with($dsn, 'mysql:')) {
        $pdo->exec("SET NAMES utf8mb4");
    }

} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}