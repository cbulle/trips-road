<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../bd/lec_bd.php';

try {
    session_start();
    if (!isset($pdo) || !$pdo instanceof PDO) throw new Exception("Connexion PDO introuvable.");

    if (!isset($_POST['roadtrip'])) throw new Exception("Aucune donnée 'roadtrip' reçue.");

    $data = json_decode($_POST['roadtrip'], true);
    if ($data === null) throw new Exception("JSON invalide.");

    $roadtripData = $data['roadtrip'] ?? null;
    $trajets = $data['trajets'] ?? [];

    $id_utilisateur = $roadtripData['id_utilisateur'] ?? $_SESSION['user_id'] ?? null;
    if (!$id_utilisateur) throw new Exception("Utilisateur non identifié.");
    if (empty($roadtripData['titre'])) throw new Exception("Le titre du roadtrip est requis.");
    if (count($trajets) < 1) throw new Exception("Au moins un trajet est requis.");

    $pdo->beginTransaction();

    $stmt = $pdo->prepare("INSERT INTO roadtrip (titre, description, visibilite, id_utilisateur) VALUES (?, ?, ?, ?)");
    $stmt->execute([$roadtripData['titre'], $roadtripData['description'] ?? '', $roadtripData['visibilite'] ?? 'public', $id_utilisateur]);
    $roadtripId = $pdo->lastInsertId();

    foreach ($trajets as $trajet) {
        $stmt = $pdo->prepare("INSERT INTO trajet (numero, titre, depart, arrivee, date_trajet, mode_transport, road_trip_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $trajet['numero'],
            $trajet['titre'],
            $trajet['depart'],
            $trajet['arrivee'],
            $trajet['date_trajet'] ?? date('Y-m-d H:i:s'),
            $trajet['mode_transport'] ?? 'Voiture',
            $roadtripId
        ]);
        $trajetId = $pdo->lastInsertId();

        foreach ($trajet['sousEtapes'] as $se) {
            $photos = [];
            if (!empty($se['photos'])) {
                foreach ($se['photos'] as $index => $file) {
                    if (isset($_FILES[$file])) {
                        $tmpName = $_FILES[$file]['tmp_name'];
                        $ext = pathinfo($_FILES[$file]['name'], PATHINFO_EXTENSION);
                        $filename = uniqid('photo_') . '.' . $ext;
                        $destination = __DIR__ . '/uploads/' . $filename;
                        move_uploaded_file($tmpName, $destination);
                        $photos[] = $filename;
                    }
                }
            }
            $photosJson = json_encode($photos);

            $stmt = $pdo->prepare("INSERT INTO sous_etape (numero, ville, description, photos, mode_transport, trajet_id) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $se['numero'],
                $se['ville'],
                $se['description'] ?? '',
                $photosJson,
                $se['mode_transport'] ?? $trajet['mode_transport'] ?? 'Voiture',
                $trajetId
            ]);
        }
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Roadtrip sauvegardé.', 'roadtripId' => $roadtripId]);

} catch (Exception $ex) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $ex->getMessage()]);
}
