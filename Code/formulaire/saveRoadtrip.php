<?php
require_once __DIR__ . '/../include/init.php';
include __DIR__ . '/../bd/lec_bd.php';
require_once __DIR__ . '/../fonctions/compressImage.php';

header('Content-Type: application/json');

// Désactiver l'affichage des erreurs HTML pour ne pas casser le JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Fonction de log personnalisée pour retourner du JSON en cas d'erreur
function jsonError($message) {
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

/**
 * Fonction pour sauvegarder les coordonnées dans le cache (Table lieux_geocodes)
 * CORRECTION : On utilise 'lieux_geocodes' pour que la page de visualisation retrouve les infos.
 */
function sauvegarderVilleDansCache($nomVille, $lat, $lon, $pdo) {
    if (empty($nomVille) || empty($lat) || empty($lon)) return;
    try {
        // On vérifie si la ville existe déjà dans la table lieux_geocodes
        $stmt = $pdo->prepare("SELECT id FROM lieux_geocodes WHERE nom = ?");
        $stmt->execute([$nomVille]);
        
        if (!$stmt->fetch()) {
            // Insertion avec les colonnes attendues par vuRoadTrip.php (nom, lat, lon, date_last_use)
            $stmtInsert = $pdo->prepare("INSERT INTO lieux_geocodes (nom, lat, lon, date_last_use) VALUES (?, ?, ?, NOW())");
            $stmtInsert->execute([$nomVille, (string)$lat, (string)$lon]);
        }
    } catch (Exception $e) {
        // On ignore les erreurs (doublons, etc) pour ne pas bloquer
    }
}

try {
    if (!isset($_SESSION['utilisateur']['id'])) {
        jsonError("Utilisateur non connecté");
    }
    $id_user = $_SESSION['utilisateur']['id'];

    // Données principales
    $titre = $_POST['titre'] ?? '';
    $description = $_POST['description'] ?? '';
    $visibilite = $_POST['visibilite'] ?? 'prive';
    $statut = $_POST['statut'] ?? 'brouillon';
    
    // Décodage des JSON
    $villes = json_decode($_POST['villes'] ?? '[]', true);
    $trajets = json_decode($_POST['trajets'] ?? '[]', true);

    if (!$titre) {
        jsonError("Le titre est obligatoire");
    }

    // Gestion Mode Édition
    $modeEdition = !empty($_POST['id_roadtrip']);
    $roadTripId = null;
    $anciennePhoto = null;

    if ($modeEdition) {
        $roadTripId = intval($_POST['id_roadtrip']);
        $stmt = $pdo->prepare("SELECT statut, photo FROM roadtrip WHERE id = ? AND id_utilisateur = ?");
        $stmt->execute([$roadTripId, $id_user]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$existing) jsonError("RoadTrip introuvable ou droits insuffisants.");
        
        $anciennePhoto = $existing['photo'];
    }

    // ---------------------------------------------------------
    // 1. GESTION PHOTO DE COUVERTURE (PRINCIPALE)
    // ---------------------------------------------------------
    $photo_cover_name = null;
    $updatePhoto = false;

    // Vérification si un fichier est envoyé
    if (isset($_FILES['photo_cover']) && $_FILES['photo_cover']['error'] != UPLOAD_ERR_NO_FILE) {
        
        $fileError = $_FILES['photo_cover']['error'];
        
        if ($fileError !== UPLOAD_ERR_OK) {
            jsonError("Erreur lors de l'upload de la couverture.");
        }

        // Configuration Dossier
        $uploadDir = __DIR__ . '/../uploads/roadtrips/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Vérification Extension
        $ext = strtolower(pathinfo($_FILES['photo_cover']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            jsonError("Format d'image invalide (JPG, PNG, GIF, WEBP acceptés).");
        }

        // Nom unique
        $photo_cover_name = uniqid('rt_') . '.' . $ext;
        $sourcePath = $_FILES['photo_cover']['tmp_name'];
        $destinationPath = $uploadDir . $photo_cover_name;

        // Compression
        $compressionReussie = compressImage($sourcePath, $destinationPath, 75);

        if (!$compressionReussie) {
            if (!move_uploaded_file($sourcePath, $destinationPath)) {
                jsonError("Erreur technique lors de l'enregistrement de l'image.");
            }
        }

        $updatePhoto = true;

        // Suppression ancienne photo de couverture
        if ($modeEdition && !empty($anciennePhoto)) {
            if (file_exists($uploadDir . $anciennePhoto)) {
                @unlink($uploadDir . $anciennePhoto);
            }
        }
    }

    // ---------------------------------------------------------
    // 2. ENREGISTREMENT SQL (Roadtrip)
    // ---------------------------------------------------------
    $pdo->beginTransaction();

    if ($modeEdition) {
        // UPDATE
        if ($updatePhoto) {
            $stmt = $pdo->prepare("UPDATE roadtrip SET titre=?, description=?, visibilite=?, statut=?, photo=? WHERE id=? AND id_utilisateur=?");
            $stmt->execute([$titre, $description, $visibilite, $statut, $photo_cover_name, $roadTripId, $id_user]);
        } else {
            $stmt = $pdo->prepare("UPDATE roadtrip SET titre=?, description=?, visibilite=?, statut=? WHERE id=? AND id_utilisateur=?");
            $stmt->execute([$titre, $description, $visibilite, $statut, $roadTripId, $id_user]);
        }

        // Nettoyage complet des trajets/sous-étapes existants pour les recréer proprement
        $pdo->prepare("DELETE sep FROM sous_etape_photos sep INNER JOIN sous_etape se ON sep.sous_etape_id = se.id INNER JOIN trajet t ON se.trajet_id = t.id WHERE t.road_trip_id = ?")->execute([$roadTripId]);
        $pdo->prepare("DELETE se FROM sous_etape se INNER JOIN trajet t ON se.trajet_id = t.id WHERE t.road_trip_id = ?")->execute([$roadTripId]);
        $pdo->prepare("DELETE FROM trajet WHERE road_trip_id = ?")->execute([$roadTripId]);

    } else {
        // INSERT
        $stmt = $pdo->prepare("INSERT INTO roadtrip (titre, description, visibilite, statut, id_utilisateur, photo, date_creation) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$titre, $description, $visibilite, $statut, $id_user, $photo_cover_name]);
        $roadTripId = $pdo->lastInsertId();
    }

    // ---------------------------------------------------------
    // 3. INSERT TRAJETS ET SCAN DES IMAGES TINYMCE
    // ---------------------------------------------------------
    foreach ($trajets as $i => $trajet) {
        $depart = $trajet['depart'] ?? '';
        $arrivee = $trajet['arrivee'] ?? '';
        $mode = $trajet['mode'] ?? 'Voiture';
        $date_trajet = $trajet['date'] ?? null;
        $heure_depart = $trajet['heure_depart'] ?? '08:00';

        // SAUVEGARDE DES COORDONNÉES DÉPART/ARRIVÉE DANS LE CACHE
        sauvegarderVilleDansCache($depart, $trajet['departLat'] ?? null, $trajet['departLon'] ?? null, $pdo);
        sauvegarderVilleDansCache($arrivee, $trajet['arriveeLat'] ?? null, $trajet['arriveeLon'] ?? null, $pdo);

        $stmt = $pdo->prepare("INSERT INTO trajet (numero, titre, depart, arrivee, mode_transport, date_trajet, heure_depart, road_trip_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$i + 1, "$depart → $arrivee", $depart, $arrivee, $mode, $date_trajet, $heure_depart, $roadTripId]);
        $trajetId = $pdo->lastInsertId();

        foreach ($trajet['sousEtapes'] ?? [] as $j => $se) {
            $nom = $se['nom'] ?? '';
            $descriptionSousEtape = $se['remarque'] ?? ''; // Contenu HTML de TinyMCE
            
            // SAUVEGARDE DES COORDONNÉES SOUS-ÉTAPE DANS LE CACHE
            sauvegarderVilleDansCache($nom, $se['lat'] ?? null, $se['lon'] ?? null, $pdo);

            $stmt = $pdo->prepare("INSERT INTO sous_etape (numero, ville, description, trajet_id, type_transport, heure) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$j + 1, $nom, $descriptionSousEtape, $trajetId, $mode, $se['heure'] ?? null]);
            $sousEtapeId = $pdo->lastInsertId();

            // --- Scan des images TinyMCE ---
            $pattern = '/\/uploads\/sousetapes\/(rt_img_[a-zA-Z0-9_]+\.(?:jpg|jpeg|png|gif|webp))/i';
            
            if (preg_match_all($pattern, $descriptionSousEtape, $matches)) {
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

    $pdo->commit();
    echo json_encode(['success' => true, 'id' => $roadTripId]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    jsonError("Erreur Serveur: " . $e->getMessage());
}
?>