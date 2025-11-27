<?php
require_once __DIR__ . '/../modules/init.php';
include_once __DIR__ . '/../bd/lec_bd.php';

// Vérification que l'utilisateur est connecté
if (!isset($_SESSION['utilisateur']['id'])) {
    header('Location: /id.php');
    exit;
}

$id_utilisateur = $_SESSION['utilisateur']['id'];

// Vérification de l'ID passé dans l'URL
if (!isset($_GET['id'])) {
    die("Aucun ID de road trip fourni.");
}

$id_roadtrip = intval($_GET['id']);

// Vérifier que ce road trip appartient bien à l'utilisateur connecté
$stmt = $pdo->prepare("
    SELECT id 
    FROM roadtrip 
    WHERE id = :id AND id_utilisateur = :uid
");
$stmt->execute([
    'id' => $id_roadtrip,
    'uid' => $id_utilisateur
]);

$roadtrip = $stmt->fetch();

if (!$roadtrip) {
    die("Accès refusé : ce road trip ne vous appartient pas.");
}

// SUPPRESSION DU ROAD TRIP
$delete = $pdo->prepare("DELETE FROM roadtrip WHERE id = :id");
$delete->execute(['id' => $id_roadtrip]);

// Retour à la liste des road trips
header("Location: /../mesRoadTrips.php?deleted=1");
exit;
