<?php echo 'Loaded ini: ' . (php_ini_loaded_file() ?: 'none') . "\n"; echo 'Extension dir: ' . ini_get('extension_dir'); ?>

<?php
// 1. Définition des paramètres
$host = '127.0.0.1';
$db   = 'do1';
$user = 'root';
$pass = '';

// 2. Le DSN (Data Source Name)
$dsn = "mysql:host=$host;port=3306;dbname=$db;charset=utf8";

try {
    // 3. Tentative de connexion
    // On crée l'instance PDO en passant le DSN, l'utilisateur et le pass 
    $pdo = new PDO($dsn, $user, $pass);

    // 4. Configuration des erreurs
    // On force PDO à lancer des exceptions en cas de problème SQL
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Bravo ! La connexion à MySQL Workbench est établie.";

} catch (PDOException $e) {
    // 5. Gestion de l'erreur
    // Si ça rate, on affiche un message propre au lieu d'une erreur système critique
    die("Erreur de connexion : " . $e->getMessage());
}

// 6. Fermeture de la connexion (Optionnel en PHP, mais recommandé)
$pdo = NULL; 
?>
