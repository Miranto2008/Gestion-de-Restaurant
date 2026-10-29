<?php
session_start();
require 'bd.php';

if (!isset($_SESSION['admin'])) {
    header("Location: connexion.php");
    exit();
}

/* Modifier le statut */
if (isset($_POST['modifier'])) {
    $sql = "UPDATE commandes SET statut = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $_POST['statut'],
        $_POST['commande_id']
    ]);
    header("Location: admin.php?success=Statut modifié avec succès");
    exit();
}

/* Supprimer un utilisateur ET ses commandes */
if (isset($_POST['supprimer'])) {
    $utilisateur_id = $_POST['utilisateur_id'];
    
    // Supprimer d'abord les commandes de l'utilisateur
    $sql = "DELETE FROM commandes WHERE utilisateur_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$utilisateur_id]);
    
    // Puis supprimer l'utilisateur
    $sql = "DELETE FROM utilisateurs WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$utilisateur_id]);
    
    header("Location: admin.php?success=Utilisateur et ses commandes supprimés avec succès");
    exit();
}

/* Déconnexion */
if (isset($_GET['deconnexion'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Statistiques
$stmt = $pdo->query("SELECT COUNT(*) FROM utilisateurs");
$clients = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM commandes WHERE statut = 'en_attente'");
$attente = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM commandes WHERE statut = 'en_preparation'");
$preparation = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM commandes WHERE statut = 'terminee'");
$terminee = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM commandes WHERE statut = 'annulee'");
$annulee = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM plats");
$plats = $stmt->fetchColumn();

/* Récupérer les utilisateurs et commandes */
$sql = "
    SELECT
        utilisateurs.id AS utilisateur_id,
        utilisateurs.nom,
        utilisateurs.email,
        commandes.id AS commande_id,
        commandes.date_commande,
        commandes.statut,
        commandes.prix_total AS prix_total,
        commandes.quantite,
        commandes.telephone,
        commandes.adresse_livraison,
        plats.nom AS plat_nom
    FROM utilisateurs
    LEFT JOIN commandes ON commandes.utilisateur_id = utilisateurs.id
    LEFT JOIN plats ON plats.id = commandes.plat_id
    ORDER BY utilisateurs.id DESC, commandes.id DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$donnees = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* Choix de la liste */
$liste = $_GET['liste'] ?? 'tous';
$success = $_GET['success'] ?? '';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kalitsika - Administration</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="admin-container">

    <!-- ========== SIDEBAR ========== -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <h2>🍽️ Kalitsika</h2>
            <span class="brand-sub">Administration</span>
        </div>
        <nav class="sidebar-nav">
            <a href="#dashboard" class="active">
                <i class="fas fa-chart-pie"></i>
                <span>Tableau de bord</span>
            </a>
            <a href="#utilisateurs">
                <i class="fas fa-users"></i>
                <span>Utilisateurs</span>
            </a>
            <a href="ajouter.php">
                <i class="fas fa-plus-circle"></i>
                <span>Gestion des plats</span>
            </a>
            <a href="?deconnexion=1" class="logout" onclick="return confirm('Voulez-vous vraiment vous déconnecter ?')">
                <i class="fas fa-sign-out-alt"></i>
                <span>Déconnexion</span>
            </a>
        </nav>
    </aside>

    <!-- ========== MAIN CONTENT ========== -->
    <main class="main-content">

        <!-- Header -->
        <header class="header">
            <div class="header-left">
                <h1><i class="fas fa-tachometer-alt"></i> Tableau de bord</h1>
                <p class="header-sub">Gestion du restaurant Kalitsika</p>
            </div>
            <div class="header-user">
                <i class="fas fa-user-circle"></i>
                <span>Bienvenue, <strong><?php echo htmlspecialchars($_SESSION['admin']); ?></strong></span>
            </div>
        </header>

        <!-- Notification -->
        <?php if ($success): ?>
            <div class="notification success">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <!-- ========== STATISTIQUES ========== -->
        <section class="stats-section" id="dashboard">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= $clients ?></h3>
                        <p>Clients</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon yellow">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= $attente ?></h3>
                        <p>En attente</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon orange">
                        <i class="fas fa-spinner"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= $preparation ?></h3>
                        <p>En préparation</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= $terminee ?></h3>
                        <p>Terminées</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon red">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= $annulee ?></h3>
                        <p>Annulées</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon purple">
                        <i class="fas fa-utensils"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= $plats ?></h3>
                        <p>Plats</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ========== FILTRE ========== -->
        <section class="filter-section">
            <div class="filter-container">
                <form method="GET" class="filter-form">
                    <label for="liste"><i class="fas fa-filter"></i> Filtrer par :</label>
                    <select name="liste" id="liste" class="filter-select">
                        <option value="tous" <?= $liste == 'tous' ? 'selected' : '' ?> class="opt-tous">
                            📋 Tous les utilisateurs
                        </option>
                        <option value="sans_commande" <?= $liste == 'sans_commande' ? 'selected' : '' ?> class="opt-sans">
                            🚫 Sans commande
                        </option>
                        <option value="en_attente" <?= $liste == 'en_attente' ? 'selected' : '' ?> class="opt-attente">
                            ⏳ En attente
                        </option>
                        <option value="en_preparation" <?= $liste == 'en_preparation' ? 'selected' : '' ?> class="opt-preparation">
                            🔧 En préparation
                        </option>
                        <option value="terminee" <?= $liste == 'terminee' ? 'selected' : '' ?> class="opt-terminee">
                            ✅ Terminées
                        </option>
                        <option value="annulee" <?= $liste == 'annulee' ? 'selected' : '' ?> class="opt-annulee">
                            ❌ Annulées
                        </option>
                    </select>
                    <button type="submit" class="btn-filter">
                        <i class="fas fa-search"></i> Filtrer
                    </button>
                </form>
                <div class="filter-info">
                    <i class="fas fa-info-circle"></i>
                    <span>Affichage : <strong><?= ucfirst(str_replace('_', ' ', $liste)) ?></strong></span>
                </div>
            </div>
        </section>

        <!-- ========== TABLEAU ========== -->
        <section class="table-section" id="utilisateurs">
            <div class="table-header">
                <h2><i class="fas fa-list"></i> Liste des utilisateurs</h2>
                <span class="table-count">
                    <i class="fas fa-users"></i>
                    <?php 
                        $count = 0;
                        $users_affichés = [];
                        foreach ($donnees as $user) {
                            if (in_array($user['utilisateur_id'], $users_affichés)) continue;
                            $afficher = false;
                            if ($liste == 'tous') $afficher = true;
                            if ($liste == 'sans_commande' && !$user['commande_id']) $afficher = true;
                            if ($user['statut'] == $liste) $afficher = true;
                            if ($afficher) {
                                $count++;
                                $users_affichés[] = $user['utilisateur_id'];
                            }
                        }
                        echo $count;
                    ?> utilisateur(s)
                </span>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th><i class="fas fa-hashtag"></i> ID</th>
                            <th><i class="fas fa-user"></i> Nom</th>
                            <th><i class="fas fa-envelope"></i> Email</th>
                            <th><i class="fas fa-utensils"></i> Plat(s)</th>
                            <th><i class="fas fa-phone"></i> Téléphone</th>
                            <th><i class="fas fa-map-marker-alt"></i> Adresse</th>
                            <th><i class="fas fa-money-bill-wave"></i> Total</th>
                            <th><i class="fas fa-tasks"></i> Statut(s)</th>
                            <th><i class="fas fa-trash"></i> Supprimer</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $has_results = false;
                        $users_traités = [];
                        
                        foreach ($donnees as $user): 
                            if (in_array($user['utilisateur_id'], $users_traités)) continue;
                            
                            $afficher = false;
                            if ($liste == 'tous') $afficher = true;
                            if ($liste == 'sans_commande' && !$user['commande_id']) $afficher = true;
                            if ($user['statut'] == $liste) $afficher = true;
                            
                            if (!$afficher) continue;
                            $has_results = true;
                            $users_traités[] = $user['utilisateur_id'];
                            
                            // Récupérer toutes les commandes de cet utilisateur
                            $commandes_user = array_filter($donnees, function($u) use ($user) {
                                return $u['utilisateur_id'] == $user['utilisateur_id'] && $u['commande_id'];
                            });
                            
                            // Prendre la première commande pour les infos uniques
                            $first_commande = !empty($commandes_user) ? reset($commandes_user) : null;
                            
                            // Calculer le total de toutes les commandes
                            $total_user = 0;
                            foreach ($commandes_user as $cmd) {
                                $total_user += $cmd['prix_total'];
                            }
                        ?>
                        <tr>
                            <td><span class="id-badge">#<?= $user['utilisateur_id'] ?></span></td>
                            <td><strong><?= htmlspecialchars($user['nom']) ?></strong></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td>
                                <?php if (!empty($commandes_user)): ?>
                                    <?php foreach ($commandes_user as $cmd): ?>
                                        <span class="plat-name"><?= htmlspecialchars($cmd['plat_nom'] ?? '—') ?></span>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <span class="no-data">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($first_commande && $first_commande['telephone']): ?>
                                    <span class="phone"><?= htmlspecialchars($first_commande['telephone']) ?></span>
                                <?php else: ?>
                                    <span class="no-data">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($first_commande && $first_commande['adresse_livraison']): ?>
                                    <span class="address"><?= htmlspecialchars($first_commande['adresse_livraison']) ?></span>
                                <?php else: ?>
                                    <span class="no-data">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($total_user > 0): ?>
                                    <span class="price"><?= number_format($total_user, 0, ',', ' ') ?> Ar</span>
                                <?php else: ?>
                                    <span class="no-data">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($commandes_user)): ?>
                                    <?php foreach ($commandes_user as $cmd): ?>
                                        <form method="POST" class="status-form">
                                            <input type="hidden" name="commande_id" value="<?= $cmd['commande_id'] ?>">
                                            <select name="statut" class="status-select status-<?= $cmd['statut'] ?>">
                                                <option value="en_attente" <?= $cmd['statut'] == 'en_attente' ? 'selected' : '' ?>>En attente</option>
                                                <option value="en_preparation" <?= $cmd['statut'] == 'en_preparation' ? 'selected' : '' ?>>En préparation</option>
                                                <option value="terminee" <?= $cmd['statut'] == 'terminee' ? 'selected' : '' ?>>Terminée</option>
                                                <option value="annulee" <?= $cmd['statut'] == 'annulee' ? 'selected' : '' ?>>Annulée</option>
                                            </select>
                                            <button type="submit" name="modifier" class="btn-update">
                                                <i class="fas fa-sync-alt"></i>
                                            </button>
                                        </form>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <span class="no-data">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <!-- Une seule poubelle par utilisateur -->
                                <form method="POST">
                                    <input type="hidden" name="utilisateur_id" value="<?= $user['utilisateur_id'] ?>">
                                    <button type="submit" name="supprimer" 
                                            onclick="return confirm('Supprimer cet utilisateur et toutes ses commandes ?')" 
                                            class="btn-delete" title="Supprimer l'utilisateur">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if (!$has_results): ?>
                        <tr>
                            <td colspan="9" class="empty-row">
                                <i class="fas fa-inbox"></i>
                                <p>Aucun résultat trouvé</p>
                                <span>Aucun utilisateur ne correspond à ce filtre</span>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

    </main>
</div>

</body>
</html>