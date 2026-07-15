<?php
$host     = getenv('PGHOST')     ?: 'localhost';
$port     = getenv('PGPORT')     ?: '5432';
$dbname   = getenv('PGDATABASE') ?: 'networkee';
$user     = getenv('PGUSER')     ?: 'runner';
$password = getenv('PGPASSWORD') ?: '';

$dsn = "pgsql:host=$host;port=$port;dbname=$dbname";

try {
    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>
