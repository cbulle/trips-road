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

// 1. Récupération Roadtrip
$stmt = $pdo->prepare("SELECT * FROM roadtrip WHERE id = ? AND id_utilisateur = ?");
$stmt->execute([$id_roadtrip, $id_utilisateur]);
$roadTrip = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$roadTrip) { echo "Road trip introuvable."; exit; }

// 2. Récupération Trajets
$stmt = $pdo->prepare("SELECT * FROM trajet WHERE road_trip_id = ? ORDER BY numero");
$stmt->execute([$id_roadtrip]);
$trajets = $stmt->fetchAll(PDO::FETCH_ASSOC);

$etapes = [];
$jsMapData = []; // Tableau qui sera envoyé au JS

// 3. Boucle de préparation des données
foreach ($trajets as $trajet) {
    // Récup sous-étapes
    $stmt = $pdo->prepare("SELECT * FROM sous_etape WHERE trajet_id = ? ORDER BY numero");
    $stmt->execute([$trajet['id']]);
    $sousEtapes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Récup photos
    foreach ($sousEtapes as &$se) {
        $stmtPhoto = $pdo->prepare("SELECT * FROM sous_etape_photos WHERE sous_etape_id = ?");
        $stmtPhoto->execute([$se['id']]);
        $se['photos'] = $stmtPhoto->fetchAll(PDO::FETCH_ASSOC);
    }
    unset($se);
    
    $etapes[$trajet['id']] = $sousEtapes;

    // Coordonnées pour la carte JS
    $coordsDep = getCoordonneesDepuisCache($trajet['depart'], $pdo);
    $coordsArr = getCoordonneesDepuisCache($trajet['arrivee'], $pdo);

    if ($coordsDep && $coordsArr) {
        // Préparer les coordonnées des sous-étapes pour la carte
        $sousEtapesCoords = [];
        foreach ($sousEtapes as $se) {
            if (!empty($se['ville'])) {
                $coords = getCoordonneesDepuisCache($se['ville'], $pdo);
                if ($coords) {
                    $sousEtapesCoords[] = [
                        'lat' => $coords['lat'],
                        'lon' => $coords['lon'],
                        'nom' => $se['ville'],
                        'heure' => $se['heure'] ?? '',
                        'remarque' => $se['description'] ?? ''
                    ];
                }
            }
        }

        $jsMapData[$trajet['id']] = [
            'id' => $trajet['id'],
            'titre' => $trajet['titre'],
            'mode' => strtolower($trajet['mode_transport']),
            'depart' => ['lat' => $coordsDep['lat'], 'lon' => $coordsDep['lon'], 'nom' => $trajet['depart']],
            'arrivee' => ['lat' => $coordsArr['lat'], 'lon' => $coordsArr['lon'], 'nom' => $trajet['arrivee']],
            'sousEtapes' => $sousEtapesCoords // Ajout des sous-étapes
        ];
    }
}

