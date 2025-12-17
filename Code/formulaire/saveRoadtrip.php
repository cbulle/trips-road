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
    
    // Décodage des JSON envoyés par le JS
    $villes = json_decode($_POST['villes'] ?? '[]', true);
    $trajets = json_decode($_POST['trajets'] ?? '[]', true);

    if (!$titre || !$description) {
        throw new Exception("Titre et description obligatoires");
    }

    // ---------------------------------------------------------
    // 1. Upload photo de couverture (Ça, ça ne change pas)
    // ---------------------------------------------------------
    $photo_cover_name = null;
    if (!empty($_FILES['photo_cover']['name'])) {
        $ext = pathinfo($_FILES['photo_cover']['name'], PATHINFO_EXTENSION);
        $photo_cover_name = uniqid('rt_') . '.' . $ext;
        move_uploaded_file($_FILES['photo_cover']['tmp_name'], __DIR__ . '/../uploads/roadtrips/' . $photo_cover_name);
    }

    // ---------------------------------------------------------
    // 2. Insert RoadTrip
    // ---------------------------------------------------------
    $stmt = $pdo->prepare("INSERT INTO roadtrip (titre, description, visibilite, id_utilisateur, photo) 
                           VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$titre, $description, $visibilite, $id_user, $photo_cover_name]);
    $roadTripId = $pdo->lastInsertId();

    // ---------------------------------------------------------
    // 3. Insert trajets et sous-étapes
    // ---------------------------------------------------------
    foreach ($trajets as $i => $trajet) {
        $depart = $trajet['depart'] ?? '';
        $arrivee = $trajet['arrivee'] ?? '';
        $mode = $trajet['mode'] ?? 'Voiture';
        
        $sansAutoroute = !empty($trajet['sansAutoroute']) ? 1 : 0;
        $sansPeage = !empty($trajet['sansPeage']) ? 1 : 0;
        $titreTrajet = "$depart → $arrivee";

        $stmt = $pdo->prepare("INSERT INTO trajet (numero, titre, depart, arrivee, mode_transport, road_trip_id, sans_autoroute, sans_peage)
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$i, $titreTrajet, $depart, $arrivee, $mode, $roadTripId, $sansAutoroute, $sansPeage]);
        $trajetId = $pdo->lastInsertId();

        foreach ($trajet['sousEtapes'] ?? [] as $j => $se) {
            $nom = $se['nom'] ?? '';
            
            $remarque = $se['remarque'] ?? '';
            
            $type_transport = $mode; 
            $heure = !empty($se['heure']) ? $se['heure'] : null;
            $seSansAutoroute = !empty($se['sansAutoroute']) ? 1 : 0;
            $seSansPeage = !empty($se['sansPeage']) ? 1 : 0;

            $stmt = $pdo->prepare("INSERT INTO sous_etape (numero, ville, description, trajet_id, type_transport, heure, sans_autoroute, sans_peage) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$j, $nom, $remarque, $trajetId, $type_transport, $heure, $seSansAutoroute, $seSansPeage]);
            $sousEtapeId = $pdo->lastInsertId();            
            if (!empty($remarque)) {
                $doc = new DOMDocument();
                @$doc->loadHTML(mb_convert_encoding($remarque, 'HTML-ENTITIES', 'UTF-8'));
                
                $tags = $doc->getElementsByTagName('img');

                foreach ($tags as $tag) {
                    $src = $tag->getAttribute('src');
                
                    $filename = basename($src);

                    if (strpos($src, '/uploads/sousetapes/') !== false) {
                        
                        $stmt2 = $pdo->prepare("INSERT INTO sous_etape_photos (sous_etape_id, photo) VALUES (?, ?)");
                        $stmt2->execute([$sousEtapeId, $filename]);
                    }
                }
            }
        }
    }

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    error_log("Erreur SaveRoadTrip: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>