<?php
header('Content-Type: application/json');

$terme = trim($_GET['q'] ?? '');

// 1. Validation stricte : pas de recherche sous 3 caractères
if (strlen($terme) < 3) {
    echo json_encode([]);
    exit;
}

// ------------------------------------------------------------
//  SYSTÈME DE CACHE (Fichier)
// ------------------------------------------------------------
// Création d'un dossier de cache s'il n'existe pas
$cacheDir = __DIR__ . '/../cache_nominatim/';
if (!is_dir($cacheDir)) {
    @mkdir($cacheDir, 0777, true);
}

// Nom du fichier unique pour cette recherche (ex: recherche_lyon.json)
$cacheFile = $cacheDir . 'search_' . md5(strtolower($terme)) . '.json';
$cacheTime = 7 * 24 * 60 * 60; // Durée du cache : 7 jours

// Si le fichier existe et est récent, on l'utilise direct (0 délai réseau)
if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheTime)) {
    echo file_get_contents($cacheFile);
    exit;
}

// ------------------------------------------------------------
//  APPEL API VIA CURL (Plus robuste que file_get_contents)
// ------------------------------------------------------------
$url = "https://nominatim.openstreetmap.org/search?" . http_build_query([
    'q' => $terme,
    'format' => 'json',
    'addressdetails' => 1,
    'limit' => 5, // Réduit à 5 pour alléger le JSON
    'accept-language' => 'fr',
]);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_USERAGENT, "RoadTripApp/1.0 (Contact: admin@tonsite.com)");
curl_setopt($ch, CURLOPT_TIMEOUT, 3); // Timeout de 3 secondes max !
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Si échec ou pas de réponse 200, on renvoie vide
if ($response === false || $httpCode !== 200) {
    echo json_encode([]);
    exit;
}

$data = json_decode($response, true);
$resultats = [];

// ------------------------------------------------------------
//  TRAITEMENT DES RÉSULTATS
// ------------------------------------------------------------
if (is_array($data)) {
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

        if ($contexte && strpos($affichage, $contexte) === false) {
             $affichage .= ", " . $contexte;
        }

        if ($pays && strpos($affichage, $pays) === false) {
            $affichage .= ", " . $pays;
        }

        $resultats[] = ['nom_ville' => $affichage];
    }
}

// Encodage final
$jsonOutput = json_encode($resultats);

// Sauvegarde dans le cache pour la prochaine fois
file_put_contents($cacheFile, $jsonOutput);

echo $jsonOutput;
?>