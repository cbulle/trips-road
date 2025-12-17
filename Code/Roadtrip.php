<?php
require_once __DIR__ . '/modules/init.php';
include_once __DIR__ . '/bd/lec_bd.php';

$id_utilisateur = $_SESSION['utilisateur']['id'] ?? null;

$stmt = $pdo->prepare("SELECT * FROM roadtrip WHERE visibilite = 'public' ORDER BY id DESC");
$stmt->execute();
$roadtrips = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les favoris de l'utilisateur
$favorisIds = [];
if ($id_utilisateur) {
    $stmt = $pdo->prepare("SELECT id_roadtrip FROM favoris WHERE id_utilisateur = :id");
    $stmt->execute(['id' => $id_utilisateur]);
    $favorisIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Road Trips Publics</title>
    <link rel="stylesheet" href="/css/style.css">
    <style>
        .btn-favori {
            background: #ff69b4;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }
        .btn-favori.active {
            background: #e74c3c;
        }
        .btn-favori:hover {
            background: #ff1493;
        }
        .btn-favori.active:hover {
            background: #c0392b;
        }
    </style>
</head>
<body>
<?php include_once __DIR__ . "/modules/header.php"; ?>

<h1>Road Trips Publics</h1>

<?php if (isset($_SESSION['message'])): ?>
    <p style="text-align: center; color: green; font-weight: bold;">
        <?= htmlspecialchars($_SESSION['message']) ?>
    </p>
    <?php unset($_SESSION['message']); ?>
<?php endif; ?>

<?php if (empty($roadtrips)) : ?>
    <p>Aucun road trip public pour le moment.</p>
<?php else : ?>
<div class="roadtrip-grid">
    <?php foreach ($roadtrips as $rt): ?>
        <div class="roadtrip-card">

            <?php if (!empty($rt['photo'])): ?>
                <img src="/uploads/roadtrips/<?= htmlspecialchars($rt['photo']) ?>" 
                     alt="Photo du road trip" class="roadtrip-photo">
            <?php endif; ?>

            <h3><?= htmlspecialchars($rt['titre']) ?></h3>
            <p><?= htmlspecialchars($rt['description']) ?></p>

            <div class="roadtrip-buttons">
                <a class="btn-view" href="public_road.php?id=<?= $rt['id'] ?>">
                    Voir
                </a>
                
                <?php if ($id_utilisateur): ?>
                    <?php $isFavori = in_array($rt['id'], $favorisIds); ?>
                    <a class="btn-favori <?= $isFavori ? 'active' : '' ?>" 
                       href="/formulaire/favo.php?id=<?= $rt['id'] ?>&redirect=Roadtrip.php">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="<?= $isFavori ? 'white' : 'none' ?>">
                            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" 
                                  stroke="white" stroke-width="2"/>
                        </svg>
                        <?= $isFavori ? 'Favoris' : 'Ajouter' ?>
                    </a>
                <?php endif; ?>
            </div>

        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php include_once __DIR__ . "/modules/footer.php"; ?>

</body>
</html>