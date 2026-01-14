<?php
require_once __DIR__ . '/include/init.php';
include_once __DIR__ . '/bd/lec_bd.php';
include_once __DIR__ . '/fonctions/InfoItineraire.php';

/**
 * Fonctions de géocodage
 */
function getCoordonneesDepuisFavoris($nomLieu, $id_utilisateur, $pdo) {
    $stmt = $pdo->prepare("SELECT latitude, longitude FROM lieux_favoris WHERE nom_lieu = :nom AND id_utilisateur = :uid LIMIT 1");
    $stmt->execute(['nom' => $nomLieu, 'uid' => $id_utilisateur]);
    $favori = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($favori) return ['lat' => $favori['latitude'], 'lon' => $favori['longitude']];
    return null;
}

function geocoderVilleEnDirect($nomVille, $pdo) {
    if (empty($nomVille)) return null;

    $query = urlencode($nomVille);
    $url = "https://nominatim.openstreetmap.org/search?q={$query}&format=json&limit=1&accept-language=fr";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, "SaeRoadTripApp_V2");
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    // Configuration SSL permissive pour éviter les blocages sur certains PC locaux
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $json = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 && $json) {
        $data = json_decode($json, true);
        if (!empty($data) && isset($data[0])) {
            $lat = $data[0]['lat'];
            $lon = $data[0]['lon'];
            try {
                $stmt = $pdo->prepare("INSERT IGNORE INTO lieux_geocodes (nom, lat, lon, date_last_use) VALUES (?, ?, ?, NOW())");
                $stmt->execute([trim($nomVille), $lat, $lon]);
            } catch (Exception $e) {}
            return ['lat' => $lat, 'lon' => $lon];
        }
    }
    return null;
}

// Vérification connexion
if (!isset($_SESSION['utilisateur']['id'])) {
    header('Location: /id.php');
    exit;
}
$id_utilisateur = $_SESSION['utilisateur']['id'];
$id_roadtrip = $_GET['id'] ?? null;

if (!$id_roadtrip) { echo "ID manquant."; exit; }

// Récupération Roadtrip
$stmt = $pdo->prepare("SELECT * FROM roadtrip WHERE id = ?"); 
$stmt->execute([$id_roadtrip]);
$roadTrip = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$roadTrip) { echo "Road trip introuvable."; exit; }

$stmt = $pdo->prepare("SELECT * FROM trajet WHERE road_trip_id = ? ORDER BY numero");
$stmt->execute([$id_roadtrip]);
$trajets = $stmt->fetchAll(PDO::FETCH_ASSOC);

$etapes = [];
$jsMapData = [];

