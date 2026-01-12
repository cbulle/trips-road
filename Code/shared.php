<?php
require_once __DIR__ . '/include/init.php';
include_once __DIR__ . '/bd/lec_bd.php';
include_once __DIR__ . '/fonctions/InfoItineraire.php';

$token = $_GET['t'] ?? null;

if (!$token) {
    echo "Lien invalide.";
    exit;
}

$stmt = $pdo->prepare("
    SELECT r.*, u.nom, u.prenom, rp.nb_vues
    FROM roadtrip_partages rp
    INNER JOIN roadtrip r ON rp.id_roadtrip = r.id
    INNER JOIN utilisateurs u ON r.id_utilisateur = u.id
    WHERE rp.token = :token
");
$stmt->execute(['token' => $token]);
$roadTrip = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$roadTrip) {
    echo "Ce road trip n'existe pas ou n'est plus disponible.";
    exit;
}

$stmt = $pdo->prepare("UPDATE roadtrip_partages SET nb_vues = nb_vues + 1 WHERE token = :token");
$stmt->execute(['token' => $token]);

$id_roadtrip = $roadTrip['id'];

$stmt = $pdo->prepare("SELECT * FROM trajet WHERE road_trip_id = ? ORDER BY numero");
$stmt->execute([$id_roadtrip]);
$trajets = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <title><?= htmlspecialchars($roadTrip['titre']) ?> - Road Trip Partagé</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/profil.css">
   
</head>
<body>

<div class="share-header">
    <h1><?= htmlspecialchars($roadTrip['titre']) ?></h1>
    <p class="share-info">
        Partagé par <?= htmlspecialchars($roadTrip['prenom'] . ' ' . $roadTrip['nom']) ?>
        • Vu <?= $roadTrip['nb_vues'] ?> fois
    </p>
</div>

