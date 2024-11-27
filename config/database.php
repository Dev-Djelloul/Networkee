<?php
$host = 'localhost';
$db_name = 'networkee';
$username = 'root';
$password = '';

try {
    // Connexion à la base de données avec utf8mb4
    $pdo = new PDO("mysql:host=$host;port=3307;dbname=$db_name;charset=utf8mb4;unix_socket=/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Fixer l'encodage pour toutes les interactions avec la base de données
    $pdo->exec("SET NAMES 'utf8mb4'");  // Force l'encodage des requêtes SQL
    $pdo->exec("SET CHARACTER SET utf8mb4");  // Définit l'encodage de la connexion
    $pdo->exec("SET SESSION collation_connection = 'utf8mb4_general_ci'");  // Définit la collation de la connexion
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>
