<?php
require 'bd.php';

if (isset($_POST['envoyer'])) {

    $nom = $_POST['nom'];
    $email = $_POST['email'];
    $mdp = $_POST['mdp'];
    $cmdp = $_POST['cmdp'];

    if ($mdp != $cmdp) {
        echo "<div class='error-message'>Les mots de passe ne correspondent pas.</div>";
    } else {

        $sql = "INSERT INTO utilisateurs (nom, email, pass)
                VALUES (:nom, :email, :mdp)";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':nom' => $nom,
            ':email' => $email,
            ':mdp' => $mdp
        ]);

        header("Location: login.php");
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kalitsika - Inscription</title>
    <link rel="stylesheet" href="pp.css">
</head>

<body>

<div class="container">
    <!-- Brand / Logo -->
    <div class="brand">
        <h1>🍽️ Kalitsika</h1>
        <p class="subtitle">Gestion de restaurant</p>
    </div>

    <!-- Formulaire -->
    <form action="" method="POST" class="register-form">
        
        <div class="form-group">
            <label for="nom">👤 Nom complet</label>
            <input type="text" id="nom" name="nom" placeholder="Entrez votre nom" required>
            <span class="input-line"></span>
        </div>

        <div class="form-group">
            <label for="email">📧 Adresse email</label>
            <input type="email" id="email" name="email" placeholder="exemple@email.com" required>
            <span class="input-line"></span>
        </div>

        <div class="form-group">
            <label for="mdp">🔒 Mot de passe</label>
            <input type="password" id="mdp" name="mdp" placeholder="Minimum 6 caractères" required>
            <span class="input-line"></span>
        </div>

        <div class="form-group">
            <label for="cmdp">✅ Confirmer le mot de passe</label>
            <input type="password" id="cmdp" name="cmdp" placeholder="Répétez le mot de passe" required>
            <span class="input-line"></span>
        </div>

        <button type="submit" name="envoyer" class="btn-submit">
            <span class="btn-text">Créer mon compte</span>
            <span class="btn-icon">→</span>
        </button>

    </form>

    <!-- Lien de connexion -->
    <div class="login-link">
        <p>Déjà inscrit ? <a href="login.php">Se connecter</a></p>
    </div>
</div>

</body>
</html>