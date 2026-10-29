<?php
require 'bd.php';

session_start();

if (isset($_POST['connexion'])) {

    $email = $_POST['email'];
    $mdp = $_POST['mdp'];

    // Connexion administrateur
    if ($email == "admin@gmail.com" && $mdp == "1234") {

        $_SESSION['admin'] = "admin";

        header("Location: admin.php");
        exit();

    } else {

        // Vérification dans la base de données
        $sql = "SELECT * FROM utilisateurs WHERE email = :email";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':email' => $email
        ]);

        $resultat = $stmt->fetch();

        if ($resultat && $mdp == $resultat['pass']) {

            $_SESSION['nom'] = $resultat['nom'];
            $_SESSION['email'] = $resultat['email'];

            header("Location: acceuil.php");
            exit();

        } else {

            echo "<div class='error-message'>❌ Email ou mot de passe incorrect.</div>";

        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kalitsika - Connexion</title>
    <link rel="stylesheet" href="pp.css">
</head>

<body>

<div class="container">
    <!-- Brand / Logo -->
    <div class="brand">
        <h1>🍽️ Kalitsika</h1>
        <p class="subtitle">Gestion de restaurant</p>
    </div>

    <!-- Formulaire de connexion -->
    <form action="login.php" method="POST" class="login-form">
        
        <div class="form-group">
            <label for="email">📧 Adresse email</label>
            <input type="email" id="email" name="email" placeholder="exemple@email.com" required>
            <span class="input-line"></span>
        </div>

        <div class="form-group">
            <label for="mdp">🔒 Mot de passe</label>
            <input type="password" id="mdp" name="mdp" placeholder="Entrez votre mot de passe" required>
            <span class="input-line"></span>
        </div>

        <button type="submit" name="connexion" class="btn-submit">
            <span class="btn-text">Se connecter</span>
            <span class="btn-icon">→</span>
        </button>

    </form>

    <!-- Liens -->
    <div class="login-link">
        <p>Pas encore inscrit ? <a href="index.php">Créer un compte</a></p>
    </div>
</div>

</body>
</html>