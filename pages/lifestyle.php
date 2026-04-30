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
    <a href="../index.php">Accueil</a>
    <a href="books.php">Livres</a>
    <a href="lifestyle.php">Lifestyle</a>
    <a href="register.php">Mon espace</a>
</nav>
<main>
    <div class="article-header lifestyle">
        <h1>Lifestyle</h1>
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
                <?php
                    $isPrivate = !empty($p['is_private']) && $p['is_private']==1;
                    $thumb = !empty($p['thumbnail']) ? '../' . ltrim($p['thumbnail'], '/') : '';
                    $link = ($isPrivate && !isset($_SESSION['user'])) ? 'register.php' : 'post.php?id=' . intval($p['id']);
                ?>
                <article style="border:1px solid #ccc;padding:12px;margin:10px 0;display:flex;gap:12px">
                    <?php if($thumb): ?>
                        <div style="width:140px;flex:0 0 140px;position:relative">
                            <a href="<?php echo $link; ?>"><img src="<?php echo htmlspecialchars($thumb); ?>" alt="" style="width:100%;height:90px;object-fit:cover;border-radius:8px"></a>
                        </div>
                    <?php endif; ?>
                    <div style="flex:1">
                        <h3 style="display:flex;align-items:center;gap:12px">
                            <a href="<?php echo $link; ?>"><?php echo htmlspecialchars($p['title']); ?></a>
                            <?php if (isset($_SESSION['user']) && isset($_SESSION['user']['id']) && $_SESSION['user']['id'] == $p['user_id']): ?>
                                <a class="link-action" href="edit_post.php?id=<?php echo intval($p['id']); ?>">Modifier</a>
                                <form method="post" action="delete_post.php" style="display:inline;margin-left:8px">
                                    <input type="hidden" name="id" value="<?php echo intval($p['id']); ?>">
                                    <input type="hidden" name="return" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">
                                    <button type="submit" class="link-action link-delete" onclick="return confirm('Supprimer cet article ?');">Supprimer</button>
                                </form>
                            <?php endif; ?>
                        </h3>
                        <div style="font-size:0.9em;color:#666">Par <?php echo htmlspecialchars($p['author_prenom'] ?: $p['author_nom']); ?> — <?php echo htmlspecialchars($p['created_at']); ?></div>
                        <p><?php echo nl2br(htmlspecialchars(substr($p['content'],0,300))); ?><?php if(strlen($p['content'])>300) echo '...'; ?></p>
                    </div>
                    <?php if($isPrivate && !isset($_SESSION['user'])): ?>
                        <div style="margin-left:auto;color:var(--accent-error);font-weight:800">🔒</div>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>

    </div>
    <div class="liens-retour">
        <a class="lien-retour" href="../index.php">← Retour à l'accueil</a>
    </div>
</main>
<footer><p style="color:#fff">© 2026 DUO — Tous droits réservés</p></footer>
</body>
</html>