<div class="roadtrip-vu">
    <div class="roadtrip-header">
        <?php if (!empty($roadTrip['photo'])): ?>
            <img src="/uploads/roadtrips/<?= htmlspecialchars($roadTrip['photo']) ?>" 
                 style="max-width: 100%; border-radius: 10px; margin-bottom: 20px;">
        <?php endif; ?>
        <p><?= nl2br(htmlspecialchars($roadTrip['description'])) ?></p>
    </div>
    
    <?php foreach ($trajets as $t) : 
        $depart = $t['depart'];
        $arrive = $t['arrivee']; 
        $currentDepartCity = $depart;
        $currentDepartCoords = getCoordonneesDepuisCache($currentDepartCity, $pdo);
    ?>
        <div class="card-vu" data-trajet-id="<?= $t['id']; ?>">
            <div class="trajet-header" onclick="toggleSousEtapes(<?= $t['id']; ?>)">
                <div class="trajet-info">
                    <h2 class="trajet-titre"><?= htmlspecialchars($t['titre']); ?></h2>
                    <div class="trajet-details">
                        <?php if (!empty($t['date_trajet'])) : ?>
                            <div class="trajet-detail-item">
                                <span>📅</span><span><?= date('d/m/Y', strtotime($t['date_trajet'])); ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($t['mode_transport'])) : ?>
                            <div class="trajet-detail-item">
                                <span class="transport-icon"><?= getTransportIcon($t['mode_transport']); ?></span>
                                <strong><?= htmlspecialchars(ucfirst($t['mode_transport'])); ?></strong>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="toggle-icon">▼</div>
            </div>
            
            <div class="sous-etapes-container" id="sous-etapes-<?= $t['id']; ?>">
                <div class="sous-etape-card">
                    <div class="sous-etape-header"><h3><?= htmlspecialchars($depart) ?></h3></div>
                </div>

                <?php 
                $listeEtapes = (isset($etapes[$t['id']]) && count($etapes[$t['id']]) > 0) ? $etapes[$t['id']] : [];
                
                if (empty($listeEtapes)) {
                    $stepsToProcess = [['ville' => $arrive, 'type_transport' => $t['mode_transport'], 'is_arrival' => true]];
                } else {
                    $stepsToProcess = $listeEtapes;
                    $stepsToProcess[] = ['ville' => $arrive, 'type_transport' => $t['mode_transport'], 'is_arrival' => true];
                }

                foreach ($stepsToProcess as $step) :
                    $targetCity = $step['ville'];
                    $targetCoords = getCoordonneesDepuisCache($targetCity, $pdo);
                    $mode = strtolower($step['type_transport'] ?? $t['mode_transport'] ?? 'voiture');
                    $sansAutoroute = $step['sans_autoroute'] ?? $t['sans_autoroute'] ?? 0;
                    $sansPeage = $step['sans_peage'] ?? $t['sans_peage'] ?? 0;

                    $dataAttrs = "";
                    if ($currentDepartCoords && $targetCoords) {
                        $dataAttrs = ' data-lat-dep="'.$currentDepartCoords['lat'].'"' .
                                     ' data-lon-dep="'.$currentDepartCoords['lon'].'"' .
                                     ' data-lat-arr="'.$targetCoords['lat'].'"' .
                                     ' data-lon-arr="'.$targetCoords['lon'].'"' .
                                     ' data-mode="'.$mode.'"' .
                                     ' data-sans-autoroute="'.$sansAutoroute.'"' .
                                     ' data-sans-peage="'.$sansPeage.'"';
                    }
                ?>
                    <section class="timeline">
                        <ul class="js-calculate-distance" <?= $dataAttrs; ?>>
                            <li>
                                <span class="transport-icon"><?= getTransportIcon($mode); ?></span>
                                <strong><?= htmlspecialchars(ucfirst($mode)); ?></strong>
                                <?php if($sansPeage): ?> <span title="Sans péage" style="font-size:0.8em">🚫💶</span> <?php endif; ?>
                                <?php if($sansAutoroute): ?> <span title="Sans autoroute" style="font-size:0.8em">🚫🛣️</span> <?php endif; ?>
                            </li>
                            <li class="result-distance"><span class="loading-data">Calcul...</span></li>
                            <li class="result-time"><span class="loading-data">...</span></li>
                        </ul>
                    </section>

                    <div class="sous-etape-card">
                        <div class="sous-etape-header">
                            <h3><?= htmlspecialchars($targetCity); ?></h3>
                            <?php if (isset($step['numero'])) : ?>
                                <span class="numero-etape">Étape <?= $step['numero']; ?></span>
                            <?php endif; ?>
                        </div>

                        <?php if (!isset($step['is_arrival']) || !$step['is_arrival']): ?>
                            <div class="sous-etape-info">    
                                <?php if (!empty($step['heure'])) : ?>
                                    <span><strong>🕐</strong> <?= htmlspecialchars($step['heure']); ?></span>
                                <?php endif; ?>
                            </div>
                            <?php 
                                $desc = trim($step['description'] ?? '');
                                if (!empty($desc)) : 
                            ?>
                                <div class="tinymce-content" style="margin-top: 15px;">
                                    <?= $desc; ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (isset($step['photos']) && count($step['photos']) > 0) : ?>
                                <div class="photos-container">
                                    <?php foreach ($step['photos'] as $photo) : ?>
                                        <img src="/uploads/sousetapes/<?= htmlspecialchars($photo['photo']); ?>" alt="Photo">
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

<div style="text-align: center; margin: 40px 0; padding: 20px; background: var(--beige_foncé); border-radius: 10px;">
    <p style="font-size: 1.1em; margin-bottom: 15px;">
        Vous aimez ce road trip ? Créez le vôtre !
    </p>
    <a href="/" style="background: var(--orange); color: white; padding: 12px 30px; border-radius: 5px; text-decoration: none; font-weight: bold;">
        Rejoindre Trips & Roads
    </a>
</div>

<script src="js/map.js"></script>

</body>
</html>