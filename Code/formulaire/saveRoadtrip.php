<?php
require_once __DIR__ . '/../modules/init.php';
include __DIR__ . '/../bd/lec_bd.php';
header('Content-Type: application/json');

try {
    if (!isset($_SESSION['utilisateur']['id'])) {
        throw new Exception("Utilisateur non connecté");
    }
    $id_user = $_SESSION['utilisateur']['id'];

    // Données RoadTrip
    $titre = $_POST['titre'] ?? '';
    $description = $_POST['description'] ?? '';
    $visibilite = $_POST['visibilite'] ?? '';
    $villes = json_decode($_POST['villes'] ?? '[]', true);
    $trajets = json_decode($_POST['trajets'] ?? '[]', true);

    if (!$titre || !$description) {
        throw new Exception("Titre et description obligatoires");
    }

    // ------------------- Upload photo cover -------------------
    $photo_cover_name = null;
    if (!empty($_FILES['photo_cover']['name'])) {
        $ext = pathinfo($_FILES['photo_cover']['name'], PATHINFO_EXTENSION);
        $photo_cover_name = uniqid('rt_') . '.' . $ext;
        move_uploaded_file($_FILES['photo_cover']['tmp_name'], __DIR__ . '/../uploads/roadtrips/' . $photo_cover_name);
    }

    // ------------------- Insert RoadTrip -------------------
    $stmt = $pdo->prepare("INSERT INTO roadtrip (titre, description, visibilite, id_utilisateur, photo) 
                           VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$titre, $description, $visibilite, $id_user, $photo_cover_name]);
    $roadTripId = $pdo->lastInsertId();

    // ------------------- Insert trajets et sous-étapes -------------------
    foreach ($trajets as $i => $trajet) {
        $depart = $trajet['depart'] ?? '';
        $arrivee = $trajet['arrivee'] ?? '';
        $mode = $trajet['mode'] ?? 'Voiture';

        $titreTrajet = "$depart → $arrivee";
        $stmt = $pdo->prepare("INSERT INTO trajet (numero, titre, depart, arrivee, mode_transport, road_trip_id)
                               VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$i, $titreTrajet, $depart, $arrivee, $mode, $roadTripId]);
        $trajetId = $pdo->lastInsertId();

        foreach ($trajet['sousEtapes'] ?? [] as $j => $se) {
            $nom = $se['nom'] ?? '';
            $remarque = $se['remarque'] ?? '';
            $type_transport = $se['type_transport'] ?? 'Voiture';
            $heure = !empty($se['heure']) ? $se['heure'] : null;

            $stmt = $pdo->prepare("INSERT INTO sous_etape (numero, ville, description, trajet_id, type_transport, heure) 
                                   VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$j, $nom, $remarque, $trajetId, $type_transport, $heure]);
            $sousEtapeId = $pdo->lastInsertId();

            // ------------------- Upload photos sous-étape -------------------
            if (!empty($se['photos'])) {
                foreach ($se['photos'] as $fname) {
                    if (isset($_FILES[$fname])) {
                        $filesArray = $_FILES[$fname];

                        // Gestion multiple fichiers si HTML multiple
                        if (is_array($filesArray['name'])) {
                            foreach ($filesArray['name'] as $k => $name) {
                                if ($filesArray['error'][$k] === UPLOAD_ERR_OK) {
                                    $ext = pathinfo($name, PATHINFO_EXTENSION);
                                    $newName = uniqid('se_') . '.' . $ext;
                                    move_uploaded_file($filesArray['tmp_name'][$k], __DIR__ . '/../uploads/sousetapes/' . $newName);

                                    $stmt2 = $pdo->prepare("INSERT INTO sous_etape_photos (sous_etape_id, photo) VALUES (?, ?)");
                                    $stmt2->execute([$sousEtapeId, $newName]);
                                }
                            }
                        } else { // Fichier unique
                            if ($filesArray['error'] === UPLOAD_ERR_OK) {
                                $ext = pathinfo($filesArray['name'], PATHINFO_EXTENSION);
                                $newName = uniqid('se_') . '.' . $ext;
                                move_uploaded_file($filesArray['tmp_name'], __DIR__ . '/../uploads/sousetapes/' . $newName);

                                $stmt2 = $pdo->prepare("INSERT INTO sous_etape_photos (sous_etape_id, photo) VALUES (?, ?)");
                                $stmt2->execute([$sousEtapeId, $newName]);
                            }
                        }
                    }
                }
            }
        }
    }

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}