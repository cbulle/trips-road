<?php
require_once __DIR__ . '/../bd/lec_bd.php'; // ton fichier PDO

header("Content-Type: application/json; charset=utf-8");

if (!isset($_GET['q']) || empty(trim($_GET['q']))) {
    echo json_encode(['error' => 'No query']);
    exit;
}

$nom = trim($_GET['q']);

$stmt = $pdo->prepare("SELECT lat, lon FROM lieux_geocodes WHERE nom = ?");
$stmt->execute([$nom]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row) {
    $pdo->prepare("UPDATE lieux_geocodes SET date_last_use = NOW() WHERE nom = ?")->execute([$nom]);

    echo json_encode([
        'from' => 'cache',
        'lat' => $row['lat'],
        'lon' => $row['lon']
    ]);
    exit;
}

$url = "https://nominatim.openstreetmap.org/search?format=json&q=" . urlencode($nom) .
       "&limit=1&accept-language=fr";

$opts = [
    "http" => [
        "header" => "User-Agent: RoadTripPlanner/1.0\r\n"
    ]
];
$context = stream_context_create($opts);

$response = @file_get_contents($url, false, $context);

if ($response === false) {
    echo json_encode(['error' => 'Nominatim failed']);
    exit;
}

$data = json_decode($response, true);

if (!$data || !isset($data[0])) {
    echo json_encode(['error' => 'No results']);
    exit;
}

$lat = $data[0]['lat'];
$lon = $data[0]['lon'];
$type = $data[0]['type'] ?? 'unknown';

$stmt = $pdo->prepare("
    INSERT INTO lieux_geocodes (nom, lat, lon, type)
    VALUES (?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE lat = VALUES(lat), lon = VALUES(lon)
");
$stmt->execute([$nom, $lat, $lon, $type]);

echo json_encode([
    'from' => 'nominatim',
    'lat' => $lat,
    'lon' => $lon
]);
