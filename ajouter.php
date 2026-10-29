<?php
session_start();
require 'bd.php';

if (!isset($_SESSION['admin'])) {
    header("Location: connexion.php");
    exit();
}

/* AJOUTER */
if (isset($_POST['ajouter'])) {
    $nom = $_POST['nom'];
    $description = $_POST['description'];
    $prix = $_POST['prix'];
    $categorie = $_POST['categorie'];
    $image = "";

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        if (!is_dir("images")) {
            mkdir("images");
        }
        $image = $_FILES['image']['name'];
        move_uploaded_file(
            $_FILES['image']['tmp_name'],
            "images/" . $image
        );
    }

    $sql = "INSERT INTO plats (nom, description, prix, categorie, image) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nom, $description, $prix, $categorie, $image]);

    header("Location: ajouter.php");
    exit();
}

/* MODIFIER */
if (isset($_POST['modifier'])) {
    $id = $_POST['id'];
    $nom = $_POST['nom'];
    $description = $_POST['description'];
    $prix = $_POST['prix'];
    $categorie = $_POST['categorie'];

    $sql = "UPDATE plats SET nom = ?, description = ?, prix = ?, categorie = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nom, $description, $prix, $categorie, $id]);

    header("Location: ajouter.php");
    exit();
}

/* SUPPRIMER */
if (isset($_POST['supprimer'])) {
    $id = $_POST['id'];
    $sql = "DELETE FROM plats WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);

    header("Location: ajouter.php");
    exit();
}

/* PLAT À MODIFIER */
$platModifier = null;
if (isset($_GET['modifier'])) {
    $sql = "SELECT * FROM plats WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_GET['modifier']]);
    $platModifier = $stmt->fetch(PDO::FETCH_ASSOC);
}

