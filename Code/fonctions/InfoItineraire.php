<?php
function getCoordonneesDepuisCache($ville, $pdo) {
    $stmt = $pdo->prepare("SELECT lat, lon FROM lieux_geocodes WHERE nom = ?");
    $stmt->execute([$ville]);
    $coords = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($coords) {
        return [
            'lat' => $coords['lat'],
            'lon' => $coords['lon']
        ];
    }
    
    return false; 
}

function calculerDistanceOSRM($coordDepart, $coordArrivee, $modeTransport = 'voiture') {
    $profiles = [
        'voiture' => 'car',
        'velo' => 'bike',
        'pied' => 'foot'
    ];
    
    $profile = $profiles[$modeTransport] ?? 'car';

    $url = "http://router.project-osrm.org/route/v1/{$profile}/" .
           "{$coordDepart['lon']},{$coordDepart['lat']};" .
           "{$coordArrivee['lon']},{$coordArrivee['lat']}?overview=false";

    $response = @file_get_contents($url);

    if (!$response) return false;

    $data = json_decode($response, true);

    if ($data['code'] !== 'Ok' || empty($data['routes'][0]['distance'])) {
        return false;
    }

    return round($data['routes'][0]['distance'] / 1000, 2); // km
}

function calculerTempsOSRM($coordDepart, $coordArrivee, $modeTransport = 'voiture') {
    $profiles = [
        'voiture' => 'car',
        'velo' => 'bike',
        'pied' => 'foot'
    ];
    
    $profile = $profiles[$modeTransport] ?? 'car';

    $url = "http://router.project-osrm.org/route/v1/{$profile}/" .
           "{$coordDepart['lon']},{$coordDepart['lat']};" .
           "{$coordArrivee['lon']},{$coordArrivee['lat']}?overview=false";

    $response = @file_get_contents($url);

    if (!$response) return false;

    $data = json_decode($response, true);

    if ($data['code'] !== 'Ok' || empty($data['routes'][0]['duration'])) {
        return false;
    }

    $dureeSec = $data['routes'][0]['duration'];
    $h = floor($dureeSec / 3600);
    $m = floor(($dureeSec % 3600) / 60);

    return [
        'heures' => $h,
        'minutes' => $m,
        'total_minutes' => floor($dureeSec / 60),
        'texte' => ($h > 0 ? "{$h}h " : "") . "{$m}min"
    ];
}
