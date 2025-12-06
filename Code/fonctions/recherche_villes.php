<?php
// Fichier : recherche_villes.php

header('Content-Type: application/json');

$terme = isset($_GET['q']) ? $_GET['q'] : '';

// Photon gère très bien les recherches courtes, mais on garde une sécu à 2 caractères
if (strlen($terme) < 2) {
    echo json_encode([]);
    exit;
}

// --- CONFIGURATION DU FILTRAGE À LA SOURCE ---

// 1. URL de l'API Photon (par Komoot, basée sur OSM)
$url = "https://photon.komoot.io/api/?";

// 2. Construction des paramètres
$europe_bbox = '-25,35,40,70'; 

$params = [
    'q' => $terme,
    'lang' => 'fr',
    'limit' => 10,
    // CLÉ DE LA CORRECTION : Le filtre de zone géographique
    'bbox' => $europe_bbox, 
];

// 3. LE SECRET : On force l'API à ne chercher QUE des lieux de type "place"
// On construit la requête manuellement pour ajouter plusieurs fois le même paramètre 'osm_tag'
// Cela agit comme un filtre "OU" : Cherche une ville OU un village OU un bourg.
$query_string = http_build_query($params);

// On ajoute les filtres stricts pour exclure les rues, les lieux touristiques, etc.
// place:city    = Grandes villes
// place:town    = Villes moyennes
// place:village = Villages
// place:hamlet  = Hameaux (optionnel, à retirer si vous voulez éviter les trop petits lieux)
$filtres_source = "&osm_tag=place:city&osm_tag=place:town&osm_tag=place:village";

$url_finale = $url . $query_string . $filtres_source;


// --- APPEL CURL ---

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url_finale);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
// Photon demande moins de restrictions sur le User-Agent, mais c'est bien de le laisser
curl_setopt($ch, CURLOPT_USERAGENT, 'RoadTripApp/1.0'); 

$reponse_json = curl_exec($ch);

if (curl_errno($ch)) {
    echo json_encode([]);
    exit;
}
curl_close($ch);

// --- TRAITEMENT DES RÉSULTATS ---

$data = json_decode($reponse_json, true);
$resultats_select2 = [];

// Photon renvoie du GeoJSON. Les données sont dans ['features']
if (isset($data['features']) && is_array($data['features'])) {
    
    foreach ($data['features'] as $feature) {
        // Les infos utiles sont dans 'properties'
        $props = $feature['properties'];
        
        // Construction du nom affiché
        $nom = $props['name'];
        $pays = isset($props['country']) ? $props['country'] : '';
        
        // On essaie de trouver un contexte (département, état, ou région)
        $contexte = '';
        if (isset($props['state'])) {
            $contexte = $props['state'];
        } elseif (isset($props['county'])) {
            $contexte = $props['county'];
        }

        // Formatage joli : "Lyon, Auvergne-Rhône-Alpes, France"
        $libelle_complet = $nom;
        if ($contexte) $libelle_complet .= ", " . $contexte;
        if ($pays) $libelle_complet .= ", " . $pays;

        $resultats_select2[] = [
            // Pour select2, on garde le nom complet pour éviter les ambiguïtés
            'nom_ville' => $libelle_complet
        ];
    }
}

echo json_encode($resultats_select2);
?>