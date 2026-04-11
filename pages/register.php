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
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: register.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $motdepasse = $_POST['motdepasse'] ?? '';
    if ($email && $motdepasse) {
        $stmt = $pdo->prepare('SELECT * FROM utilisateurs WHERE email = :email');
        $stmt->execute([':email' => $email]);
        $exist = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($exist) {
            if (password_verify($motdepasse, $exist['motdepasse'])) {
                $_SESSION['user'] = $exist;
                header('Location: register.php');
                exit;
            } else {
                $erreur = 'Mot de passe incorrect.';
            }
        } else {
            $hash = password_hash($motdepasse, PASSWORD_DEFAULT);
            $ins = $pdo->prepare('INSERT INTO utilisateurs (nom, prenom, email, motdepasse, created_at) VALUES (:nom, :prenom, :email, :motdepasse, :created_at)');
            $ins->execute([':nom'=>$nom,':prenom'=>$prenom,':email'=>$email,':motdepasse'=>$hash,':created_at'=>date('c')]);
            $id = $pdo->lastInsertId();
            $stmt = $pdo->prepare('SELECT * FROM utilisateurs WHERE id = :id');
            $stmt->execute([':id' => $id]);
            $_SESSION['user'] = $stmt->fetch(PDO::FETCH_ASSOC);
            header('Location: register.php');
            exit;
        }
    } else {
        $erreur = 'Veuillez fournir un email et un mot de passe.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="../css/style.css">
<title>DUO - Mon espace</title>
</head>
<body>
<header><h1><span>DUO</span></h1></header>
<nav>
<a href="../index.html">Accueil</a>
<a href="books.html">Livres</a>
<a href="lifestyle.html">Lifestyle</a>
<a href="register.php">Mon espace</a>
</nav>
<main>
<?php if (isset($_SESSION['user'])): ?>
<div class="profil">
<div class="profil-logo">DUO</div>
<h2>Bonjour, <?php echo htmlspecialchars($_SESSION['user']['prenom'] ?: $_SESSION['user']['nom']); ?></h2>
<p class="sous-titre">Gerez votre espace personnel</p>
<form method="post" action="register.php">
<label>Nom</label>
<input type="text" name="nom" value="<?php echo htmlspecialchars($_SESSION['user']['nom']); ?>">
<label>Prenom</label>
<input type="text" name="prenom" value="<?php echo htmlspecialchars($_SESSION['user']['prenom']); ?>">
<label>Email</label>
<input type="email" name="email" value="<?php echo htmlspecialchars($_SESSION['user']['email']); ?>">
<label>Nouveau mot de passe</label>
<input type="password" name="motdepasse" placeholder="Laisser vide pour garder actuel">
<button class="btn-principal" type="submit">Enregistrer</button>
</form>
<div class="liens-retour" style="margin-top:20px;border:none;padding:0">
<a class="lien-retour" href="?action=logout">Se deconnecter</a>
<a class="lien-retour" href="../index.html">Accueil</a>
</div>
</div>
<?php else: ?>
<div class="profil">
<div class="profil-logo">DUO</div>
<h2>Mon espace</h2>
<p class="sous-titre">Creez un compte ou connectez-vous</p>
<?php if (!empty($erreur)): ?>
<div class="msg-erreur"><?php echo htmlspecialchars($erreur); ?></div>
<?php endif; ?>
<form method="post" action="register.php">
<label>Nom</label>
<input type="text" name="nom">
<label>Prenom</label>
<input type="text" name="prenom">
<label>Email</label>
<input type="email" name="email" required>
<label>Mot de passe</label>
<input type="password" name="motdepasse" required>
<button class="btn-principal" type="submit">S inscrire / Se connecter</button>
</form>
<div class="divider">- ou -</div>
<button class="btn-premium-offre btn-principal-offre">S inscrire Premium</button>
<div class="liens-retour" style="margin-top:20px;border:none;padding:0">
<a class="lien-retour" href="../index.html">Accueil</a>
</div>
</div>
<?php endif; ?>
</main>
<footer><p style="color:#fff">2024 DUO</p></footer>
<script src="../js/script.js"></script>
</body>
</html>