<?php
// Ville à tester
$ville = 'Paris';

// URL Nominatim avec HTTP (pour éviter le problème openssl)
$url = "http://nominatim.openstreetmap.org/search?format=json&q=" . urlencode($ville) . "&limit=1&accept-language=fr";

// User-Agent obligatoire pour Nominatim
$opts = [
    "http" => [
        "header" => "User-Agent: RoadTripPlannerTest/1.0\r\n"
    ]
];
$context = stream_context_create($opts);

// Récupérer les données
$response = @file_get_contents($url, false, $context);

if ($response === false) {
    echo "Erreur : impossible de contacter Nominatim.\n";
} else {
    $data = json_decode($response, true);
    if (!$data || !isset($data[0])) {
        echo "Aucun résultat trouvé.\n";
    } else {
        echo "Résultat pour {$ville} :\n";
        echo "Latitude : " . $data[0]['lat'] . "\n";
        echo "Longitude : " . $data[0]['lon'] . "\n";
        echo "Type : " . ($data[0]['type'] ?? 'inconnu') . "\n";
    }
}
