<?php

session_start();
require 'bd.php';

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
    <title>Accueil - Restaurant</title>
    
    <!-- Google Fonts pour une typographie moderne -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* Reset et styles de base */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }

        /* Container principal */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
            animation: fadeIn 0.8s ease-out;
        }

        /* Animation d'entrée */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* En-tête */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
            flex-wrap: wrap;
            gap: 15px;
        }

        .welcome {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .welcome i {
            font-size: 32px;
            color: #667eea;
        }

        .welcome h1 {
            font-size: 28px;
            font-weight: 700;
            color: #2d3748;
        }

        .welcome span {
            color: #667eea;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .title-section {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .title-section i {
            font-size: 28px;
            color: #764ba2;
        }

        .title-section h2 {
            font-size: 24px;
            font-weight: 600;
            color: #2d3748;
        }

        /* Tableau moderne */
        .table-wrapper {
            overflow-x: auto;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: white;
            border-radius: 12px;
            overflow: hidden;
        }

        thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        th {
            padding: 18px 20px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        th i {
            margin-right: 8px;
            font-size: 16px;
        }

        td {
            padding: 16px 20px;
            border-bottom: 1px solid #f0f0f0;
            color: #4a5568;
            font-size: 15px;
            vertical-align: middle;
        }

        tbody tr {
            transition: all 0.3s ease;
        }

        tbody tr:hover {
            background: #f7fafc;
            transform: scale(1.01);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        /* Images des plats */
        .plat-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .plat-image:hover {
            transform: scale(1.1);
        }

        .no-image {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100px;
            height: 100px;
            background: #f7fafc;
            border-radius: 12px;
            color: #a0aec0;
            font-size: 32px;
        }

        /* Badge catégorie */
        .category-badge {
            display: inline-block;
            padding: 6px 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 0.3px;
        }

        /* Prix */
        .price {
            font-weight: 700;
            color: #2d3748;
            font-size: 17px;
        }

        .price-currency {
            color: #667eea;
            font-weight: 600;
        }

        /* Bouton Commander */
        .btn-commander {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            border: none;
            cursor: pointer;
        }

        .btn-commander:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.5);
        }

        .btn-commander:active {
            transform: translateY(0);
        }

        .btn-commander i {
            font-size: 16px;
            transition: transform 0.3s ease;
        }

        .btn-commander:hover i {
            transform: translateX(4px);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }

            .header {
                flex-direction: column;
                align-items: flex-start;
            }

            .welcome h1 {
                font-size: 22px;
            }

            table {
                font-size: 13px;
            }

            th, td {
                padding: 12px 15px;
            }

            .plat-image, .no-image {
                width: 70px;
                height: 70px;
            }

            .btn-commander {
                padding: 8px 16px;
                font-size: 12px;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 15px;
            }

            .welcome h1 {
                font-size: 18px;
            }

            th, td {
                padding: 10px 12px;
                font-size: 12px;
            }

            .category-badge {
                font-size: 11px;
                padding: 4px 10px;
            }
        }

        /* Scrollbar personnalisée */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #764ba2;
        }
    </style>
</head>

<body>

    <div class="container">
        
        <!-- En-tête -->
        <div class="header">
            <div class="welcome">
                <i class="fas fa-user-circle"></i>
                <h1>Bonjour, <span><?php echo htmlspecialchars($_SESSION['nom']); ?></span></h1>
            </div>
            <div class="title-section">
                <i class="fas fa-utensils"></i>
                <h2>Notre Carte</h2>
            </div>
        </div>

        <!-- Tableau des plats -->
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th><i class="fas fa-image"></i> Image</th>
                        <th><i class="fas fa-tag"></i> Nom</th>
                        <th><i class="fas fa-info-circle"></i> Description</th>
                        <th><i class="fas fa-euro-sign"></i> Prix</th>
                        <th><i class="fas fa-list"></i> Catégorie</th>
                        <th><i class="fas fa-cart-plus"></i> Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($plats as $plat): ?>
                    <tr>
                        <td>
                            <?php if ($plat['image'] != ""): ?>
                                <img 
                                    src="images/<?= htmlspecialchars($plat['image']) ?>" 
                                    alt="<?= htmlspecialchars($plat['nom']) ?>"
                                    class="plat-image"
                                    loading="lazy">
                            <?php else: ?>
                                <div class="no-image">
                                    <i class="fas fa-utensil-spoon"></i>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td><strong><?= htmlspecialchars($plat['nom']) ?></strong></td>
                        <td><?= htmlspecialchars($plat['description']) ?></td>
                        <td>
                            <span class="price">
                                <?= number_format($plat['prix'], 0, ',', ' ') ?> 
                                <span class="price-currency">Ar</span>
                            </span>
                        </td>
                        <td>
                            <span class="category-badge">
                                <?= htmlspecialchars($plat['categorie']) ?>
                            </span>
                        </td>
                        <td>
                            <a href="commander.php?id=<?= $plat['id'] ?>" class="btn-commander">
                                Commander
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
    </div>

</body>

</html>