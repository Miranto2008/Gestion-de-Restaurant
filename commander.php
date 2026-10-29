<?php
session_start();
require 'bd.php';

/* Vérifier que l'utilisateur est connecté */
if (!isset($_SESSION['nom'])) {
    header("Location: login.php");
    exit();
}

/* Vérifier que le plat existe */
if (!isset($_GET['id'])) {
    echo "Aucun plat sélectionné.";
    exit();
}

$id = $_GET['id'];

/* Récupérer le plat */
$sql = "SELECT * FROM plats WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$plat = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$plat) {
    echo "Plat introuvable.";
    exit();
}

/* Enregistrer la commande */
$message = '';
if (isset($_POST['envoyer'])) {
    $telephone = $_POST['telephone'];
    $adresse = $_POST['adresse'];
    $quantite = $_POST['quantite'];
    $total = $plat['prix'] * $quantite;
    
    // Récupérer l'ID de l'utilisateur connecté
    $sql_user = "SELECT id FROM utilisateurs WHERE nom = ?";
    $stmt_user = $pdo->prepare($sql_user);
    $stmt_user->execute([$_SESSION['nom']]);
    $user = $stmt_user->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        $utilisateur_id = $user['id'];
        
        $sql = "INSERT INTO commandes (utilisateur_id, plat_id, quantite, prix_total, adresse_livraison, telephone, statut) 
                VALUES (?, ?, ?, ?, ?, ?, 'en_attente')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $utilisateur_id,
            $plat['id'],
            $quantite,
            $total,
            $adresse,
            $telephone
        ]);
        
        $message = "✅ Commande enregistrée avec succès !";
        // Réinitialiser la quantité après commande
        $quantite_actuelle = 1;
        $total_actuel = $plat['prix'];
    } else {
        $message = "❌ Erreur : Utilisateur non trouvé.";
    }
}

// Récupérer la quantité actuelle (soit de la soumission, soit de la session)
if (isset($_POST['quantite'])) {
    $quantite_actuelle = (int)$_POST['quantite'];
    $total_actuel = $plat['prix'] * $quantite_actuelle;
} else {
    $quantite_actuelle = 1;
    $total_actuel = $plat['prix'];
}

// Récupérer les valeurs du formulaire (pour les conserver après soumission)
$telephone_value = isset($_POST['telephone']) ? $_POST['telephone'] : '';
$adresse_value = isset($_POST['adresse']) ? $_POST['adresse'] : '';
$quantite_value = isset($_POST['quantite']) ? $_POST['quantite'] : 1;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kalitsika - Commander</title>
    <link rel="stylesheet" href="commander.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="container">

    <!-- Header -->
    <header class="header-site">
        <h1>🍽️ Kalitsika</h1>
        <p class="subtitle">Passer une commande</p>
    </header>

    <!-- Message de confirmation -->
    <?php if ($message): ?>
        <div class="notification <?= strpos($message, '✅') !== false ? 'success' : 'error' ?>">
            <i class="fas <?= strpos($message, '✅') !== false ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
            <?= $message ?>
        </div>
    <?php endif; ?>

    <!-- Détails du plat -->
    <div class="plat-details">
        <div class="plat-image">
            <?php if ($plat['image'] != "" && file_exists("images/" . $plat['image'])): ?>
                <img src="images/<?= $plat['image'] ?>" alt="<?= htmlspecialchars($plat['nom']) ?>">
            <?php else: ?>
                <div class="plat-image-placeholder">
                    <i class="fas fa-utensils"></i>
                </div>
            <?php endif; ?>
        </div>
        <div class="plat-info">
            <h2><?= htmlspecialchars($plat['nom']) ?></h2>
            <p class="plat-description"><?= htmlspecialchars($plat['description'] ?? 'Aucune description') ?></p>
            <div class="plat-meta">
                <span class="plat-price"><?= number_format($plat['prix'], 0, ',', ' ') ?> Ar</span>
                <?php if ($plat['categorie']): ?>
                    <span class="plat-categorie"><?= htmlspecialchars($plat['categorie']) ?></span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Formulaire de commande -->
    <form method="POST" class="commande-form">
        
        <div class="form-group">
            <label for="plat"><i class="fas fa-utensils"></i> Nom du plat</label>
            <input type="text" id="plat" value="<?= htmlspecialchars($plat['nom']) ?>" readonly>
        </div>

        <div class="form-group">
            <label for="prix"><i class="fas fa-money-bill-wave"></i> Prix unitaire</label>
            <input type="text" id="prix" value="<?= number_format($plat['prix'], 0, ',', ' ') ?> Ar" readonly>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="quantite"><i class="fas fa-shopping-cart"></i> Quantité</label>
                <input type="number" id="quantite" name="quantite" value="<?= $quantite_value ?>" min="1" required>
            </div>
            <div class="form-group">
                <label for="total"><i class="fas fa-calculator"></i> Total</label>
                <input type="text" id="total" value="<?= number_format($total_actuel, 0, ',', ' ') ?> Ar" readonly>
            </div>
        </div>

        <div class="form-group">
            <label for="telephone"><i class="fas fa-phone"></i> Numéro de téléphone</label>
            <input type="tel" id="telephone" name="telephone" value="<?= htmlspecialchars($telephone_value) ?>" placeholder="Ex: 032 12 345 67" required>
        </div>

        <div class="form-group">
            <label for="adresse"><i class="fas fa-map-marker-alt"></i> Adresse de livraison</label>
            <input type="text" id="adresse" name="adresse" value="<?= htmlspecialchars($adresse_value) ?>" placeholder="Ex: Antananarivo, Ambohijatovo" required>
        </div>

        <div class="form-actions">
            <!-- Bouton pour mettre à jour le total sans envoyer la commande -->
            <button type="submit" name="mettre_a_jour" class="btn-update">
                <i class="fas fa-sync-alt"></i> Mettre à jour le total
            </button>
            <button type="submit" name="envoyer" class="btn-submit">
                <i class="fas fa-check"></i> Commander maintenant
            </button>
            <a href="acceuil.php" class="btn-cancel">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>

        <!-- Champ caché pour savoir si on met à jour -->
        <input type="hidden" name="id_plat" value="<?= $plat['id'] ?>">

    </form>

    <!-- Affichage du détail du calcul -->
    <div class="detail-calcul">
        <p>
            <i class="fas fa-info-circle"></i>
            Prix unitaire : <strong><?= number_format($plat['prix'], 0, ',', ' ') ?> Ar</strong>
            × Quantité : <strong><?= $quantite_actuelle ?></strong>
            = Total : <strong style="color: #f0c040;"><?= number_format($total_actuel, 0, ',', ' ') ?> Ar</strong>
        </p>
    </div>

</div>

</body>
</html>