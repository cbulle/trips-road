<?php
/** @var PDO $pdo */

header('Content-Type: application/json');

if (!isset($_SESSION['utilisateur']['id'])) {
    echo json_encode([]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT nom_lieu, adresse, latitude, longitude, categorie FROM lieux_favoris WHERE id_utilisateur = :uid");
    $stmt->execute(['uid' => $_SESSION['utilisateur']['id']]);
    $favoris = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($favoris);
} catch (Exception $e) {
    echo json_encode([]);
}