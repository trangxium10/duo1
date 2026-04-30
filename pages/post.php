<?php
session_start();
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

$id = intval($_GET['id'] ?? 0);
if (!$id) { header('Location: posts.php'); exit; }

$stmt = $pdo->prepare('SELECT p.*, u.nom as author_nom, u.prenom as author_prenom FROM posts p LEFT JOIN utilisateurs u ON p.user_id = u.id WHERE p.id = :id');
$stmt->execute([':id'=>$id]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$post) { header('Location: posts.php'); exit; }

// If post is private and visitor not logged in, redirect to register
if (!empty($post['is_private']) && $post['is_private']==1 && !isset($_SESSION['user'])) {
    header('Location: register.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="../css/style.css">
    <title><?php echo htmlspecialchars($post['title']); ?></title>
    <style>.post-thumb{width:100%;max-height:320px;object-fit:cover;border-radius:12px;margin-bottom:16px}</style>
</head>
<body>
<header><h1><span>DUO</span></h1></header>
<nav>
    <a href="../index.php">Accueil</a>
    <a href="books.php">Livres</a>
    <a href="lifestyle.php">Lifestyle</a>
    <a href="register.php">Mon espace</a>
</nav>
<main>
    <div class="article-header" style="margin-top:12px">
        <h1><?php echo htmlspecialchars($post['title']); ?></h1>
        <div style="font-size:0.9em;color:#fff;opacity:0.9">Par <?php echo htmlspecialchars($post['author_prenom'] ?: $post['author_nom']); ?> — <?php echo htmlspecialchars($post['created_at']); ?></div>
    </div>
    <div class="article-body" style="padding:24px">
        <?php if (!empty($post['thumbnail'])): ?>
            <img class="post-thumb" src="../<?php echo htmlspecialchars(ltrim($post['thumbnail'], '/')); ?>" alt="">
        <?php endif; ?>
        <?php if (isset($_SESSION['user']) && isset($_SESSION['user']['id']) && $_SESSION['user']['id'] == $post['user_id']): ?>
            <a class="link-action" href="edit_post.php?id=<?php echo intval($post['id']); ?>">Modifier</a>
            <form method="post" action="delete_post.php" style="margin-top:8px;display:inline-block;margin-left:8px">
                <input type="hidden" name="id" value="<?php echo intval($post['id']); ?>">
                <input type="hidden" name="return" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">
                <button type="submit" class="link-action link-delete" onclick="return confirm('Supprimer cet article ?');">Supprimer l\'article</button>
            </form>
        <?php endif; ?>
        <div><?php echo nl2br(htmlspecialchars($post['content'])); ?></div>
    </div>
</main>
<footer><p style="color:#fff">2026 DUO</p></footer>
</body>
</html>
