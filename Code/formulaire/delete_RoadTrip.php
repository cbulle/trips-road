<?php
/** @var PDO $pdo */

if (!isset($_SESSION['utilisateur']['id'])) {
    header('Location: /login');
    exit;
}

$id_utilisateur = $_SESSION['utilisateur']['id'];

if (!isset($_GET['id'])) {
    die("Aucun ID de road trip fourni.");
}

$id_roadtrip = intval($_GET['id']);

$stmt = $pdo->prepare("SELECT id, photo FROM roadtrip WHERE id = :id AND id_utilisateur = :uid");
$stmt->execute([
    'id' => $id_roadtrip,
    'uid' => $id_utilisateur
]);

$roadtrip = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$roadtrip) {
    die("Accès refusé : ce road trip n'existe pas ou ne vous appartient pas.");
}

if (!empty($roadtrip['photo'])) {
    $cheminImageRt = WEBROOT . 'uploads/roadtrips/' . $roadtrip['photo'];
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
        $cheminPhoto = WEBROOT . 'uploads/sousetapes/' . $photoNom;
        if (file_exists($cheminPhoto)) {
            unlink($cheminPhoto);
        }
    }
}

$delete = $pdo->prepare("DELETE FROM roadtrip WHERE id = :id");
$delete->execute(['id' => $id_roadtrip]);

header("Location: /../mesRoadTrips?msg=supprime");
exit;