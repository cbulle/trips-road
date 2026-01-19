<?php
function getCoordonneesDepuisFavoris($nomLieu, $id_utilisateur, $pdo) {
    $stmt = $pdo->prepare("SELECT latitude, longitude FROM lieux_favoris WHERE nom_lieu = :nom AND id_utilisateur = :uid LIMIT 1");
    $stmt->execute(['nom' => $nomLieu, 'uid' => $id_utilisateur]);
    $favori = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($favori) {
        return [
            'lat' => $favori['latitude'],
            'lon' => $favori['longitude']
        ];
    }
    return null;
}
