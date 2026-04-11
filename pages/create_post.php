<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: register.php');
    exit;
}

// Only allow the owner to create posts
$ownerEmail = 'xium10@gmail.com';
if (!isset($_SESSION['user']['email']) || strtolower($_SESSION['user']['email']) !== strtolower($ownerEmail)) {
    // simple deny: show message and link back to posts
    echo '<!doctype html><html><head><meta charset="utf-8"><title>Accès refusé</title></head><body>';
    echo '<p>Vous n\'avez pas la permission de créer des articles.</p>';
    echo '<p><a href="books.php">Retour aux Livres</a></p>';
    echo '</body></html>';
    exit;
}
$host = '127.0.0.1';
$db = 'do1';
$user = 'root';
$pass = 'xungkhium10';
$dsn = "mysql:host=$host;port=3306;dbname=$db;charset=utf8";
try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die('Erreur : ' . $e->getMessage());
}

$message = '';
// create posts table if not exists
$pdo->exec("CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    category VARCHAR(100) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $category = $_POST['category'] ?? 'critiques';
    if ($title && $content) {
        $ins = $pdo->prepare('INSERT INTO posts (user_id, title, content, category, created_at) VALUES (:user_id, :title, :content, :category, :created_at)');
        $ins->execute([
            ':user_id' => $_SESSION['user']['id'],
            ':title' => $title,
            ':content' => $content,
            ':category' => $category,
            ':created_at' => date('Y-m-d H:i:s')
        ]);
        // redirect to category page so the post is visible immediately
        if ($category === 'critiques') header('Location: books.php');
        else header('Location: lifestyle.php');
        exit;
    } else {
        $message = 'Veuillez saisir un titre et un contenu.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="../css/style.css">
    <title>Créer un article</title>
</head>
<body>
<header><h1><span>DUO</span></h1></header>
<nav>
    <a href="../index.php">Accueil</a>
    <a href="posts.php">Articles</a>
    <a href="profile.php">Mon profil</a>
</nav>
<main>
    <div class="profil">
        <h2>Nouvel article</h2>
        <?php if ($message): ?><div class="msg-succes"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
        <form method="post" action="create_post.php">
            <label>Titre</label>
            <input type="text" name="title" required>
            <label>Catégorie</label>
            <select name="category">
                <option value="critiques">Livres</option>
                <option value="histoires">Lifestyle</option>
            </select>
            <label>Contenu</label>
            <textarea name="content" rows="8" required></textarea>
            <button class="btn-principal" type="submit">Publier</button>
        </form>
        <div class="liens-retour" style="margin-top:20px;border:none;padding:0">
            <a class="lien-retour" href="posts.php">← Voir les articles</a>
        </div>
    </div>
</main>
<footer><p style="color:#fff">2024 DUO</p></footer>
</body>
</html>
