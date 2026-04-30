<?php
// session already started above and login check performed

session_start();
// Only allow the single owner to create posts
$__owner_email = 'xium10@gmail.com';
$__owner_nom = 'NGUYEN';
$__owner_prenom = 'Xiu';
if (!isset($_SESSION['user'])
    || !isset($_SESSION['user']['email']) || strtolower($_SESSION['user']['email']) !== strtolower($__owner_email)
    || !isset($_SESSION['user']['nom']) || strtoupper($_SESSION['user']['nom']) !== strtoupper($__owner_nom)
    || !isset($_SESSION['user']['prenom']) || $_SESSION['user']['prenom'] !== $__owner_prenom) {
    echo '<!doctype html><html><head><meta charset="utf-8"><title>Accès refusé</title></head><body>';
    echo '<p>Vous n\'avez pas la permission de créer des articles.</p>';
    echo '<p><a href="books.php">Retour aux Livres</a></p>';
    echo '</body></html>';
    exit;
}
$host = '127.0.0.1';
$db = 'do1';
$user = 'root';
$pass = '';
$dsn = "mysql:host=$host;port=3306;dbname=$db;charset=utf8";
try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die('Erreur : ' . $e->getMessage());
}

$message = '';
// create posts table if not exists (with thumbnail and privacy)
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

// ensure uploads directory exists
$uploadDir = __DIR__ . '/../uploads';
if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);

// add missing columns if existing table lacks them
try {
    $cols = $pdo->query("SHOW COLUMNS FROM posts")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('thumbnail', $cols)) {
        $pdo->exec("ALTER TABLE posts ADD thumbnail VARCHAR(255) DEFAULT NULL");
    }
    if (!in_array('is_private', $cols)) {
        $pdo->exec("ALTER TABLE posts ADD is_private TINYINT(1) DEFAULT 0");
    }
} catch (Exception $e) {
    // ignore
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $category = $_POST['category'] ?? 'critiques';
    $is_private = isset($_POST['is_private']) && $_POST['is_private'] === '1' ? 1 : 0;
    $thumbnailPath = null;

    // handle file upload (optional)
    if (!empty($_FILES['thumbnail']['name'])) {
        $f = $_FILES['thumbnail'];
        if ($f['error'] === UPLOAD_ERR_OK) {
            $mime = mime_content_type($f['tmp_name']);
            if (strpos($mime, 'image/') === 0) {
                $ext = pathinfo($f['name'], PATHINFO_EXTENSION);
                $name = uniqid('thumb_', true) . '.' . $ext;
                $dest = $uploadDir . '/' . $name;
                if (move_uploaded_file($f['tmp_name'], $dest)) {
                    $thumbnailPath = 'uploads/' . $name;
                }
            }
        }
    }

    if ($title && $content) {
        $ins = $pdo->prepare('INSERT INTO posts (user_id, title, content, category, thumbnail, is_private, created_at) VALUES (:user_id, :title, :content, :category, :thumbnail, :is_private, :created_at)');
        $ins->execute([
            ':user_id' => $_SESSION['user']['id'],
            ':title' => $title,
            ':content' => $content,
            ':category' => $category,
            ':thumbnail' => $thumbnailPath,
            ':is_private' => $is_private,
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
        <form method="post" action="create_post.php" enctype="multipart/form-data">
            <label>Titre</label>
            <input type="text" name="title" required>
            <label>Catégorie</label>
            <select name="category">
                <option value="critiques">Livres</option>
                <option value="histoires">Lifestyle</option>
            </select>
            <label>Thumbnail (image)</label>
            <input type="file" name="thumbnail" accept="image/*">
            <label>Visibilité</label>
            <select name="is_private">
                <option value="0">Public</option>
                <option value="1">Privé (réservé aux utilisateurs inscrits)</option>
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
<footer><p style="color:#fff">2026 DUO</p></footer>
</body>
</html>
