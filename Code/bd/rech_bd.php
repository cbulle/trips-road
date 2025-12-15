<?php
// On inclut le fichier qui contient la variable $pdo
require_once __DIR__ . '/lec_bd.php'; 

// On démarre la session SI elle n'est pas déjà démarrée dans init.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$userId = $_SESSION['utilisateur']['id'] ?? null;

// Requête SQL
$sql = 'SELECT * FROM roadtrip WHERE visibilite = "public"';

if ($userId !== null) {
    $sql .= ' OR id_utilisateur = :userId';
}

$sql .= ' ORDER BY date_creation DESC';

$stmt = $pdo->prepare($sql);

if ($userId !== null) {
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
}

$stmt->execute();
$roadtrips = $stmt->fetchAll(PDO::FETCH_ASSOC);

$response = [
    "userId" => $userId, 
    "roadtrips" => $roadtrips
];

// Envoi du JSON
header('Content-Type: application/json');
echo json_encode($response);
exit;
?>