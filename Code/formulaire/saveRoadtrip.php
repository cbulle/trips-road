<?php
/** @var PDO $pdo */

@ini_set('memory_limit', '512M');
@ini_set('max_execution_time', 300);
@ini_set('post_max_size', '64M');
@ini_set('upload_max_filesize', '64M');

ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

if (empty($_POST) && empty($_FILES) && isset($_SERVER['CONTENT_LENGTH']) && $_SERVER['CONTENT_LENGTH'] > 0) {
    jsonError("Le Road Trip est trop volumineux pour le serveur de l'université. Essayez de réduire le nombre de photos ou la longueur des descriptions.");
}

try {
    if (!isset($_SESSION['utilisateur']['id'])) {
        jsonError("Votre session a expiré. Veuillez vous reconnecter.");
    }
    $id_user = $_SESSION['utilisateur']['id'];

    $titre       = $_POST['titre'] ?? '';
    $description = $_POST['description'] ?? '';
    $visibilite  = $_POST['visibilite'] ?? 'prive';
    $statut      = $_POST['statut'] ?? 'brouillon';

    if (empty($titre)) jsonError("Le titre est obligatoire.");

    $jsonContent = '[]';
    
    if (isset($_FILES['trajets_file']) && $_FILES['trajets_file']['error'] === UPLOAD_ERR_OK) {
        $jsonContent = file_get_contents($_FILES['trajets_file']['tmp_name']);
    } 
    elseif (isset($_POST['trajets'])) {
        $jsonContent = $_POST['trajets'];
    }

    $trajets = json_decode($jsonContent, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        $extrait = substr($jsonContent, 0, 100);
        jsonError("Données corrompues reçues par le serveur. (Erreur JSON: " . json_last_error_msg() . ")", $extrait);
    }

    $modeEdition = !empty($_POST['id_roadtrip']);
    $roadTripId = null;
    $anciennePhoto = null;

    if ($modeEdition) {
        $roadTripId = intval($_POST['id_roadtrip']);
        $stmt = $pdo->prepare("SELECT statut, photo FROM roadtrip WHERE id = ? AND id_utilisateur = ?");
        $stmt->execute([$roadTripId, $id_user]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$existing) jsonError("RoadTrip introuvable ou vous n'avez pas les droits.");
        $anciennePhoto = $existing['photo'];
    }

    $photo_cover_name = null;
    $updatePhoto = false;

    if (isset($_FILES['photo_cover']) && $_FILES['photo_cover']['error'] != UPLOAD_ERR_NO_FILE) {
        if ($_FILES['photo_cover']['error'] !== UPLOAD_ERR_OK) {
            jsonError("Erreur upload photo couverture (Code: " . $_FILES['photo_cover']['error'] . ")");
        }

        $uploadDir = WEBROOT . 'uploads/roadtrips/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $ext = strtolower(pathinfo($_FILES['photo_cover']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            jsonError("Format image invalide (JPG, PNG, WEBP acceptés).");
        }

        $photo_cover_name = uniqid('rt_') . '.' . $ext;
        $destPath = $uploadDir . $photo_cover_name;

        if (!move_uploaded_file($_FILES['photo_cover']['tmp_name'], $destPath)) {
            jsonError("Erreur technique lors de l'enregistrement de l'image sur le disque.");
        }

        $updatePhoto = true;

        if ($modeEdition && !empty($anciennePhoto) && file_exists($uploadDir . $anciennePhoto)) {
            @unlink($uploadDir . $anciennePhoto);
        }
    }

    $pdo->beginTransaction();

    if ($modeEdition) {
        if ($updatePhoto) {
            $stmt = $pdo->prepare("UPDATE roadtrip SET titre=?, description=?, visibilite=?, statut=?, photo=? WHERE id=? AND id_utilisateur=?");
            $stmt->execute([$titre, $description, $visibilite, $statut, $photo_cover_name, $roadTripId, $id_user]);
        } else {
            $stmt = $pdo->prepare("UPDATE roadtrip SET titre=?, description=?, visibilite=?, statut=? WHERE id=? AND id_utilisateur=?");
            $stmt->execute([$titre, $description, $visibilite, $statut, $roadTripId, $id_user]);
        }

        $pdo->prepare("DELETE sep FROM sous_etape_photos sep INNER JOIN sous_etape se ON sep.sous_etape_id = se.id INNER JOIN trajet t ON se.trajet_id = t.id WHERE t.road_trip_id = ?")->execute([$roadTripId]);
        $pdo->prepare("DELETE se FROM sous_etape se INNER JOIN trajet t ON se.trajet_id = t.id WHERE t.road_trip_id = ?")->execute([$roadTripId]);
        $pdo->prepare("DELETE FROM trajet WHERE road_trip_id = ?")->execute([$roadTripId]);

    } else {
        $stmt = $pdo->prepare("INSERT INTO roadtrip (titre, description, visibilite, statut, id_utilisateur, photo, date_creation) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$titre, $description, $visibilite, $statut, $id_user, $photo_cover_name]);
        $roadTripId = $pdo->lastInsertId();
    }

    foreach ($trajets as $i => $trajet) {
        $depart = $trajet['depart'] ?? '';
        $arrivee = $trajet['arrivee'] ?? '';
        $mode = $trajet['mode'] ?? 'Voiture';
        $date_trajet = (!empty($trajet['date'])) ? $trajet['date'] : null;
        $heure_depart = $trajet['heure_depart'] ?? '08:00';

        sauvegarderVilleDansCache($depart, $trajet['departLat'] ?? null, $trajet['departLon'] ?? null, $pdo);
        sauvegarderVilleDansCache($arrivee, $trajet['arriveeLat'] ?? null, $trajet['arriveeLon'] ?? null, $pdo);

        $stmt = $pdo->prepare("INSERT INTO trajet (numero, titre, depart, arrivee, mode_transport, date_trajet, heure_depart, road_trip_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$i + 1, "$depart → $arrivee", $depart, $arrivee, $mode, $date_trajet, $heure_depart, $roadTripId]);
        $trajetId = $pdo->lastInsertId();

        foreach ($trajet['sousEtapes'] ?? [] as $j => $se) {
            $nom = $se['nom'] ?? '';
            $descSE = $se['remarque'] ?? ''; 
            $heureSE = $se['heure'] ?? '00:00';

            sauvegarderVilleDansCache($nom, $se['lat'] ?? null, $se['lon'] ?? null, $pdo);

            $stmtSE = $pdo->prepare("INSERT INTO sous_etape (numero, ville, description, trajet_id, type_transport, heure) VALUES (?, ?, ?, ?, ?, ?)");
            $stmtSE->execute([$j + 1, $nom, $descSE, $trajetId, $mode, $heureSE]);
            $sousEtapeId = $pdo->lastInsertId();

            $pattern = '/\/uploads\/sousetapes\/(rt_img_[a-zA-Z0-9_]+\.(?:jpg|jpeg|png|gif|webp))/i';
            
            if (preg_match_all($pattern, $descSE, $matches)) {
                $imagesTrouvees = array_unique($matches[1]);
                if (!empty($imagesTrouvees)) {
                    $stmtImg = $pdo->prepare("INSERT INTO sous_etape_photos (sous_etape_id, photo) VALUES (?, ?)");
                    foreach ($imagesTrouvees as $nomImage) {
                        $stmtImg->execute([$sousEtapeId, $nomImage]);
                    }
                }
            }
        }
    }

    // 8. SUCCÈS - COMMIT
    $pdo->commit();
    echo json_encode(['success' => true, 'id' => $roadTripId]);

} catch (Exception $e) {
    // 9. ERREUR - ROLLBACK
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // On log l'erreur serveur pour l'admin
    error_log("Erreur SaveRoadTrip: " . $e->getMessage());
    
    // On renvoie l'erreur au JS
    jsonError("Erreur lors de l'enregistrement : " . $e->getMessage());
}