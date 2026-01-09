<?php
// Code/formulaire/fav_lieu.php

require_once __DIR__ . '/../include/init.php';
// Ajout de la connexion à la base de données (indispensable)
require_once __DIR__ . '/../bd/lec_bd.php'; 

header('Content-Type: application/json');

// Vérification de la connexion utilisateur
if (!isset($_SESSION['utilisateur']['id'])) {
    echo json_encode(['success' => false, 'message' => 'Vous devez être connecté.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode invalide.']);
    exit;
}

$id_utilisateur = $_SESSION['utilisateur']['id'];
$nom = $_POST['nom'] ?? '';
$adresse = $_POST['adresse'] ?? '';
$lat = $_POST['lat'] ?? 0;
$lon = $_POST['lon'] ?? 0;
$categorie = $_POST['categorie'] ?? 'autre';

if (empty($nom) || empty($lat) || empty($lon)) {
    echo json_encode(['success' => false, 'message' => 'Données incomplètes.']);
    exit;
}

try {
    // Vérifier si la table existe (au cas où elle n'aurait pas été créée)
    // On suppose que $pdo est disponible grâce à lec_bd.php

    // Vérifier si le lieu est déjà en favori (basé sur lat/lon très proches)
    $stmt = $pdo->prepare("SELECT id FROM lieux_favoris WHERE id_utilisateur = :uid AND ABS(latitude - :lat) < 0.0001 AND ABS(longitude - :lon) < 0.0001");
    $stmt->execute(['uid' => $id_utilisateur, 'lat' => $lat, 'lon' => $lon]);
    $existant = $stmt->fetch();

    if ($existant) {
        // Suppression (Retirer des favoris)
        $del = $pdo->prepare("DELETE FROM lieux_favoris WHERE id = :id");
        $del->execute(['id' => $existant['id']]);
        echo json_encode(['success' => true, 'action' => 'removed', 'message' => 'Lieu retiré des favoris.']);
    } else {
        // Ajout
        $ins = $pdo->prepare("INSERT INTO lieux_favoris (id_utilisateur, nom_lieu, adresse, latitude, longitude, categorie) VALUES (:uid, :nom, :adr, :lat, :lon, :cat)");
        $ins->execute([
            'uid' => $id_utilisateur,
            'nom' => $nom,
            'adr' => $adresse,
            'lat' => $lat,
            'lon' => $lon,
            'cat' => $categorie
        ]);
        echo json_encode(['success' => true, 'action' => 'added', 'message' => 'Lieu ajouté aux favoris !']);
    }

} catch (PDOException $e) {
    // En cas d'erreur SQL, on renvoie le détail pour aider au débogage
    echo json_encode(['success' => false, 'message' => 'Erreur BD: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
}