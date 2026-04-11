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
    $submit_action = $_POST['submit_action'] ?? '';

    // If user is logged in: handle profile update
    if (isset($_SESSION['user']) && $_SESSION['user'] && $submit_action === 'update_profile') {
        $userId = $_SESSION['user']['id'];

        if ($submit_action === 'update_profile') {
            $nom = trim($_POST['nom'] ?? '');
            $prenom = trim($_POST['prenom'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $newpass = $_POST['motdepasse'] ?? '';

            if ($email) {
                // Check email uniqueness (exclude current user)
                $chk = $pdo->prepare('SELECT id FROM utilisateurs WHERE email = :email AND id != :id');
                $chk->execute([':email' => $email, ':id' => $userId]);
                $other = $chk->fetch(PDO::FETCH_ASSOC);
                if ($other) {
                    $erreur = 'Cet email est déjà utilisé par un autre compte.';
                } else {
                    if ($newpass !== '') {
                        // Store raw new password (insecure) to match user's request
                        $upd = $pdo->prepare('UPDATE utilisateurs SET nom = :nom, prenom = :prenom, email = :email, motdepasse = :motdepasse WHERE id = :id');
                        $upd->execute([':nom'=>$nom, ':prenom'=>$prenom, ':email'=>$email, ':motdepasse'=>$newpass, ':id'=>$userId]);
                    } else {
                        $upd = $pdo->prepare('UPDATE utilisateurs SET nom = :nom, prenom = :prenom, email = :email WHERE id = :id');
                        $upd->execute([':nom'=>$nom, ':prenom'=>$prenom, ':email'=>$email, ':id'=>$userId]);
                    }
                    $stmt = $pdo->prepare('SELECT * FROM utilisateurs WHERE id = :id');
                    $stmt->execute([':id' => $userId]);
                    $_SESSION['user'] = $stmt->fetch(PDO::FETCH_ASSOC);
                    $success = 'Profil mis à jour avec succès.';
                }
            } else {
                $erreur = 'Veuillez saisir un email.';
            }
        }

        // (Premium feature removed) no action here.

    } else {
        // Not logged: either register or login
        $nom = trim($_POST['nom'] ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $motdepasse = $_POST['motdepasse'] ?? '';

        if (!$email || !$motdepasse) {
            $erreur = 'Veuillez fournir un email et un mot de passe.';
        } else {
            $stmt = $pdo->prepare('SELECT * FROM utilisateurs WHERE email = :email');
            $stmt->execute([':email' => $email]);
            $exist = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($submit_action === 'login') {
                // Accept both bcrypt-hashed passwords and plain-text stored passwords
                if ($exist && (password_verify($motdepasse, $exist['motdepasse']) || $motdepasse === $exist['motdepasse'])) {
                    $_SESSION['user'] = $exist;
                    header('Location: register.php');
                    exit;
                } else {
                    $erreur = 'Informations de connexion incorrectes.';
                }
            } else {
                // register
                if ($exist) {
                    $erreur = 'Compte déjà enregistré. <a href="register.php">Se connecter</a>';
                } else {
                    // Store the raw password as requested (insecure).
                    $stored = $motdepasse;
                    $ins = $pdo->prepare('INSERT INTO utilisateurs (nom, prenom, email, motdepasse, created_at) VALUES (:nom, :prenom, :email, :motdepasse, :created_at)');
                    $ins->execute([':nom'=>$nom,':prenom'=>$prenom,':email'=>$email,':motdepasse'=>$stored,':created_at'=>date('c')]);
                    $id = $pdo->lastInsertId();
                    $stmt = $pdo->prepare('SELECT * FROM utilisateurs WHERE id = :id');
                    $stmt->execute([':id' => $id]);
                    $_SESSION['user'] = $stmt->fetch(PDO::FETCH_ASSOC);
                    header('Location: register.php');
                    exit;
                }
            }
        }
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
    <a href="../index.php">Accueil</a>
    <a href="register.php">Mon espace</a>
</nav>
<main>
<?php if (isset($_SESSION['user'])): ?>
<div class="profil">
<div class="profil-logo">DUO</div>
<h2>Bonjour, <?php echo htmlspecialchars($_SESSION['user']['prenom'] ?: $_SESSION['user']['nom']); ?></h2>
<p class="sous-titre">Gerez votre espace personnel</p>
<?php if (!empty($success)): ?>
<div class="msg-succes"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>
<?php if (!empty($erreur)): ?>
<div class="msg-erreur"><?php echo $erreur; ?></div>
<?php endif; ?>
<!-- Profile edit form -->
<form method="post" action="register.php">
    <input type="hidden" name="submit_action" value="update_profile">
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
<!-- Premium feature removed -->
<div class="liens-retour" style="margin-top:20px;border:none;padding:0">
<a class="lien-retour" href="?action=logout">Se deconnecter</a>
<a class="lien-retour" href="../index.php">Accueil</a>
</div>
</div>
<div style="text-align:center;margin-top:10px">
    <a class="lien-retour" href="profile.php">Voir mon profil détaillé</a>
</div>
<?php else: ?>
<div class="profil">
<div class="profil-logo">DUO</div>
<h2>Mon espace</h2>
<p class="sous-titre">Creez un compte ou connectez-vous</p>
<?php if (!empty($erreur)): ?>
<div class="msg-erreur"><?php echo $erreur; ?></div>
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
    <div style="display:flex;gap:8px;margin-top:10px">
    <button class="btn-principal" type="submit" name="submit_action" value="register">S'inscrire</button>
    <button class="btn-principal" type="submit" name="submit_action" value="login">Se connecter</button>
</div>
</form>
<div class="divider">- ou -</div>
<div class="liens-retour" style="margin-top:20px;border:none;padding:0">
<a class="lien-retour" href="../index.php">Accueil</a>
</div>
</div>
<?php endif; ?>
</main>
<footer><p style="color:#fff">2024 DUO</p></footer>
<script src="../js/script.js"></script>
</body>
</html>