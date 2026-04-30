<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: register.php');
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

$userId = $_SESSION['user']['id'];
$erreur = '';
$success = '';

// Ensure telephone column exists (add if missing)
try {
    $cols = $pdo->query("SHOW COLUMNS FROM utilisateurs LIKE 'telephone'")->fetchAll();
    if (count($cols) === 0) {
        $pdo->exec("ALTER TABLE utilisateurs ADD telephone VARCHAR(50) NULL AFTER email");
    }
} catch (Exception $e) {
    // ignore alter errors but continue
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'update') {
        $nom = trim($_POST['nom'] ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telephone = trim($_POST['telephone'] ?? '');
        $motdepasse = $_POST['motdepasse'] ?? '';

        if (!$email) {
            $erreur = 'Email requis.';
        } else {
            // check email not used by another
            $chk = $pdo->prepare('SELECT id FROM utilisateurs WHERE email = :email AND id != :id');
            $chk->execute([':email'=>$email, ':id'=>$userId]);
            if ($chk->fetch(PDO::FETCH_ASSOC)) {
                $erreur = 'Email déjà utilisé par un autre compte.';
            } else {
                if ($motdepasse !== '') {
                    // store raw password as requested
                    $upd = $pdo->prepare('UPDATE utilisateurs SET nom = :nom, prenom = :prenom, email = :email, motdepasse = :motdepasse, telephone = :telephone WHERE id = :id');
                    $upd->execute([':nom'=>$nom,':prenom'=>$prenom,':email'=>$email,':motdepasse'=>$motdepasse,':telephone'=>$telephone,':id'=>$userId]);
                } else {
                    $upd = $pdo->prepare('UPDATE utilisateurs SET nom = :nom, prenom = :prenom, email = :email, telephone = :telephone WHERE id = :id');
                    $upd->execute([':nom'=>$nom,':prenom'=>$prenom,':email'=>$email,':telephone'=>$telephone,':id'=>$userId]);
                }
                $stmt = $pdo->prepare('SELECT * FROM utilisateurs WHERE id = :id');
                $stmt->execute([':id' => $userId]);
                $_SESSION['user'] = $stmt->fetch(PDO::FETCH_ASSOC);
                $success = 'Profil mis à jour.';
            }
        }
    } elseif ($action === 'delete') {
        // Delete user account (premium feature removed)
        $del2 = $pdo->prepare('DELETE FROM utilisateurs WHERE id = :id');
        $del2->execute([':id'=>$userId]);
        session_destroy();
        header('Location: ../index.php');
        exit;
    }
}

$stmt = $pdo->prepare('SELECT * FROM utilisateurs WHERE id = :id');
$stmt->execute([':id' => $userId]);
$userRow = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="../css/style.css">
    <title>Profil — Mon espace</title>
</head>
<body>
<header><h1><span>DUO</span></h1></header>
<nav>
    <a href="../index.php">Accueil</a>
    <a href="register.php">Mon espace</a>
</nav>
<main>
    <div class="profil">
        <div class="profil-logo">DUO</div>
        <h2>Profil de <?php echo htmlspecialchars($userRow['prenom'] ?: $userRow['nom']); ?></h2>
        <?php if ($erreur): ?><div class="msg-erreur"><?php echo htmlspecialchars($erreur); ?></div><?php endif; ?>
        <?php if ($success): ?><div class="msg-succes"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>

        <!-- Display current info -->
        <div id="profile-view">
            <p><strong>Nom:</strong> <?php echo htmlspecialchars($userRow['nom'] ?? ''); ?></p>
            <p><strong>Prénom:</strong> <?php echo htmlspecialchars($userRow['prenom'] ?? ''); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($userRow['email'] ?? ''); ?></p>
            <p><strong>Téléphone:</strong> <?php echo htmlspecialchars($userRow['telephone'] ?? ''); ?></p>
            <p><strong>Mot de passe:</strong> <?php echo htmlspecialchars($userRow['motdepasse'] ?? ''); ?></p>
            <div style="margin-top:12px">
                <button id="btn-edit" class="btn-principal" type="button">Modifier les informations</button>
            </div>
        </div>

        <!-- Hidden edit form -->
        <form id="profile-edit" method="post" action="profile.php" style="display:none;margin-top:10px">
            <input type="hidden" name="action" value="update">
            <label>Nom</label>
            <input type="text" name="nom" id="input-nom" value="<?php echo htmlspecialchars($userRow['nom'] ?? ''); ?>">
            <label>Prénom</label>
            <input type="text" name="prenom" id="input-prenom" value="<?php echo htmlspecialchars($userRow['prenom'] ?? ''); ?>">
            <label>Email</label>
            <input type="email" name="email" id="input-email" value="<?php echo htmlspecialchars($userRow['email'] ?? ''); ?>" required>
            <label>Téléphone</label>
            <input type="tel" name="telephone" id="input-telephone" value="<?php echo htmlspecialchars($userRow['telephone'] ?? ''); ?>">
            <label>Mot de passe</label>
            <input type="text" name="motdepasse" id="input-motdepasse" placeholder="Laisser vide pour garder actuel">
            <div style="margin-top:12px;display:flex;gap:8px">
                <button class="btn-principal" type="submit">Enregistrer</button>
                <button id="btn-cancel" class="btn-principal" type="button">Annuler</button>
            </div>
        </form>

        <script>
        document.getElementById('btn-edit').addEventListener('click', function(){
            document.getElementById('profile-view').style.display = 'none';
            document.getElementById('profile-edit').style.display = 'block';
        });
        document.getElementById('btn-cancel').addEventListener('click', function(){
            document.getElementById('profile-edit').style.display = 'none';
            document.getElementById('profile-view').style.display = 'block';
        });
        </script>

        <form method="post" action="profile.php" onsubmit="return confirm('Voulez-vous supprimer votre compte ? Cette action est irréversible.');">
            <input type="hidden" name="action" value="delete">
            <button class="btn-principal" type="submit" style="margin-top:10px">Supprimer mon compte</button>
        </form>

        <div class="liens-retour" style="margin-top:20px;border:none;padding:0">
            <a class="lien-retour" href="register.php">← Retour Mon espace</a>
            <a class="lien-retour" href="../index.php">Accueil</a>
        </div>
    </div>
</main>
<footer><p style="color:#fff">2026 DUO</p></footer>
</body>
</html>
