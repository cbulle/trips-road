<?php
require_once __DIR__ . '/modules/init.php';
include_once __DIR__ . '/bd/lec_bd.php';

if (!isset($_SESSION['utilisateur']['id'])) {
    header('Location: /id.php');
    exit;
}

$id_utilisateur = $_SESSION['utilisateur']['id'];
$id_roadtrip = $_GET['id']; // L'ID du road trip, passé en paramètre dans l'URL

// Récupérer les informations du road trip
$stmt = $pdo->prepare("SELECT * FROM roadtrip WHERE id = ? AND id_utilisateur = ?");
$stmt->execute([$id_roadtrip, $id_utilisateur]);
$roadTrip = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$roadTrip) {
    echo "Road trip introuvable.";
    exit;
}

// Récupérer les trajets associés à ce road trip
$stmt = $pdo->prepare("SELECT * FROM trajet WHERE road_trip_id = ? ORDER BY numero");
$stmt->execute([$id_roadtrip]);
$trajets = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les sous-étapes et les photos des sous-étapes
$etapes = [];
foreach ($trajets as $trajet) {
    $stmt = $pdo->prepare("SELECT * FROM sous_etape WHERE trajet_id = ? ORDER BY numero");
    $stmt->execute([$trajet['id']]);
    $etapes[$trajet['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer les photos pour chaque sous-étape
    foreach ($etapes[$trajet['id']] as &$sousEtape) {
        $stmt = $pdo->prepare("SELECT * FROM sous_etape_photos WHERE sous_etape_id = ?");
        $stmt->execute([$sousEtape['id']]);
        $sousEtape['photos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détails du Road Trip</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include_once __DIR__ . "/modules/header.php"; ?>

<div class="roadtrip-container">
    <div class="roadtrip-details">
        <h2><?= htmlspecialchars($roadTrip['titre']) ?></h2>
        <p><?= htmlspecialchars($roadTrip['description']) ?></p>

        <div id="etapes-container">
            <?php foreach ($trajets as $trajet): ?>
                <div class="etape" id="etape-<?= $trajet['id'] ?>" onclick="toggleSousEtapes(<?= $trajet['id'] ?>)">
                    <h3><?= htmlspecialchars($trajet['titre']) ?></h3>
                    <p><?= htmlspecialchars($trajet['depart']) ?> → <?= htmlspecialchars($trajet['arrivee']) ?></p>
                </div>

                <div class="sous-etapes" id="sous-etapes-<?= $trajet['id'] ?>" style="display: none;">
                    <?php foreach ($etapes[$trajet['id']] as $sousEtape): ?>
                        <div class="sous-etape">
                            <h4><?= htmlspecialchars($sousEtape['ville']) ?></h4>
                            <p><?= htmlspecialchars($sousEtape['description']) ?></p>
                            <div class="photos">
                                <?php foreach ($sousEtape['photos'] as $photo): ?>
                                    <img src="/uploads/sousetapes/<?= htmlspecialchars($photo['chemin']) ?>" alt="Photo sous-étape" class="sous-etape-photo">
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <button onclick="toggleSousEtapes(<?= $trajet['id'] ?>)">Fermer sous-étapes</button>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="map-container-vu">
        <div id="map-vu"></div>
    </div>
</div>

<script src="js/map_vu.js"></script>

<?php include_once __DIR__ . "/modules/footer.php"; ?>

</body>
</html>
