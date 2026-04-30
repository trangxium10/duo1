<?php
session_start();
session_start();

$id = intval($_GET['id'] ?? $_POST['id'] ?? 0);
if (!$id) {
    header('Location: posts.php');
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

// fetch existing post
$stmt = $pdo->prepare('SELECT * FROM posts WHERE id = :id');
$stmt->execute([':id'=>$id]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$post) { header('Location: posts.php'); exit; }

// ownership: only post author can edit
if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id']) || $_SESSION['user']['id'] != $post['user_id']) {
    header('HTTP/1.1 403 Forbidden');
    echo 'Accès refusé.';
    exit;
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $category = $_POST['category'] ?? 'critiques';
    $is_private = isset($_POST['is_private']) && $_POST['is_private']==='1' ? 1 : 0;

    // handle new thumbnail upload
    $thumbnailPath = $post['thumbnail'];
    if (!empty($_FILES['thumbnail']['name'])) {
        $f = $_FILES['thumbnail'];
        if ($f['error'] === UPLOAD_ERR_OK) {
            $mime = mime_content_type($f['tmp_name']);
            if (strpos($mime, 'image/') === 0) {
                $ext = pathinfo($f['name'], PATHINFO_EXTENSION);
                $name = uniqid('thumb_', true) . '.' . $ext;
                $uploadDir = __DIR__ . '/../uploads';
                if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);
                $dest = $uploadDir . '/' . $name;
                if (move_uploaded_file($f['tmp_name'], $dest)) {
                    // remove old file
                    if (!empty($post['thumbnail'])) {
                        $old = __DIR__ . '/../' . ltrim($post['thumbnail'], '/');
                        if (file_exists($old)) @unlink($old);
                    }
                    $thumbnailPath = 'uploads/' . $name;
                }
            }
        }
    }

    // allow removing current thumbnail
    if (isset($_POST['remove_thumbnail']) && $_POST['remove_thumbnail'] === '1') {
        if (!empty($post['thumbnail'])) {
            $old = __DIR__ . '/../' . ltrim($post['thumbnail'], '/');
            if (file_exists($old)) @unlink($old);
        }
        $thumbnailPath = null;
    }

    if ($title && $content) {
        $upd = $pdo->prepare('UPDATE posts SET title=:title, content=:content, category=:category, thumbnail=:thumbnail, is_private=:is_private WHERE id=:id');
        $upd->execute([
            ':title'=>$title,
            ':content'=>$content,
            ':category'=>$category,
            ':thumbnail'=>$thumbnailPath,
            ':is_private'=>$is_private,
            ':id'=>$id
        ]);
        // redirect to post page
        header('Location: post.php?id=' . $id);
        exit;
    } else {
        $message = 'Veuillez saisir un titre et un contenu.';
    }
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="../css/style.css">
    <title>Modifier article</title>
</head>
<body>
<header><h1><span>DUO</span></h1></header>
<nav>
    <a href="../index.php">Accueil</a>
    <a href="posts.php">Articles</a>
    <a href="register.php">Mon espace</a>
</nav>
<main>
    <div class="profil">
        <h2>Modifier l'article</h2>
        <?php if ($message): ?><div class="msg-erreur"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
        <form method="post" action="edit_post.php" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo intval($id); ?>">
            <label>Titre</label>
            <input type="text" name="title" value="<?php echo htmlspecialchars($post['title']); ?>" required>
            <label>Catégorie</label>
            <select name="category">
                <option value="critiques" <?php if($post['category']==='critiques') echo 'selected'; ?>>Livres</option>
                <option value="histoires" <?php if($post['category']==='histoires') echo 'selected'; ?>>Lifestyle</option>
            </select>
            <label>Thumbnail (image)</label>
            <?php if (!empty($post['thumbnail'])): ?>
                <div style="margin-bottom:8px"><img src="../<?php echo htmlspecialchars(ltrim($post['thumbnail'],'/')); ?>" style="max-width:140px;border-radius:6px"></div>
                <label style="display:block;max-width:520px;margin:0 auto 8px"><input type="checkbox" name="remove_thumbnail" value="1"> Supprimer l'image actuelle</label>
            <?php endif; ?>
            <input type="file" name="thumbnail" accept="image/*">
            <label>Visibilité</label>
            <select name="is_private">
                <option value="0" <?php if(empty($post['is_private'])) echo 'selected'; ?>>Public</option>
                <option value="1" <?php if(!empty($post['is_private'])) echo 'selected'; ?>>Privé</option>
            </select>
            <label>Contenu</label>
            <textarea name="content" rows="10" required><?php echo htmlspecialchars($post['content']); ?></textarea>
            <button class="btn-principal" type="submit">Enregistrer les modifications</button>
        </form>
        <div class="liens-retour" style="margin-top:20px;border:none;padding:0">
            <a class="lien-retour" href="post.php?id=<?php echo intval($id); ?>">← Retour à l'article</a>
        </div>
    </div>
</main>
</body>
</html>
