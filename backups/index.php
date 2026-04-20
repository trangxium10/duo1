<?php
session_start();
require_once __DIR__ . '/includes/permissions.php';

$host = '127.0.0.1';
$db = 'do1';
$user = 'root';
$pass = 'xungkhium10';
$dsn = "mysql:host=$host;port=3306;dbname=$db;charset=utf8";
try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    $pdo = null;
}

// Ensure posts table has new columns (thumbnail, is_private) to avoid SELECT errors
if ($pdo) {
    try {
        $c1 = $pdo->query("SHOW COLUMNS FROM posts LIKE 'thumbnail'")->fetchAll();
        if (count($c1) === 0) {
            $pdo->exec("ALTER TABLE posts ADD COLUMN thumbnail VARCHAR(255) DEFAULT NULL");
        }
        $c2 = $pdo->query("SHOW COLUMNS FROM posts LIKE 'is_private'")->fetchAll();
        if (count($c2) === 0) {
            $pdo->exec("ALTER TABLE posts ADD COLUMN is_private TINYINT(1) DEFAULT 0");
        }
    } catch (Exception $e) {
        // if posts table doesn't exist or ALTER fails, ignore — other pages will handle creating table
    }
}

// For legacy uses, tests should use is_owner()

$postsLivres = [];
$postsLifestyle = [];
if ($pdo) {
    $stmt = $pdo->prepare("SELECT p.id, p.title, p.content, p.created_at, p.thumbnail, p.is_private, u.nom, u.prenom FROM posts p LEFT JOIN utilisateurs u ON p.user_id = u.id WHERE p.category = :cat ORDER BY p.created_at DESC LIMIT 3");
    $stmt->execute([':cat' => 'critiques']);
    $postsLivres = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->execute([':cat' => 'histoires']);
    $postsLifestyle = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css?v=2">
    <title>DUO — Accueil</title>
    <style>
    .recent-post {border:1px solid #eee;padding:10px;margin-top:12px;background:#fff}
    .recent-post h4{margin:0 0 6px}
    </style>
</head>
<body class="page-accueil">
    <header>
        <h1><span>DUO</span></h1>
    </header>

    <nav>
        <a href="index.php">Accueil</a>
        <a href="pages/books.php">Livres</a>
        <a href="pages/lifestyle.php">Lifestyle</a>
        <a class="link-outline" href="pages/register.php">Mon espace</a>
        <?php if (is_owner()): ?>
            <a href="pages/create_post.php">Créer</a>
        <?php endif; ?>
        <!-- search moved below hero on the Accueil page -->
    </nav>

    <main>
        <section class="hero">
            <h1>Bienvenue sur <span>DUO</span></h1>
            <p>Découvrez des critiques de livres, des récits de vie et des conseils pour les jeunes vivant en France.</p>
            <a class="btn-hero" href="pages/books.php">Commencer maintenant →</a>
        </section>

        <!-- Centered search bar below hero (input + inline button) -->
        <div class="hero-search">
            <form method="get" action="pages/posts.php">
                <input type="text" name="q" placeholder="Rechercher articles...">
                <button class="btn-principal search-btn" type="submit">Rechercher</button>
            </form>
        </div>

        <div class="section-titre">Nos <span>catégories</span></div>

        <section class="grille-cartes">
            <article class="carte">
                <img src="images/livre.jpg" alt="Livres">
                <div class="carte-body">
                    <h3>Livres</h3>
                    <p>Analyses et recommandations de livres soigneusement sélectionnés pour vous.</p>
                    <a class="lire-plus" href="pages/books.php">Découvrir →</a>
                    <?php if ($postsLivres): ?>
                        <div class="recent-post">
                            <?php foreach($postsLivres as $rp): ?>
                                <?php $isPrivate = !empty($rp['is_private']) && $rp['is_private']==1; $thumb = !empty($rp['thumbnail']) ? $rp['thumbnail'] : ''; $link = ($isPrivate && !isset($_SESSION['user'])) ? 'pages/register.php' : 'pages/post.php?id=' . intval($rp['id']); ?>
                                <div style="display:flex;gap:8px;margin-bottom:8px">
                                    <?php if($thumb): ?>
                                        <a href="<?php echo $link; ?>"><img src="<?php echo htmlspecialchars($thumb); ?>" style="width:80px;height:60px;object-fit:cover;border-radius:6px"></a>
                                    <?php endif; ?>
                                    <div>
                                        <h4 style="margin:0"><a href="<?php echo $link; ?>"><?php echo htmlspecialchars($rp['title']); ?></a></h4>
                                        <div style="font-size:0.85em;color:#666">Par <?php echo htmlspecialchars($rp['prenom'] ?: $rp['nom']); ?> — <?php echo htmlspecialchars($rp['created_at']); ?></div>
                                        <?php if (function_exists('is_owner') && is_owner()): ?>
                                            <div style="margin-top:6px">
                                                <a class="link-action" href="pages/edit_post.php?id=<?php echo intval($rp['id']); ?>">Modifier</a>
                                                <form method="post" action="pages/delete_post.php" style="display:inline">
                                                    <input type="hidden" name="id" value="<?php echo intval($rp['id']); ?>">
                                                    <input type="hidden" name="return" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">
                                                    <button type="submit" class="link-action link-delete" onclick="return confirm('Supprimer cet article ?');">Supprimer</button>
                                                </form>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <?php if($isPrivate && !isset($_SESSION['user'])): ?>
                                        <div style="margin-left:auto;color:var(--accent-error);font-weight:800">🔒</div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </article>

            <article class="carte">
                <img src="images/lifestyle.jpg" alt="Lifestyle">
                <div class="carte-body">
                    <h3>Lifestyle</h3>
                    <p>Récits de vie et conseils pratiques pour les jeunes en France.</p>
                    <a class="lire-plus" href="pages/lifestyle.php">Découvrir →</a>
                    <?php if ($postsLifestyle): ?>
                        <div class="recent-post">
                            <?php foreach($postsLifestyle as $rp): ?>
                                <?php $isPrivate = !empty($rp['is_private']) && $rp['is_private']==1; $thumb = !empty($rp['thumbnail']) ? $rp['thumbnail'] : ''; $link = ($isPrivate && !isset($_SESSION['user'])) ? 'pages/register.php' : 'pages/post.php?id=' . intval($rp['id']); ?>
                                <div style="display:flex;gap:8px;margin-bottom:8px;align-items:flex-start">
                                    <div style="flex:1">
                                        <h4 style="margin:0 0 6px"><a href="<?php echo $link; ?>"><?php echo htmlspecialchars($rp['title']); ?></a></h4>
                                        <div style="font-size:0.85em;color:#666">Par <?php echo htmlspecialchars($rp['prenom'] ?: $rp['nom']); ?> — <?php echo htmlspecialchars($rp['created_at']); ?></div>
                                        <p style="margin:6px 0"><?php echo nl2br(htmlspecialchars(substr($rp['content'],0,120))); ?><?php if(strlen($rp['content'])>120) echo '...'; ?></p>
                                        <?php if (function_exists('is_owner') && is_owner()): ?>
                                            <div style="margin-top:6px">
                                                <a class="link-action" href="pages/edit_post.php?id=<?php echo intval($rp['id']); ?>">Modifier</a>
                                                <form method="post" action="pages/delete_post.php" style="display:inline">
                                                    <input type="hidden" name="id" value="<?php echo intval($rp['id']); ?>">
                                                    <input type="hidden" name="return" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">
                                                    <button type="submit" class="link-action link-delete" onclick="return confirm('Supprimer cet article ?');">Supprimer</button>
                                                </form>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <?php if($isPrivate && !isset($_SESSION['user'])): ?>
                                        <div style="margin-left:auto;color:var(--accent-error);font-weight:800">🔒</div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </article>

            <!-- Premium card removed -->
        </section>
    </main>

    <footer>
        <p style="color:#fff">© 2024 DUO — Tous droits réservés</p>
    </footer>

    <script src="js/script.js"></script>
</body>
</html>