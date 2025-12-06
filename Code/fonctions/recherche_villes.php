<?php
header('Content-Type: application/json');

$terme = trim($_GET['q'] ?? '');
if ($terme === '') {
    echo json_encode([]);
    exit;
}

$url = "https://nominatim.openstreetmap.org/search?" . http_build_query([
    'q' => $terme,
    'format' => 'json',
    'addressdetails' => 1,
    'limit' => 10,
    'accept-language' => 'fr',
]);

$opts = [
    "http" => [
        "method" => "GET",
        "header" => "User-Agent: RoadTripApp/1.0\r\n"
    ]
];

$context = stream_context_create($opts);
$response = @file_get_contents($url, false, $context);

if ($response === false) {
    echo json_encode([]);
    exit;
}

$data = json_decode($response, true);
$resultats = [];

// ------------------------------------------------------------
//  TRAITEMENT DES RÉSULTATS
// ------------------------------------------------------------

foreach ($data as $ville) {
    $addr = $ville['address'] ?? [];
    $nom_lieu = $ville['name'] ?? $ville['display_name'] ?? null;
    
    $nom_ville = $addr['city'] ?? $addr['town'] ?? $addr['village'] ?? $addr['hamlet'] ?? null;
    $nom_principal = $nom_lieu ?: $nom_ville; 
    if (!$nom_principal) continue;
    
    $affichage = $nom_principal;
    $contexte = $addr['state'] ?? $addr['county'] ?? null;
    $pays = $addr['country'] ?? null;
    if ($nom_principal !== $nom_ville && $nom_ville !== null) {
        $affichage .= ", " . $nom_ville;
    }

    if ($contexte) {
        if (strpos($affichage, $contexte) === false) {
             $affichage .= ", " . $contexte;
        }
    }

    if ($pays) {
        if (strpos($affichage, $pays) === false) {
            $affichage .= ", " . $pays;
        }
    }
    $type_lieu = $ville['type'] ?? $ville['class'] ?? null;

    $resultats[] = ['nom_ville' => $affichage];
}

echo json_encode($resultats);
?>