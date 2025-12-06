<?php
// Fichier : recherche_villes.php

header('Content-Type: application/json');

// Utilisation de l'opérateur de coalescence nulle (??) et trim pour nettoyer le terme
$terme = trim($_GET['q'] ?? '');
if ($terme === '') {
    echo json_encode([]);
    exit;
}

// ------------------------------------------------------------
//  REQUÊTE NOMINATIM - Pas de filtres OSM pour tout accepter
// ------------------------------------------------------------

// Nominatim par défaut n'applique pas de filtres OSM_TAG, ce qui permet
// de rechercher des rues, des lieux touristiques, et des villes simultanément.
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

    // Priorité 1: Nom du lieu ou du monument (key 'name' ou 'display_name')
    $nom_lieu = $ville['name'] ?? $ville['display_name'] ?? null;
    
    // Priorité 2: Nom de la ville/village (si c'est une adresse)
    $nom_ville = $addr['city'] ?? $addr['town'] ?? $addr['village'] ?? $addr['hamlet'] ?? null;

    // Nom principal affiché : lieu > ville
    $nom_principal = $nom_lieu ?: $nom_ville; 

    // Si on ne trouve ni nom de lieu ni nom de ville pertinent, on passe au suivant
    if (!$nom_principal) continue;
    
    // CLÉ DE LA CORRECTION : Détecter si c'est une adresse, une ville, ou un lieu touristique
    
    $affichage = $nom_principal;

    // Contexte (département/région/état)
    $contexte = $addr['state'] ?? $addr['county'] ?? null;
    $pays = $addr['country'] ?? null;
    
    // --- Logique d'affichage : On ajoute le contexte pour différencier les lieux ---
    
    // Si l'affichage principal n'est PAS déjà le nom de la ville/village, on l'ajoute
    if ($nom_principal !== $nom_ville && $nom_ville !== null) {
        $affichage .= ", " . $nom_ville;
    }

    // Ajout du Contexte (uniquement s'il n'est pas déjà dans le nom de ville)
    if ($contexte) {
        // On évite d'ajouter le département si le nom de la ville est déjà très long
        if (strpos($affichage, $contexte) === false) {
             $affichage .= ", " . $contexte;
        }
    }

    // Pays
    if ($pays) {
        if (strpos($affichage, $pays) === false) {
            $affichage .= ", " . $pays;
        }
    }

    // Optionnel : Ajouter le type de lieu pour la clarté (ex: "Tour Eiffel (attraction), Paris")
    $type_lieu = $ville['type'] ?? $ville['class'] ?? null;
    // if ($type_lieu && $type_lieu !== 'yes' && $type_lieu !== 'boundary') {
    //      $affichage .= " [" . $type_lieu . "]";
    // }


    $resultats[] = ['nom_ville' => $affichage];
}

echo json_encode($resultats);
?>