<?php
include_once __DIR__ . '/lec_bd.php';
$userId = $_SESSION['utilisateur']['id'] ?? null;


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

header('Content-Type: application/json');
echo json_encode($response);