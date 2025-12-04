<?php
require_once __DIR__ . '/modules/init.php';
include_once __DIR__ . '/bd/lec_bd.php';
include_once __DIR__ . '/fonctions/InfoItineraire.php';
if (!isset($_SESSION['utilisateur']['id'])) {
    header('Location: /id.php');
    exit;
}
$id_utilisateur = $_SESSION['utilisateur']['id'];
$id_roadtrip = $_GET['id'];

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
    $sousEtapes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $etapes[$trajet['id']] = [];
    
    foreach ($sousEtapes as $sousEtape) {
        $stmt = $pdo->prepare("SELECT * FROM sous_etape_photos WHERE sous_etape_id = ?");
        $stmt->execute([$sousEtape['id']]);
        $sousEtape['photos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $etapes[$trajet['id']][] = $sousEtape;
    }
}

// Fonction pour obtenir l'emoji du transport
function getTransportIcon($type) {
    switch(strtolower($type)) {
        case 'voiture':
            return '🚗';
        case 'velo':
        case 'vélo':
            return '🚴';
        case 'marche':
        case 'à pied':
        case 'a pied':
            return '🚶';
        default:
            return '🚗';
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
<div class="roadtrip-vu">
    <div class="roadtrip-header">
        <h1><?php echo htmlspecialchars($roadTrip['titre']); ?></h1>
        <p><?php echo nl2br(htmlspecialchars($roadTrip['description'])); ?></p>
    </div>
    
    <?php foreach ($trajets as $t) : ?>
        <?php $depart = $t['depart'];
        $arrive = $t['arrivee']; ?>

        <div class="card-vu" data-trajet-id="<?php echo $t['id']; ?>">
            <div class="trajet-header" onclick="toggleSousEtapes(<?php echo $t['id']; ?>)">
                <div class="trajet-info">
                    <h2 class="trajet-titre"><?php echo htmlspecialchars($t['titre']); ?></h2>
                    <div class="trajet-details">
                        <?php if (!empty($t['date_trajet'])) : ?>
                            <div class="trajet-detail-item">
                                <span>📅</span>
                                <span><?php echo date('d/m/Y', strtotime($t['date_trajet'])); ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($t['mode_transport'])) : ?>
                            <div class="trajet-detail-item">
                                <span class="transport-icon"><?php echo getTransportIcon($t['mode_transport']); ?></span>
                                <strong><?php echo htmlspecialchars(ucfirst($t['mode_transport'])); ?></strong>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="toggle-icon">▼</div>
            </div>
            
            <div class="sous-etapes-container" id="sous-etapes-<?php echo $t['id']; ?>">
                <?php if (isset($etapes[$t['id']]) && count($etapes[$t['id']]) > 0) : ?>
                    <div class="sous-etape-card">
                        <div class="sous-etape-header">
                            <h3><?php echo htmlspecialchars($depart) ?></h3>
                        </div>
                    </div>
                    <?php foreach ($etapes[$t['id']] as $sousEtape) : ?>
                        <section class="timeline">
                            <ul>
                                <li><span class="transport-icon"><?php echo getTransportIcon($sousEtape['type_transport']); ?></span>
                                    <strong><?php echo htmlspecialchars(ucfirst($sousEtape['type_transport'])); ?></strong></li>
                                <li><?php $disance = calculerDistance($depart, $sousEtape['ville'], $sousEtape['type_transport']); echo $distance;?></li>
                                <li><?php $temps = calculerTempsTrajet($depart, $sousEtape['ville'], $sousEtape['type_transport']); echo $temps['texte'];?></li>
                            </ul>
                        </section>
                        <?php $depart = $sousEtape['ville'];?>
                        <div class="sous-etape-card">
                            <div class="sous-etape-header">
                                <h3><?php echo htmlspecialchars($sousEtape['ville']); ?></h3>
                                <span class="numero-etape">Étape <?php echo $sousEtape['numero']; ?></span>
                            </div>
                            
                            <div class="sous-etape-info">    
                                <?php if (!empty($sousEtape['heure'])) : ?>
                                    <span>
                                        <strong>🕐</strong>
                                        <?php echo htmlspecialchars($sousEtape['heure']); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <?php if (!empty($sousEtape['description'])) : ?>
                                <p><?php echo nl2br(htmlspecialchars($sousEtape['description'])); ?></p>
                            <?php endif; ?>
                            
                            <?php if (isset($sousEtape['photos']) && count($sousEtape['photos']) > 0) : ?>
                                <div class="photos-container">
                                    <?php foreach ($sousEtape['photos'] as $photo) : ?>
                                        <img src="/uploads/sousetapes/<?php echo htmlspecialchars($photo['photo']); ?>" 
                                             alt="Photo de <?php echo htmlspecialchars($sousEtape['ville']); ?>">
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    <section class="timeline">
                            <ul>
                                <li><span class="transport-icon"><?php echo getTransportIcon($t['mode_transport']); ?></span>
                                    <strong><?php echo htmlspecialchars(ucfirst($t['mode_transport'])); ?></strong></li>
                                <li><?="Distance"?></li>
                                <li><?="Temps"?></li>
                            </ul>
                        </section>
                    <div class="sous-etape-card">
                        <div class="sous-etape-header">
                            <h3><?php echo htmlspecialchars($arrive) ?></h3>
                        </div>
                    </div>
                <?php else : ?>
                    <p class="no-sous-etapes">Aucune sous-étape pour ce trajet.</p>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>    
</div>
<script src="js/map_vu.js"></script>
<?php include_once __DIR__ . "/modules/footer.php"; ?>
</body>
</html>