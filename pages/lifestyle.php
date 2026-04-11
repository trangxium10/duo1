<?php
session_start();
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

$q = trim($_GET['q'] ?? '');

$sql = 'SELECT p.*, u.nom as author_nom, u.prenom as author_prenom FROM posts p LEFT JOIN utilisateurs u ON p.user_id = u.id WHERE p.category = :cat';
if ($q !== '') {
    $sql .= ' AND (p.title LIKE :q OR p.content LIKE :q)';
}
$sql .= ' ORDER BY p.created_at DESC';

$stmt = $pdo->prepare($sql);
$params = [':cat' => 'histoires'];
if ($q !== '') $params[':q'] = '%' . $q . '%';
$stmt->execute($params);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="../css/style.css">
    <title>DUO — Lifestyle</title>
</head>
<body>
<header><h1><span>DUO</span></h1></header>
<nav>
    <a href="../index.php">🏠 Accueil</a>
    <a href="books.php">📚 Livres</a>
    <a href="lifestyle.php">✨ Lifestyle</a>
    <a href="register.php">👤 Mon espace</a>
</nav>
<main>
    <div class="article-header lifestyle">
        <h1>✨ Lifestyle</h1>
        <p>Récits de vie et conseils pratiques</p>
    </div>

    <div class="profil">
        <form method="get" action="lifestyle.php">
            <input type="text" name="q" placeholder="Rechercher dans Lifestyle..." value="<?php echo htmlspecialchars($q); ?>">
            <button class="btn-principal" type="submit">Rechercher</button>
        </form>

        <?php if (!$posts): ?>
            <p>Aucun article.</p>
        <?php else: ?>
            <?php foreach($posts as $p): ?>
                <article style="border:1px solid #ccc;padding:12px;margin:10px 0">
                    <h3><?php echo htmlspecialchars($p['title']); ?></h3>
                    <div style="font-size:0.9em;color:#666">Par <?php echo htmlspecialchars($p['author_prenom'] ?: $p['author_nom']); ?> — <?php echo htmlspecialchars($p['created_at']); ?></div>
                    <p><?php echo nl2br(htmlspecialchars(substr($p['content'],0,800))); ?><?php if(strlen($p['content'])>800) echo '...'; ?></p>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>

    </div>
    <div class="liens-retour">
        <a class="lien-retour" href="../index.php">← Retour à l'accueil</a>
    </div>
</main>
<footer><p style="color:#fff">© 2024 DUO — Tous droits réservés</p></footer>
</body>
</html>
