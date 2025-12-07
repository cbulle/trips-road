<?php
require_once __DIR__ . '/../modules/init.php';
include_once __DIR__ . '/../bd/lec_bd.php';

if (!isset($_SESSION['utilisateur']['id'])) {
    header('Location: /id.php');
    exit;
}

$id_utilisateur = $_SESSION['utilisateur']['id'];

if (!isset($_GET['id'])) {
    die("Aucun ID de road trip fourni.");
}

$id_roadtrip = intval($_GET['id']);

$stmt = $pdo->prepare("
    SELECT id, photo 
    FROM roadtrip 
    WHERE id = :id AND id_utilisateur = :uid
");
$stmt->execute([
    'id' => $id_roadtrip,
    'uid' => $id_utilisateur
]);

$roadtrip = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$roadtrip) {
    die("Accès refusé : ce road trip ne vous appartient pas.");
}

if (!empty($roadtrip['photo'])) {
    $cheminImageRt = __DIR__ . '/../uploads/roadtrips/' . $roadtrip['photo'];
    
    if (file_exists($cheminImageRt)) {
        unlink($cheminImageRt);
    }
}


$sqlPhotos = "
    SELECT sep.photo
    FROM sous_etape_photos sep
    INNER JOIN sous_etape se ON sep.sous_etape_id = se.id
    INNER JOIN trajet t ON se.trajet_id = t.id
    WHERE t.road_trip_id = :id_rt
";

$stmt = $pdo->prepare($sqlPhotos);
$stmt->execute(['id_rt' => $id_roadtrip]);
$photosSousEtapes = $stmt->fetchAll(PDO::FETCH_COLUMN);

foreach ($photosSousEtapes as $photoNom) {
    if (!empty($photoNom)) {
        $cheminPhoto = __DIR__ . '/../uploads/sousetapes/' . $photoNom;
        if (file_exists($cheminPhoto)) {
            unlink($cheminPhoto);
        }
    }
}


$delete = $pdo->prepare("DELETE FROM roadtrip WHERE id = :id");
$delete->execute(['id' => $id_roadtrip]);

// Retour à la liste des road trips
header("Location: /../mesRoadTrips.php?deleted=1");
exit;