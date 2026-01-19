<?php

function geocoderVilleEnDirect($nomVille, $pdo) {
    if (empty($nomVille)) return null;

    $query = urlencode($nomVille);
    $url = "https://nominatim.openstreetmap.org/search?q={$query}&format=json&limit=1&accept-language=fr";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, "SaeRoadTripApp_PublicView");
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    // Fix SSL pour compatibilité maximale
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $json = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 && $json) {
        $data = json_decode($json, true);
        if (!empty($data) && isset($data[0])) {
            $lat = $data[0]['lat'];
            $lon = $data[0]['lon'];
            try {
                $stmt = $pdo->prepare("INSERT IGNORE INTO lieux_geocodes (nom, lat, lon, date_last_use) VALUES (?, ?, ?, NOW())");
                $stmt->execute([trim($nomVille), $lat, $lon]);
            } catch (Exception $e) {}
            return ['lat' => $lat, 'lon' => $lon];
        }
    }
    return null;
}
