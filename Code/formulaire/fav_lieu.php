<?php
/** @var PDO $pdo */

header('Content-Type: application/json');

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
    $stmt = $pdo->prepare("SELECT id FROM lieux_favoris WHERE id_utilisateur = :uid AND ABS(latitude - :lat) < 0.0001 AND ABS(longitude - :lon) < 0.0001");
    $stmt->execute(['uid' => $id_utilisateur, 'lat' => $lat, 'lon' => $lon]);
    $existant = $stmt->fetch();

    if ($existant) {
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
    echo json_encode(['success' => false, 'message' => 'Erreur BD: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
}