// Boucle de préparation
foreach ($trajets as $trajet) {
    $stmt = $pdo->prepare("SELECT * FROM sous_etape WHERE trajet_id = ? ORDER BY numero");
    $stmt->execute([$trajet['id']]);
    $sousEtapes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($sousEtapes as &$se) {
        $stmtPhoto = $pdo->prepare("SELECT * FROM sous_etape_photos WHERE sous_etape_id = ?");
        $stmtPhoto->execute([$se['id']]);
        $se['photos'] = $stmtPhoto->fetchAll(PDO::FETCH_ASSOC);
    }
    unset($se);
    
    $etapes[$trajet['id']] = $sousEtapes;

    // Géocodage
    $coordsDep = getCoordonneesDepuisFavoris($trajet['depart'], $id_utilisateur, $pdo) 
                 ?: (getCoordonneesDepuisCache($trajet['depart'], $pdo) 
                 ?: geocoderVilleEnDirect($trajet['depart'], $pdo));

    $coordsArr = getCoordonneesDepuisFavoris($trajet['arrivee'], $id_utilisateur, $pdo) 
                 ?: (getCoordonneesDepuisCache($trajet['arrivee'], $pdo) 
                 ?: geocoderVilleEnDirect($trajet['arrivee'], $pdo));

    $sousEtapesCoords = [];
    foreach ($sousEtapes as $se) {
        if (!empty($se['ville'])) {
            $coords = getCoordonneesDepuisFavoris($se['ville'], $id_utilisateur, $pdo) 
                      ?: (getCoordonneesDepuisCache($se['ville'], $pdo) 
                      ?: geocoderVilleEnDirect($se['ville'], $pdo));

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
    
    $jsMapData[] = [ 
        'id' => $trajet['id'],
        'titre' => $trajet['titre'],
        'mode' => strtolower($trajet['mode_transport']),
        'depart' => [
            'lat' => $coordsDep ? $coordsDep['lat'] : null, 
            'lon' => $coordsDep ? $coordsDep['lon'] : null, 
            'nom' => $trajet['depart']
        ],
        'arrivee' => [
            'lat' => $coordsArr ? $coordsArr['lat'] : null, 
            'lon' => $coordsArr ? $coordsArr['lon'] : null, 
            'nom' => $trajet['arrivee']
        ],
        'heure_depart' => $trajet['heure_depart'],
        'sousEtapes' => $sousEtapesCoords,
        'hasCoords' => ($coordsDep && $coordsArr) ? true : false
    ];
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
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster/dist/MarkerCluster.Default.css" />
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include_once __DIR__ . "/modules/header.php"; ?>

<div class="roadtrip-vu">
    <div class="roadtrip-header">
        <h1>
            <?php echo htmlspecialchars($roadTrip['titre']); ?>
            <?php 
                // Badge de statut
                $statut = $roadTrip['statut'] ?? 'brouillon';
                $label = ($statut === 'termine') ? 'Publié' : 'Brouillon';
                $cssClass = ($statut === 'termine') ? 'status-termine' : 'status-brouillon';
            ?>
            <span class="status-badge <?php echo $cssClass; ?>"><?php echo $label; ?></span>
        </h1>
        
        <p><?php echo nl2br($roadTrip['description']); ?></p>

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
                    
                    $timeline = [];
                    $timeline[] = ['ville' => $t['depart'], 'is_departure' => true, 'heure_depart' => $t['heure_depart'] ?? null];
                    foreach ($listeEtapes as $etape) { $timeline[] = $etape; }
                    $timeline[] = ['ville' => $t['arrivee'], 'is_arrival' => true];
                    
                    for ($i = 0; $i < count($timeline); $i++) :
                        $step = $timeline[$i];
                        $villeNom = $step['ville'] ?? $step['nom'] ?? 'Étape';
                        $isDeparture = isset($step['is_departure']);
                        $isArrival = isset($step['is_arrival']);
                        $pauseDuration = $step['heure'] ?? '00:00';
                    ?>
                        <div class="sous-etape-card <?php echo $isDeparture ? 'depart-card' : ($isArrival ? 'arrivee-card' : ''); ?>" 
                             data-is-departure="<?php echo $isDeparture ? '1' : '0'; ?>"
                             data-is-arrival="<?php echo $isArrival ? '1' : '0'; ?>"
                             data-pause="<?php echo htmlspecialchars($pauseDuration); ?>"
                             data-step-index="<?php echo $i; ?>">
                            <div class="sous-etape-header">
                                <h3>
                                    <?php 
                                    if ($isDeparture) echo '🚀 Départ : ';
                                    elseif ($isArrival) echo '🏁 Arrivée : ';
                                    else echo '📍 ';
                                    echo htmlspecialchars($villeNom); 
                                    ?>
                                </h3>
                                <div class="horaire-info">
                                    <?php if ($isDeparture && !empty($t['heure_depart'])): ?>
                                        <span class="horaire-depart">🕐 Départ : <strong><?php echo htmlspecialchars($t['heure_depart']); ?></strong></span>
                                    <?php else: ?>
                                        <span class="horaire-calcule" data-type="<?php echo $isArrival ? 'arrivee' : 'etape'; ?>">
                                            <span class="horaire-loader">⏱️ Calcul...</span>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <?php if (!$isDeparture && !$isArrival): ?>
                                <div class="sous-etape-info">    
                                    <?php if (!empty($step['heure'])) : ?>
                                        <span class="pause-duree">⏰ Temps sur place : <strong><?php echo htmlspecialchars($step['heure']); ?></strong></span>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if (!empty($step['description'])) : ?>
                                    <div class="sous-etape-description">
                                        <h4 class="description-title">📝 Description</h4>
                                        <div class="tinymce-content">
                                            <?php echo $step['description']; ?> 
                                        </div>
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
                        if ($i < count($timeline) - 1) :
                            $nextStep = $timeline[$i + 1];
                            $nextVilleNom = $nextStep['ville'] ?? $nextStep['nom'] ?? 'Étape';
                            
                            $coordsFrom = getCoordonneesDepuisCache($villeNom, $pdo) ?: geocoderVilleEnDirect($villeNom, $pdo);
                            $coordsTo = getCoordonneesDepuisCache($nextVilleNom, $pdo) ?: geocoderVilleEnDirect($nextVilleNom, $pdo);
                            
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
                <div class = "map-container-vu"> 
                    <div id="map-trajet-<?php echo $t['id']; ?>" class="map-trajet"></div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>    
</div>

<?php include_once __DIR__ . "/modules/footer.php"; ?>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.markercluster/dist/leaflet.markercluster.js"></script>
<script>
    const roadTripData = <?php echo json_encode($jsMapData); ?>;
</script>
<script src="js/vuRoadTrip.js"></script>
</body>
</html>