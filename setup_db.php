<?php
// Script pour initialiser la base de données locale
$host = '127.0.0.1';
$user = 'root';
$pass = ''; // mot de passe par défaut très courant (XAMPP/WAMP)

echo "<meta charset='utf-8'><h2>Configuration de la Base de Données</h2>";

try {
    // Connexion sans base spécifique d'abord pour pouvoir la créer
    $pdo = new PDO("mysql:host=$host;port=3306;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Créer la base de données
    $pdo->exec("CREATE DATABASE IF NOT EXISTS do1 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci");
    $pdo->exec("USE do1");

    // Créer la table utilisateurs
    $pdo->exec("CREATE TABLE IF NOT EXISTS utilisateurs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nom VARCHAR(100) NOT NULL,
        prenom VARCHAR(100) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        telephone VARCHAR(20) DEFAULT NULL,
        motdepasse VARCHAR(255) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Créer la table posts
    $pdo->exec("CREATE TABLE IF NOT EXISTS posts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        content TEXT NOT NULL,
        category VARCHAR(100) NOT NULL,
        thumbnail VARCHAR(255) DEFAULT NULL,
        is_private TINYINT(1) DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    echo "<p style='color:green;'>✅ Base de données 'do1' créée avec succès.</p>";
    echo "<p style='color:green;'>✅ Tables 'utilisateurs' et 'posts' créées avec succès.</p>";
    echo "<p><a href='index.php'>Retour à l'accueil du site</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color:red;'>❌ Erreur de connexion à MySQL : " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>💡 <b>Astuce :</b> Assurez-vous que MySQL (via XAMPP, WAMP ou autre) est bien lancé sur votre ordinateur !</p>";
}
?>
