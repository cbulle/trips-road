<?php
require_once __DIR__ . '/include/init.php';
include_once __DIR__ . '/bd/lec_bd.php';

$id_utilisateur = $_SESSION['utilisateur']['id'] ?? null;

// --- MODIFICATION ICI : On fait une jointure pour récupérer le pseudo ---
// On suppose que la clé étrangère dans 'roadtrip' est 'id_utilisateur'
$sql = "SELECT r.*, u.pseudo 
        FROM roadtrip r 
        LEFT JOIN utilisateurs u ON r.id_utilisateur = u.id 
        WHERE r.visibilite = 'public' 
        ORDER BY r.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$roadtrips = $stmt->fetchAll(PDO::FETCH_ASSOC);

$favorisIds = [];
if ($id_utilisateur) {
    $stmt = $pdo->prepare("SELECT id_roadtrip FROM favoris WHERE id_utilisateur = :id");
    $stmt->execute(['id' => $id_utilisateur]);
    $favorisIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// (La requête séparée sur 'utilisateurs' n'est plus nécessaire ici, je l'ai retirée)
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Road Trips Publics</title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/accessibilite.css">
    
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

            <p class="creator-info">
                Proposé par : <strong><?= htmlspecialchars($rt['pseudo'] ?? 'Utilisateur inconnu') ?></strong>
            </p>

            <div class="roadtrip-buttons">
                <a class="btn-view" href="public_road.php?id=<?= $rt['id'] ?>">
                    <i class="material-icons">visibility</i>
                </a>
                
                <?php if ($id_utilisateur): ?>
                    <?php $isFavori = in_array($rt['id'], $favorisIds); ?>
                    <a class="btn-favori <?= $isFavori ? 'active' : '' ?>" 
                       href="/formulaire/favo.php?id=<?= $rt['id'] ?>&redirect=Roadtrip.php">
                        <i class="material-icons">favorite</i>
                        
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