/* AFFICHER LES PLATS */
$sql = "SELECT * FROM plats";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$plats = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Plats - Administration</title>
    <style>
        /* ===== RESET & BASE ===== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0c2461 0%, #1e3799 30%, #4a69bd 70%, #6a89cc 100%);
            min-height: 100vh;
            padding: 40px 20px;
            color: #333;
            position: relative;
        }

        /* Effet de particules en arrière-plan */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(2px 2px at 20% 30%, rgba(255,255,255,0.3), transparent),
                radial-gradient(2px 2px at 40% 70%, rgba(255,255,255,0.2), transparent),
                radial-gradient(2px 2px at 60% 20%, rgba(255,255,255,0.3), transparent),
                radial-gradient(2px 2px at 80% 80%, rgba(255,255,255,0.2), transparent),
                radial-gradient(2px 2px at 10% 90%, rgba(255,255,255,0.3), transparent),
                radial-gradient(2px 2px at 90% 10%, rgba(255,255,255,0.2), transparent);
            background-size: 200px 200px;
            pointer-events: none;
            z-index: 0;
        }

        /* ===== CONTENEUR PRINCIPAL ===== */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            padding: 40px 50px;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3), 
                        inset 0 1px 0 rgba(255, 255, 255, 0.2);
            position: relative;
            z-index: 1;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* ===== HEADER ===== */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 35px;
            flex-wrap: wrap;
            background: linear-gradient(135deg, #0c2461, #1e3799);
            padding: 20px 30px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(12, 36, 97, 0.3);
        }

        .header h1 {
            font-size: 32px;
            font-weight: 700;
            color: #ffffff;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .header h1 span {
            color: #f6b93b;
        }

        .header .admin-badge {
            background: rgba(255, 255, 255, 0.15);
            color: #fff;
            padding: 10px 24px;
            border-radius: 30px;
            font-size: 14px;
            font-weight: 500;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .header .admin-badge i {
            margin-right: 6px;
        }

        /* ===== TITRES ===== */
        h2 {
            font-size: 26px;
            font-weight: 600;
            color: #0c2461;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 3px solid #4a69bd;
            display: inline-block;
        }

        /* ===== FORMULAIRES ===== */
        .form-section {
            background: linear-gradient(135deg, #f0f4ff, #e8edf9);
            padding: 30px 35px;
            border-radius: 16px;
            margin-bottom: 40px;
            border: 1px solid rgba(74, 105, 189, 0.15);
            transition: box-shadow 0.3s;
            box-shadow: 0 2px 15px rgba(12, 36, 97, 0.05);
        }

        .form-section:hover {
            box-shadow: 0 8px 30px rgba(12, 36, 97, 0.1);
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            font-size: 14px;
            color: #0c2461;
            margin-bottom: 6px;
            letter-spacing: 0.3px;
        }

        .form-group label .required {
            color: #e94560;
        }

        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group textarea {
            width: 100%;
            max-width: 500px;
            padding: 12px 16px;
            border: 2px solid #dce3ef;
            border-radius: 10px;
            font-size: 15px;
            background: #ffffff;
            transition: border-color 0.25s, box-shadow 0.25s, transform 0.2s;
            font-family: inherit;
        }

        .form-group input[type="text"]:focus,
        .form-group input[type="number"]:focus,
        .form-group textarea:focus {
            border-color: #4a69bd;
            outline: none;
            box-shadow: 0 0 0 4px rgba(74, 105, 189, 0.15);
            transform: translateY(-1px);
        }

        .form-group textarea {
            min-height: 80px;
            resize: vertical;
        }

        .form-group input[type="file"] {
            display: block;
            margin-top: 8px;
            font-size: 14px;
            color: #0c2461;
            padding: 8px 0;
        }

        .form-group input[type="file"]::file-selector-button {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            background: linear-gradient(135deg, #1e3799, #4a69bd);
            color: #fff;
            font-weight: 600;
            cursor: pointer;
            margin-right: 14px;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .form-group input[type="file"]::file-selector-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(74, 105, 189, 0.3);
        }

        .form-actions {
            margin-top: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        /* ===== BOUTONS ===== */
        .btn {
            display: inline-block;
            padding: 12px 30px;
            font-size: 15px;
            font-weight: 600;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            letter-spacing: 0.3px;
            position: relative;
            overflow: hidden;
        }

        .btn::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .btn:active::after {
            width: 300px;
            height: 300px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #0c2461, #1e3799);
            color: #ffffff;
            box-shadow: 0 4px 15px rgba(12, 36, 97, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(12, 36, 97, 0.4);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #4a69bd, #6a89cc);
            color: #ffffff;
            box-shadow: 0 4px 15px rgba(74, 105, 189, 0.3);
        }

        .btn-secondary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(74, 105, 189, 0.4);
        }

        .btn-danger {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: #ffffff;
            box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
        }

        .btn-danger:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(231, 76, 60, 0.4);
        }

        .btn-sm {
            padding: 8px 18px;
            font-size: 13px;
        }

        .btn:active {
            transform: scale(0.97);
        }

        .btn-link {
            background: none;
            color: #4a69bd;
            padding: 8px 0;
            font-weight: 600;
            box-shadow: none;
        }

        .btn-link:hover {
            color: #0c2461;
            text-decoration: underline;
            transform: none;
            box-shadow: none;
            background: none;
        }

        /* ===== TABLEAU ===== */
        .table-wrapper {
            overflow-x: auto;
            margin-top: 30px;
            border-radius: 16px;
            border: 1px solid rgba(74, 105, 189, 0.1);
            box-shadow: 0 2px 20px rgba(12, 36, 97, 0.05);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 15px;
        }

        table thead {
            background: linear-gradient(135deg, #0c2461, #1e3799);
        }

        table th {
            color: #ffffff;
            font-weight: 600;
            padding: 16px 18px;
            text-align: left;
            letter-spacing: 0.3px;
            font-size: 13px;
            text-transform: uppercase;
        }

        table td {
            padding: 16px 18px;
            border-bottom: 1px solid #eef2f7;
            vertical-align: middle;
            background: #ffffff;
        }

        table tbody tr {
            transition: background 0.2s;
        }

        table tbody tr:hover td {
            background: #f5f8ff;
        }

        table tbody tr:last-child td {
            border-bottom: none;
        }

        .plat-image {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 12px;
            border: 2px solid #e8edf5;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .plat-image:hover {
            transform: scale(1.5);
            box-shadow: 0 8px 25px rgba(12, 36, 97, 0.3);
            z-index: 10;
            position: relative;
            border-color: #4a69bd;
        }

        .plat-image-placeholder {
            width: 70px;
            height: 70px;
            border-radius: 12px;
            border: 2px dashed #b8c6e0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: #4a69bd;
            background: #f0f4ff;
        }

        .plat-prix {
            font-weight: 700;
            color: #0c2461;
            font-size: 16px;
        }

        .plat-categorie {
            display: inline-block;
            background: linear-gradient(135deg, #4a69bd, #6a89cc);
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 13px;
            color: #ffffff;
            font-weight: 500;
        }

        .table-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .table-actions .btn {
            margin: 0;
        }

        /* ===== STATS ===== */
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: linear-gradient(135deg, #f0f4ff, #e8edf9);
            padding: 20px 25px;
            border-radius: 14px;
            border: 1px solid rgba(74, 105, 189, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(12, 36, 97, 0.1);
        }

        .stat-card .stat-number {
            font-size: 32px;
            font-weight: 700;
            color: #0c2461;
        }

        .stat-card .stat-label {
            font-size: 14px;
            color: #4a69bd;
            margin-top: 4px;
            font-weight: 500;
        }

        /* ===== ALERTES ===== */
        .alert {
            padding: 16px 22px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .alert-info {
            background: linear-gradient(135deg, #dbeafe, #bfdbfe);
            color: #1e3a8a;
            border: 1px solid #93b5e8;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 992px) {
            .container {
                padding: 30px;
            }
        }

        @media (max-width: 768px) {
            .container {
                padding: 20px;
                border-radius: 16px;
            }

            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
                padding: 16px 20px;
            }

            .header h1 {
                font-size: 24px;
            }

            .form-section {
                padding: 20px;
            }

            .form-group input[type="text"],
            .form-group input[type="number"],
            .form-group textarea {
                max-width: 100%;
            }

            table {
                font-size: 13px;
            }

            table th,
            table td {
                padding: 12px 14px;
            }

            .plat-image,
            .plat-image-placeholder {
                width: 50px;
                height: 50px;
            }

            .plat-image:hover {
                transform: scale(1.2);
            }

            h2 {
                font-size: 22px;
            }

            .stats {
                grid-template-columns: 1fr 1fr;
            }

            .stat-card .stat-number {
                font-size: 26px;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 12px 8px;
            }

            .container {
                padding: 14px;
                border-radius: 12px;
            }

            .form-section {
                padding: 14px;
            }

            .btn {
                padding: 10px 20px;
                font-size: 14px;
            }

            .table-actions {
                flex-direction: column;
                gap: 4px;
            }

            .table-actions .btn {
                width: 100%;
                text-align: center;
            }

            .stats {
                grid-template-columns: 1fr;
            }

            .header h1 {
                font-size: 20px;
            }
        }

        /* ===== SCROLLBAR ===== */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #e8edf9;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #4a69bd, #0c2461);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #6a89cc, #1e3799);
        }

        /* Animation d'entrée */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .container {
            animation: fadeInUp 0.6s ease-out;
        }

        .stat-card {
            animation: fadeInUp 0.6s ease-out forwards;
            opacity: 0;
        }

        .stat-card:nth-child(1) { animation-delay: 0.1s; }
        .stat-card:nth-child(2) { animation-delay: 0.2s; }
        .stat-card:nth-child(3) { animation-delay: 0.3s; }
    </style>
</head>
<body>

<div class="container">

    <!-- ===== HEADER ===== -->
    <div class="header">
        <h1>🍽️ Gestion des <span>Plats</span></h1>
        <div class="admin-badge">
            👤 Administrateur
        </div>
    </div>

    <!-- ===== STATISTIQUES ===== -->
    <div class="stats">
        <div class="stat-card">
            <div class="stat-number"><?= count($plats) ?></div>
            <div class="stat-label">Total des plats</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">
                <?php
                $categories = array_unique(array_column($plats, 'categorie'));
                echo count($categories);
                ?>
            </div>
            <div class="stat-label">Catégories</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">
                <?php
                $totalPrix = array_sum(array_column($plats, 'prix'));
                echo number_format($totalPrix, 0, ',', ' ');
                ?> Ar
            </div>
            <div class="stat-label">Valeur totale du menu</div>
        </div>
    </div>

    <!-- ===== FORMULAIRE AJOUT / MODIFICATION ===== -->
    <?php if ($platModifier): ?>

        <h2>✏️ Modifier le plat</h2>
        <div class="form-section">
            <form method="POST">
                <input type="hidden" name="id" value="<?= $platModifier['id'] ?>">

                <div class="form-group">
                    <label>Nom <span class="required">*</span></label>
                    <input type="text" name="nom" value="<?= htmlspecialchars($platModifier['nom']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description"><?= htmlspecialchars($platModifier['description']) ?></textarea>
                </div>

                <div class="form-group">
                    <label>Prix <span class="required">*</span></label>
                    <input type="number" name="prix" value="<?= $platModifier['prix'] ?>" step="0.01" required>
                </div>

                <div class="form-group">
                    <label>Catégorie</label>
                    <input type="text" name="categorie" value="<?= htmlspecialchars($platModifier['categorie']) ?>">
                </div>

                <div class="form-actions">
                    <button type="submit" name="modifier" class="btn btn-primary">💾 Enregistrer</button>
                    <a href="ajouter.php" class="btn btn-link">❌ Annuler</a>
                </div>
            </form>
        </div>

    <?php else: ?>

        <h2>➕ Ajouter un plat</h2>
        <div class="form-section">
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Nom <span class="required">*</span></label>
                    <input type="text" name="nom" required>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description"></textarea>
                </div>

                <div class="form-group">
                    <label>Prix <span class="required">*</span></label>
                    <input type="number" name="prix" step="0.01" required>
                </div>

                <div class="form-group">
                    <label>Catégorie</label>
                    <input type="text" name="categorie" placeholder="Ex: Entrée, Plat, Dessert...">
                </div>

                <div class="form-group">
                    <label>Image</label>
                    <input type="file" name="image" accept="image/*">
                </div>

                <div class="form-actions">
                    <button type="submit" name="ajouter" class="btn btn-primary">➕ Ajouter</button>
                    <a href="admin.php" class="btn btn-secondary">🏠 Revenir à l'accueil</a>
                </div>
            </form>
        </div>

    <?php endif; ?>

    <!-- ===== LISTE DES PLATS ===== -->
    <h2>📋 Liste des plats</h2>

    <?php if (empty($plats)): ?>
        <div class="alert alert-info">
            Aucun plat n'a été ajouté pour le moment.
        </div>
    <?php else: ?>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Nom</th>
                        <th>Description</th>
                        <th>Prix</th>
                        <th>Catégorie</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($plats as $plat): ?>
                    <tr>
                        <td><strong>#<?= $plat['id'] ?></strong></td>
                        <td>
                            <?php if (!empty($plat['image']) && file_exists("images/" . $plat['image'])): ?>
                                <img src="images/<?= $plat['image'] ?>" alt="<?= htmlspecialchars($plat['nom']) ?>" class="plat-image">
                            <?php else: ?>
                                <div class="plat-image-placeholder">📷</div>
                            <?php endif; ?>
                        </td>
                        <td><strong><?= htmlspecialchars($plat['nom']) ?></strong></td>
                        <td><?= htmlspecialchars(substr($plat['description'] ?? '', 0, 60)) ?><?= strlen($plat['description'] ?? '') > 60 ? '...' : '' ?></td>
                        <td class="plat-prix"><?= number_format($plat['prix'], 0, ',', ' ') ?> Ar</td>
                        <td>
                            <?php if (!empty($plat['categorie'])): ?>
                                <span class="plat-categorie"><?= htmlspecialchars($plat['categorie']) ?></span>
                            <?php else: ?>
                                <span style="color:#999;font-size:13px;">Non catégorisé</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="table-actions">
                                <a href="ajouter.php?modifier=<?= $plat['id'] ?>" class="btn btn-secondary btn-sm">✏️ Modifier</a>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="id" value="<?= $plat['id'] ?>">
                                    <button type="submit" name="supprimer" class="btn btn-danger btn-sm" onclick="return confirm('⚠️ Voulez-vous vraiment supprimer ce plat ?')">
                                        🗑️ Supprimer
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php endif; ?>

</div>

</body>
</html>