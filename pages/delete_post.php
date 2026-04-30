<?php
session_start();
$id = intval($_POST['id'] ?? 0);
$return = $_POST['return'] ?? 'posts.php';
if (!$id) {
    header('Location: ' . $return);
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
    header('Location: ' . $return);
    exit;
}

// fetch post to find thumbnail and check ownership
$stmt = $pdo->prepare('SELECT user_id, thumbnail FROM posts WHERE id = :id');
$stmt->execute([':id'=>$id]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);
if ($post) {
    if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id']) || $_SESSION['user']['id'] != $post['user_id']) {
        header('HTTP/1.1 403 Forbidden');
        echo 'Accès refusé.';
        exit;
    }
    if (!empty($post['thumbnail'])) {
        $file = __DIR__ . '/../' . ltrim($post['thumbnail'], '/');
        if (file_exists($file)) @unlink($file);
    }
    $del = $pdo->prepare('DELETE FROM posts WHERE id = :id');
    $del->execute([':id'=>$id]);
}

header('Location: ' . $return);
exit;