function getTransportIcon($type) {
    switch(strtolower($type)) {
        case 'voiture': return '🚗';
        case 'velo': case 'vélo': return '🚴';
        case 'marche': case 'à pied': return '🚶';
        default: return '🚗';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($roadTrip['titre']); ?></title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include_once __DIR__ . "/modules/header.php"; ?>

<div class="roadtrip-vu">
    <div class="roadtrip-header">
        <h1><?php echo htmlspecialchars($roadTrip['titre']); ?></h1>
        <p><?php echo nl2br(htmlspecialchars($roadTrip['description'])); ?></p>
    </div>

    <h2>Vue d'ensemble du Road Trip 🌍</h2>
    <div id="map-global"></div>
    
    <?php foreach ($trajets as $t) : ?>
        <div class="card-vu" id="card-<?php echo $t['id']; ?>">
            
            <div class="trajet-header" onclick="toggleTrajet(<?php echo $t['id']; ?>)">
                <div class="trajet-info">
                    <h2 class="trajet-titre"><?php echo htmlspecialchars($t['depart'] . ' ➝ ' . $t['arrivee']); ?></h2>
                    
                    <div class="trajet-details">
                        <?php if (!empty($t['date_trajet'])) : ?>
                            <div class="trajet-detail-item">
                                <span>📅 <?php echo date('d/m/Y', strtotime($t['date_trajet'])); ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="trajet-detail-item">
                            <span class="transport-icon"><?php echo getTransportIcon($t['mode_transport']); ?></span>
                        </div>
                    </div>
                </div>
                <div class="toggle-icon">▼</div>
            </div>
            
            <div class="sous-etapes-container" id="sous-etapes-<?php echo $t['id']; ?>">
                
                <div class="trajet-details-column">
                    <?php 
                    $listeEtapes = $etapes[$t['id']] ?? [];
                    
                    // MODIFICATION : Construire la timeline complète avec DEPART + SOUS-ETAPES + ARRIVEE
                    $timeline = [];
                    
                    // 1. Ajout du DÉPART
                    $timeline[] = [
                        'ville' => $t['depart'], 
                        'is_departure' => true
                    ];
                    
                    // 2. Ajout des SOUS-ÉTAPES
                    foreach ($listeEtapes as $etape) {
                        $timeline[] = $etape;
                    }
                    
                    // 3. Ajout de l'ARRIVÉE
                    $timeline[] = [
                        'ville' => $t['arrivee'], 
                        'is_arrival' => true
                    ];
                    
                    // Affichage de toute la timeline
                    for ($i = 0; $i < count($timeline); $i++) :
                        $step = $timeline[$i];
                        $villeNom = $step['ville'] ?? $step['nom'] ?? 'Étape';
                        $isDeparture = isset($step['is_departure']);
                        $isArrival = isset($step['is_arrival']);
                    ?>
                        <div class="sous-etape-card <?php echo $isDeparture ? 'depart-card' : ($isArrival ? 'arrivee-card' : ''); ?>">
                            <div class="sous-etape-header">
                                <h3>
                                    <?php 
                                    if ($isDeparture) {
                                        echo '🚀 Départ : ';
                                    } elseif ($isArrival) {
                                        echo '🏁 Arrivée : ';
                                    } else {
                                        echo '📍 ';
                                    }
                                    echo htmlspecialchars($villeNom); 
                                    ?>
                                </h3>
                            </div>

                            <?php if (!$isDeparture && !$isArrival): ?>
                                <!-- Affichage des sous-étapes normales -->
                                <div class="sous-etape-info">    
                                    <?php if (!empty($step['heure'])) : ?>
                                        <span>🕐 <?php echo htmlspecialchars($step['heure']); ?></span>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if (!empty($step['description'])) : ?>
                                    <div class="sous-etape-description">
                                        <h4 class="description-title">📝 Description</h4>
                                        <div class="tinymce-content">
                                            <?php echo $step['description']; ?> 
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <!-- Debug : afficher si la description est vide -->
                                    <div class="sous-etape-description-empty">
                                        <p style="color: #999; font-style: italic;">Aucune description pour cette étape</p>
                                    </div>
                                <?php endif; ?>

                                <?php if (isset($step['photos']) && count($step['photos']) > 0) : ?>
                                    <div class="photos-section">
                                        <h4 class="photos-title">📷 Photos</h4>
                                        <div class="photos-container">
                                            <?php foreach ($step['photos'] as $photo) : ?>
                                                <img src="/uploads/sousetapes/<?php echo htmlspecialchars($photo['photo']); ?>" alt="Photo" class="popup-photo">
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        
                        <?php 
                        // NOUVEAU : Affichage du segment de transport vers la prochaine étape
                        if ($i < count($timeline) - 1) :
                            $nextStep = $timeline[$i + 1];
                            $nextVilleNom = $nextStep['ville'] ?? $nextStep['nom'] ?? 'Étape';
                            
                            // Récupérer les coordonnées
                            $coordsFrom = getCoordonneesDepuisCache($villeNom, $pdo);
                            $coordsTo = getCoordonneesDepuisCache($nextVilleNom, $pdo);
                            
                            if ($coordsFrom && $coordsTo) :
                        ?>
                            <div class="segment-transport">
                                <div class="segment-line"></div>
                                <div class="segment-info" 
                                     data-lat-dep="<?php echo $coordsFrom['lat']; ?>"
                                     data-lon-dep="<?php echo $coordsFrom['lon']; ?>"
                                     data-lat-arr="<?php echo $coordsTo['lat']; ?>"
                                     data-lon-arr="<?php echo $coordsTo['lon']; ?>"
                                     data-mode="<?php echo strtolower($t['mode_transport']); ?>">
                                    <span class="segment-icon"><?php echo getTransportIcon($t['mode_transport']); ?></span>
                                    <span class="segment-distance">Calcul...</span>
                                    <span class="segment-separator">•</span>
                                    <span class="segment-time">Calcul...</span>
                                </div>
                            </div>
                        <?php 
                            endif;
                        endif; 
                        ?>
                        
                    <?php endfor; ?>
                </div> 
                
                <div id="map-trajet-<?php echo $t['id']; ?>" class="map-trajet"></div>

            </div>
        </div>
    <?php endforeach; ?>    
</div>

<?php include_once __DIR__ . "/modules/footer.php"; ?>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<script>
    const roadTripData = <?php echo json_encode($jsMapData); ?>;
</script>

<script src="js/vuRoadTrip.js"></script>

</body>
</html>