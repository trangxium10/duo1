<?php
try {
    $host = '127.0.0.1';
    $db = 'do1';
    $user = 'root';
    $pass = 'xungkhium10';
    $dsn = "mysql:host=$host;port=3306;dbname=$db;charset=utf8";
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die('Erreur base de données : ' . $e->getMessage());
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $plan = $_POST['plan'] ?? 'standard';

    if ($email) {
        $ins = $pdo->prepare('INSERT INTO premium (nom, prenom, email, telephone, plan, created_at) VALUES (?, ?, ?, ?, ?, ?)');
        $ins->execute([$nom, $prenom, $email, $telephone, $plan, date('c')]);
        $message = 'succes';
    } else {
        $message = 'erreur';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
    <title>DUO — Offre Premium</title>
</head>
<body>
    <header>
        <h1><span>DUO</span></h1>
    </header>

    <nav>
        <a href="../index.html">🏠 Accueil</a>
        <a href="books.html">📚 Livres</a>
        <a href="lifestyle.html">✨ Lifestyle</a>
        <a href="register.php">👤 Mon espace</a>
    </nav>

    <main>
        <div class="profil">
            <div class="profil-logo" style="font-size:36px">👑</div>
            <h2>Offre Premium</h2>
            <p class="sous-titre">Accédez à tous les contenus exclusifs DUO</p>

            <?php if ($message === 'succes'): ?>
                <div class="msg-succes">✅ Merci ! Votre inscription Premium a bien été enregistrée.</div>
            <?php elseif ($message === 'erreur'): ?>
                <div class="msg-erreur">Veuillez saisir un email valide.</div>
            <?php endif; ?>

            <div class="plan-select">
                <div class="plan-opt actif" onclick="selectPlan(this,'standard')">
                    <div class="plan-nom">Standard</div>
                    <div class="plan-prix">Gratuit</div>
                </div>
                <div class="plan-opt" onclick="selectPlan(this,'premium')">
                    <div class="plan-nom">✨ Premium</div>
                    <div class="plan-prix">9,99€/mois</div>
                </div>
            </div>

            <form method="post" action="formulaire.php" id="form-premium">
                <input type="hidden" name="plan" id="plan-value" value="standard">
                <label>Nom</label>
                <input type="text" name="nom">
                <label>Prénom</label>
                <input type="text" name="prenom">
                <label>Email</label>
                <input type="email" name="email" required>
                <label>Téléphone</label>
                <input type="tel" name="telephone">
                <button class="btn-premium-offre" type="submit" style="margin-top:20px">👑 S'inscrire Premium</button>
            </form>

            <div class="liens-retour" style="margin-top:20px;border:none;padding:0">
                <a class="lien-retour" href="../index.html">← Accueil</a>
            </div>
        </div>
    </main>

    <footer>
        <p style="color:#fff">© 2024 DUO — Tous droits réservés</p>
    </footer>

    <script src="../js/script.js"></script>
    <script>
    function selectPlan(el, val) {
        document.querySelectorAll('.plan-opt').forEach(o => o.classList.remove('actif'));
        el.classList.add('actif');
        document.getElementById('plan-value').value = val;
    }
    </script>
</body>
</html>
