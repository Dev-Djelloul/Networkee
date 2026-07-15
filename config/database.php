<?php
/**
 * Connexion à la base de données.
 * Sur Replit : utilise les variables d'environnement PG* (PostgreSQL).
 * En local XAMPP : se connecte à MySQL avec les identifiants par défaut.
 */

$host = getenv('PGHOST') ?: 'localhost';

if ($host && getenv('PGDATABASE')) {
    // Replit / PostgreSQL
    $port     = getenv('PGPORT')     ?: '5432';
    $dbname   = getenv('PGDATABASE') ?: 'networkee';
    $user     = getenv('PGUSER')     ?: 'runner';
    $password = getenv('PGPASSWORD') ?: '';
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
} else {
    // Local / XAMPP / MySQL
    $dbname   = 'networkee';
    $user     = 'root';
    $password = '';
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
}

try {
    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec("SET NAMES 'utf8mb4'");
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
