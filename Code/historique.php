<?php
require_once __DIR__ . '/include/init.php';
include_once __DIR__ . '/bd/lec_bd.php';

/** @var PDO $pdo */

// Redirection si pas connecté
if (!isset($_SESSION['utilisateur']['id'])) {
    header('Location: /id.php');
    exit;
}

$id_utilisateur = $_SESSION['utilisateur']['id'];

// --- ACTION : SUPPRIMER L'HISTORIQUE ---
if (isset($_GET['action']) && $_GET['action'] === 'clear') {
    $stmt = $pdo->prepare("DELETE FROM historique WHERE id_utilisateur = :uid");
    $stmt->execute(['uid' => $id_utilisateur]);
    header('Location: historique.php');
    exit;
}

// --- RÉCUPÉRATION DES DONNÉES ---
// On joint historique -> roadtrip -> utilisateurs (pour avoir le nom du créateur)
$stmt = $pdo->prepare("
    SELECT h.date_visite, r.*, u.nom, u.prenom 
    FROM historique h
    JOIN roadtrip r ON h.id_roadtrip = r.id
    JOIN utilisateurs u ON r.id_utilisateur = u.id
    WHERE h.id_utilisateur = :uid
    ORDER BY h.date_visite DESC
    LIMIT 50
");
$stmt->execute(['uid' => $id_utilisateur]);
$historique = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Historique - Trips & Roads</title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/accessibilite.css">
    <script src="https://kit.fontawesome.com/d76759a8b0.js" crossorigin="anonymous"></script>
    <style>
        .header-tools {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .btn-clear-history {
            background-color: var(--rouge); 
            color: white;
            padding: 10px 15px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            font-size: 0.9rem;
            transition: 0.3s;
        }
        .btn-clear-history:hover {
            background-color: #8B0000;
        }
        .date-visite {
            font-size: 0.8rem;
            color: #666;
            font-style: italic;
            margin-bottom: 10px;
            display: block;
        }
    </style>
</head>
<body>

<?php include_once __DIR__ . "/modules/header.php"; ?>

<main class="main-index">
    <div class="index_container">
        
        <div class="header-tools">
            <h1>🕓 Mon Historique</h1>
            <?php if (!empty($historique)): ?>
                <a href="historique.php?action=clear" class="btn-clear-history" 
                   onclick="return confirm('Voulez-vous vraiment effacer tout votre historique de consultation ?');">
                    <i class="fas fa-trash-alt"></i> Tout effacer
                </a>
            <?php endif; ?>
        </div>

        <?php if (empty($historique)): ?>
            <div style="text-align: center; padding: 50px;">
                <p>Vous n'avez consulté aucun road trip récemment.</p>
                <a href="Roadtrip.php" style="color: var(--bleu_clair); font-weight: bold;">
                    Explorer les road trips
                </a>
            </div>
        <?php else: ?>
            <div class="roadtrip-grid">
                <?php foreach ($historique as $item): ?>
                    <div class="roadtrip-card">
                        <?php 
                        // Gestion Image
                        $imagePath = "default_trip.jpg";
                        if (!empty($item['photo'])) $imagePath = $item['photo'];
                        elseif (!empty($item['photo_cover'])) $imagePath = $item['photo_cover'];
                        ?>
                        
                        <img src="/uploads/roadtrips/<?= htmlspecialchars($imagePath) ?>" 
                             alt="Photo du road trip" class="roadtrip-photo">

                        <h3><?= htmlspecialchars($item['titre']) ?></h3>
                        
                        <span class="date-visite">
                            Vu le <?= date('d/m/Y à H:i', strtotime($item['date_visite'])) ?>
                        </span>

                        <p><?= htmlspecialchars(substr($item['description'], 0, 80)) ?>...</p>
                        <p><small>Par <?= htmlspecialchars($item['nom'] . ' ' . $item['prenom']) ?></small></p>

                        <div class="roadtrip-buttons">
                            <a class="btn-view" href="public_road.php?id=<?= $item['id'] ?>">
                                <i class="fas fa-eye"></i> Revoir
                            </a>
                            <a class="btn-edit" href="/formulaire/favo.php?id=<?= $item['id'] ?>&redirect=historique.php">
                                <i class="far fa-star"></i> Favoris
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</main>

<?php include_once __DIR__ . "/modules/footer.php"; ?>

</body>
</html>