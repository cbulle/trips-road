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
if (!$roadTrip) { echo "Road trip introuvable."; exit; }

// Récupérer les trajets
$stmt = $pdo->prepare("SELECT * FROM trajet WHERE road_trip_id = ? ORDER BY numero");
$stmt->execute([$id_roadtrip]);
$trajets = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les sous-étapes
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

function getTransportIcon($type) {
    switch(strtolower($type)) {
        case 'voiture': return '🚗';
        case 'velo': case 'vélo': return '🚴';
        case 'marche': case 'à pied': case 'a pied': return '🚶';
        default: return '🚗';
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
    <style>
        .loading-data { color: #999; font-size: 0.9em; font-style: italic; }
        .error-data { color: red; font-size: 0.8em; }
    </style>
</head>
<body>
<?php include_once __DIR__ . "/modules/header.php"; ?>

<div class="roadtrip-vu">
    <div class="roadtrip-header">
        <h1><?php echo htmlspecialchars($roadTrip['titre']); ?></h1>
        <p><?php echo nl2br(htmlspecialchars($roadTrip['description'])); ?></p>
    </div>
    
    <?php foreach ($trajets as $t) : 
        $depart = $t['depart'];
        $arrive = $t['arrivee']; 
        $currentDepartCity = $depart;
        $currentDepartCoords = getCoordonneesDepuisCache($currentDepartCity, $pdo);
    ?>
        <div class="card-vu" data-trajet-id="<?php echo $t['id']; ?>">
            <div class="trajet-header" onclick="toggleSousEtapes(<?php echo $t['id']; ?>)">
                <div class="trajet-info">
                    <h2 class="trajet-titre"><?php echo htmlspecialchars($t['titre']); ?></h2>
                    <div class="trajet-details">
                        <?php if (!empty($t['date_trajet'])) : ?>
                            <div class="trajet-detail-item">
                                <span>📅</span><span><?php echo date('d/m/Y', strtotime($t['date_trajet'])); ?></span>
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
                
                <div class="sous-etape-card">
                    <div class="sous-etape-header"><h3><?php echo htmlspecialchars($depart) ?></h3></div>
                </div>

                <?php 
                // Gestion des sous-étapes ou du trajet direct
                $listeEtapes = (isset($etapes[$t['id']]) && count($etapes[$t['id']]) > 0) ? $etapes[$t['id']] : [];
                
                // Si pas de sous-étapes, on crée un tableau fictif pour faire le lien direct Départ -> Arrivée
                if (empty($listeEtapes)) {
                    // On simule une étape finale qui est l'arrivée
                    $isDirect = true;
                    $stepsToProcess = [['ville' => $arrive, 'type_transport' => $t['mode_transport'], 'is_arrival' => true]];
                } else {
                    $isDirect = false;
                    $stepsToProcess = $listeEtapes;
                    // On ajoute l'arrivée réelle à la fin pour le dernier tronçon
                    $stepsToProcess[] = ['ville' => $arrive, 'type_transport' => $t['mode_transport'], 'is_arrival' => true];
                }

                foreach ($stepsToProcess as $step) :
                    $targetCity = $step['ville'];
                    $targetCoords = getCoordonneesDepuisCache($targetCity, $pdo);
                    
                    // Récupération du mode
                    $mode = strtolower($step['type_transport'] ?? $t['mode_transport'] ?? 'voiture');

                    // Récupération des préférences (priorité à la sous-étape, sinon au trajet global)
                    $sansAutoroute = $step['sans_autoroute'] ?? $t['sans_autoroute'] ?? 0;
                    $sansPeage = $step['sans_peage'] ?? $t['sans_peage'] ?? 0;

                    // Construction des data-attributes
                    $dataAttrs = "";
                    if ($currentDepartCoords && $targetCoords) {
                        $dataAttrs = ' data-lat-dep="'.$currentDepartCoords['lat'].'"' .
                                     ' data-lon-dep="'.$currentDepartCoords['lon'].'"' .
                                     ' data-lat-arr="'.$targetCoords['lat'].'"' .
                                     ' data-lon-arr="'.$targetCoords['lon'].'"' .
                                     ' data-mode="'.$mode.'"' .
                                     // AJOUT DES NOUVEAUX ATTRIBUTS
                                     ' data-sans-autoroute="'.$sansAutoroute.'"' .
                                     ' data-sans-peage="'.$sansPeage.'"';
                    }
                ?>
                    <section class="timeline">
                        <ul class="js-calculate-distance" <?php echo $dataAttrs; ?>>
                            <li>
                                <span class="transport-icon"><?php echo getTransportIcon($mode); ?></span>
                                <strong><?php echo htmlspecialchars(ucfirst($mode)); ?></strong>
                                <?php if($sansPeage): ?> <span title="Sans péage" style="font-size:0.8em">🚫💶</span> <?php endif; ?>
                                <?php if($sansAutoroute): ?> <span title="Sans autoroute" style="font-size:0.8em">🚫🛣️</span> <?php endif; ?>
                            </li>
                            <li class="result-distance"><span class="loading-data">Calcul...</span></li>
                            <li class="result-time"><span class="loading-data">...</span></li>
                        </ul>
                    </section>

                    <div class="sous-etape-card">
                        <div class="sous-etape-header">
                            <h3><?php echo htmlspecialchars($targetCity); ?></h3>
                            <?php if (isset($step['numero'])) : ?>
                                <span class="numero-etape">Étape <?php echo $step['numero']; ?></span>
                            <?php endif; ?>
                        </div>

                        <?php if (isset($step['is_arrival']) && $step['is_arrival'] == true): ?>
                             <?php else: ?>
                            <div class="sous-etape-info">    
                                <?php if (!empty($step['heure'])) : ?>
                                    <span><strong>🕐</strong> <?php echo htmlspecialchars($step['heure']); ?></span>
                                <?php endif; ?>
                            </div>
                            <?php 
                                $desc = trim($step['description'] ?? '');
                                if (!empty($desc)) : 
                            ?>
                                <p><?php echo $desc; ?></p> 
                            <?php endif; ?>
                            
                            <?php if (isset($step['photos']) && count($step['photos']) > 0) : ?>
                                <div class="photos-container">
                                    <?php foreach ($step['photos'] as $photo) : ?>
                                        <img src="/uploads/sousetapes/<?php echo htmlspecialchars($photo['photo']); ?>" alt="Photo">
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>

                    <?php 
                    $currentDepartCity = $targetCity;
                    $currentDepartCoords = $targetCoords;
                    endforeach; 
                    ?>
            </div>
        </div>
    <?php endforeach; ?>    
</div>

<script src="js/map.js"></script>

<?php include_once __DIR__ . "/modules/footer.php"; ?>
</body>
</html>