<?php
function getCoordonneesDepuisCache($nomVille, $pdo) {
    if (empty($nomVille)) return null;
    
    $stmt = $pdo->prepare("SELECT lat, lon FROM lieux_geocodes WHERE nom = :nom LIMIT 1");
    $stmt->execute(['nom' => trim($nomVille)]);
    $cache = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cache) {
        $update = $pdo->prepare("UPDATE lieux_geocodes SET date_last_use = NOW() WHERE nom = ?");
        $update->execute([trim($nomVille)]);
        
        return [
            'lat' => $cache['lat'],
            'lon' => $cache['lon']
        ];
    }
    return null;
}
