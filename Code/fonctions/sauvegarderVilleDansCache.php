<?php
function sauvegarderVilleDansCache($nomVille, $lat, $lon, $pdo) {
    if (empty($nomVille) || $lat === null || $lon === null || $lat === '' || $lon === '') return;

    try {
        $stmt = $pdo->prepare("SELECT id FROM lieux_geocodes WHERE nom = ?");
        $stmt->execute([$nomVille]);

        if (!$stmt->fetch()) {
            $stmtInsert = $pdo->prepare("INSERT INTO lieux_geocodes (nom, lat, lon, date_last_use) VALUES (?, ?, ?, NOW())");
            $stmtInsert->execute([$nomVille, (string)$lat, (string)$lon]);
        }
    } catch (Exception $e) {
    }
}
