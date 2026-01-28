<?php
header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || empty($input['nom'])) {
        echo json_encode(['success' => false, 'message' => 'Nom de ville manquant']);
        exit;
    }

    $nom = trim($input['nom']);
    $lat = $input['lat'] ?? null;
    $lon = $input['lon'] ?? null;

    $stmt = $pdo->prepare("SELECT nom, lat, lon FROM lieux_geocodes WHERE nom = ?");
    $stmt->execute([$nom]);
    $ville = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($ville) {
        echo json_encode([
            'success' => true,
            'cached' => true,
            'ville' => [
                'nom' => $ville['nom'],
                'lat' => $ville['lat'],
                'lon' => $ville['lon']
            ]
        ]);
        exit;
    }

    // Si la ville n'existe pas, on doit obtenir ses coordonnées
    if (!$lat || !$lon) {
        // Appeler Nominatim pour obtenir les coordonnées
        $url = "https://nominatim.openstreetmap.org/search?format=json&q=" 
               . urlencode($nom) . "&limit=1&accept-language=fr";
        
        $context = stream_context_create([
            'http' => [
                'header' => "User-Agent: RoadTripApp/1.0\r\n"
            ]
        ]);
        
        $resp = @file_get_contents($url, false, $context);
        
        if (!$resp) {
            echo json_encode(['success' => false, 'message' => 'Impossible de contacter Nominatim']);
            exit;
        }
        
        $data = json_decode($resp, true);
        if (!$data || !isset($data[0]['lat'], $data[0]['lon'])) {
            echo json_encode(['success' => false, 'message' => 'Ville introuvable']);
            exit;
        }
        
        $lat = $data[0]['lat'];
        $lon = $data[0]['lon'];
    }

    // Insérer la nouvelle ville dans la base
    $stmt = $pdo->prepare("
        INSERT INTO lieux_geocodes (nom, lat, lon, date_last_use)
        VALUES (?, ?, ?, NOW())
    ");
    $stmt->execute([$nom, $lat, $lon]);

    echo json_encode([
        'success' => true,
        'cached' => false,
        'ville' => [
            'nom' => $nom,
            'lat' => $lat,
            'lon' => $lon
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}