<?php
// ============================================================
// CONFIGURATION & INITIALISATION
// ============================================================

// On tente d'augmenter les limites pour ce script (si le serveur l'autorise)
@ini_set('memory_limit', '512M');       // Mémoire vive pour le traitement
@ini_set('max_execution_time', 300);    // Temps max (5 min)
@ini_set('post_max_size', '64M');       // Taille max POST
@ini_set('upload_max_filesize', '64M'); // Taille max Fichier

// Désactiver l'affichage des erreurs HTML (sinon le JSON casse)
ini_set('display_errors', 0);
error_reporting(E_ALL);

// On définit le type de réponse attendu par le JS
header('Content-Type: application/json; charset=utf-8');

// Imports
require_once __DIR__ . '/../include/init.php';
include __DIR__ . '/../bd/lec_bd.php';
require_once __DIR__ . '/../fonctions/compressImage.php';

// ============================================================
// FONCTIONS UTILITAIRES
// ============================================================

/**
 * Renvoie une erreur au format JSON et arrête le script
 */
function jsonError($message, $debug = null) {
    echo json_encode([
        'success' => false, 
        'message' => $message,
        'debug'   => $debug
    ]);
    exit;
}

/**
 * Sauvegarde les coordonnées d'une ville dans le cache (lieux_geocodes)
 */
function sauvegarderVilleDansCache($nomVille, $lat, $lon, $pdo) {
    if (empty($nomVille) || $lat === null || $lon === null || $lat === '' || $lon === '') return;
    
    try {
        $stmt = $pdo->prepare("SELECT id FROM lieux_geocodes WHERE nom = ?");
        $stmt->execute([$nomVille]);
        
        if (!$stmt->fetch()) {
            $stmtInsert = $pdo->prepare("INSERT INTO lieux_geocodes (nom, lat, lon, date_last_use) VALUES (?, ?, ?, NOW())");
            // Forçage de type string pour éviter les erreurs SQL
            $stmtInsert->execute([$nomVille, (string)$lat, (string)$lon]);
        }
    } catch (Exception $e) {
        // On ignore les erreurs de cache, ce n'est pas critique
    }
}

// ============================================================
// VÉRIFICATION PRÉALABLE DU SERVEUR
// ============================================================

// Si $_POST est vide mais que Content-Length > 0, c'est que le serveur a coupé la requête (Trop gros)
if (empty($_POST) && empty($_FILES) && isset($_SERVER['CONTENT_LENGTH']) && $_SERVER['CONTENT_LENGTH'] > 0) {
    jsonError("Le Road Trip est trop volumineux pour le serveur de l'université. Essayez de réduire le nombre de photos ou la longueur des descriptions.");
}

// ============================================================
// LOGIQUE PRINCIPALE
// ============================================================

try {
    // 1. Vérification connexion
    if (!isset($_SESSION['utilisateur']['id'])) {
        jsonError("Votre session a expiré. Veuillez vous reconnecter.");
    }
    $id_user = $_SESSION['utilisateur']['id'];

    // 2. Récupération des champs simples
    $titre       = $_POST['titre'] ?? '';
    $description = $_POST['description'] ?? '';
    $visibilite  = $_POST['visibilite'] ?? 'prive';
    $statut      = $_POST['statut'] ?? 'brouillon';

    if (empty($titre)) jsonError("Le titre est obligatoire.");

    // 3. RÉCUPÉRATION DES DONNÉES DU TRAJET (LE COEUR DU PROBLÈME)
    // On cherche d'abord le FICHIER BLOB envoyé par la nouvelle méthode JS
    $jsonContent = '[]';
    
    if (isset($_FILES['trajets_file']) && $_FILES['trajets_file']['error'] === UPLOAD_ERR_OK) {
        // Méthode optimisée : Lecture du fichier temporaire
        $jsonContent = file_get_contents($_FILES['trajets_file']['tmp_name']);
    } 
    elseif (isset($_POST['trajets'])) {
        // Ancienne méthode (Fallback)
        $jsonContent = $_POST['trajets'];
    }

    // Décodage du JSON
    $trajets = json_decode($jsonContent, true);

    // Vérification de la validité du JSON
    if (json_last_error() !== JSON_ERROR_NONE) {
        // On récupère un extrait pour le debug
        $extrait = substr($jsonContent, 0, 100);
        jsonError("Données corrompues reçues par le serveur. (Erreur JSON: " . json_last_error_msg() . ")", $extrait);
    }

    // 4. GESTION MODE ÉDITION OU CRÉATION
    $modeEdition = !empty($_POST['id_roadtrip']);
    $roadTripId = null;
    $anciennePhoto = null;

    if ($modeEdition) {
        $roadTripId = intval($_POST['id_roadtrip']);
        // Vérification des droits
        $stmt = $pdo->prepare("SELECT statut, photo FROM roadtrip WHERE id = ? AND id_utilisateur = ?");
        $stmt->execute([$roadTripId, $id_user]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$existing) jsonError("RoadTrip introuvable ou vous n'avez pas les droits.");
        $anciennePhoto = $existing['photo'];
    }

    // 5. GESTION PHOTO DE COUVERTURE
    $photo_cover_name = null;
    $updatePhoto = false;

    if (isset($_FILES['photo_cover']) && $_FILES['photo_cover']['error'] != UPLOAD_ERR_NO_FILE) {
        if ($_FILES['photo_cover']['error'] !== UPLOAD_ERR_OK) {
            jsonError("Erreur upload photo couverture (Code: " . $_FILES['photo_cover']['error'] . ")");
        }

        $uploadDir = __DIR__ . '/../uploads/roadtrips/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $ext = strtolower(pathinfo($_FILES['photo_cover']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            jsonError("Format image invalide (JPG, PNG, WEBP acceptés).");
        }

        $photo_cover_name = uniqid('rt_') . '.' . $ext;
        $destPath = $uploadDir . $photo_cover_name;

        // On tente de déplacer le fichier (il est déjà compressé par le JS normalement)
        if (!move_uploaded_file($_FILES['photo_cover']['tmp_name'], $destPath)) {
            jsonError("Erreur technique lors de l'enregistrement de l'image sur le disque.");
        }

        $updatePhoto = true;

        // Suppression ancienne photo
        if ($modeEdition && !empty($anciennePhoto) && file_exists($uploadDir . $anciennePhoto)) {
            @unlink($uploadDir . $anciennePhoto);
        }
    }

    // 6. DÉBUT DE LA TRANSACTION SQL (Tout ou Rien)
    $pdo->beginTransaction();

    if ($modeEdition) {
        // MISE À JOUR ROADTRIP
        if ($updatePhoto) {
            $stmt = $pdo->prepare("UPDATE roadtrip SET titre=?, description=?, visibilite=?, statut=?, photo=? WHERE id=? AND id_utilisateur=?");
            $stmt->execute([$titre, $description, $visibilite, $statut, $photo_cover_name, $roadTripId, $id_user]);
        } else {
            $stmt = $pdo->prepare("UPDATE roadtrip SET titre=?, description=?, visibilite=?, statut=? WHERE id=? AND id_utilisateur=?");
            $stmt->execute([$titre, $description, $visibilite, $statut, $roadTripId, $id_user]);
        }

        // SUPPRESSION DES ANCIENNES DONNÉES (Pour recréer proprement)
        // On supprime d'abord les liens photos, puis les sous-étapes, puis les trajets
        $pdo->prepare("DELETE sep FROM sous_etape_photos sep INNER JOIN sous_etape se ON sep.sous_etape_id = se.id INNER JOIN trajet t ON se.trajet_id = t.id WHERE t.road_trip_id = ?")->execute([$roadTripId]);
        $pdo->prepare("DELETE se FROM sous_etape se INNER JOIN trajet t ON se.trajet_id = t.id WHERE t.road_trip_id = ?")->execute([$roadTripId]);
        $pdo->prepare("DELETE FROM trajet WHERE road_trip_id = ?")->execute([$roadTripId]);

    } else {
        // CRÉATION NOUVEAU ROADTRIP
        $stmt = $pdo->prepare("INSERT INTO roadtrip (titre, description, visibilite, statut, id_utilisateur, photo, date_creation) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$titre, $description, $visibilite, $statut, $id_user, $photo_cover_name]);
        $roadTripId = $pdo->lastInsertId();
    }

    // 7. INSERTION DES TRAJETS ET SOUS-ÉTAPES
    foreach ($trajets as $i => $trajet) {
        $depart = $trajet['depart'] ?? '';
        $arrivee = $trajet['arrivee'] ?? '';
        $mode = $trajet['mode'] ?? 'Voiture';
        $date_trajet = (!empty($trajet['date'])) ? $trajet['date'] : null;
        $heure_depart = $trajet['heure_depart'] ?? '08:00';

        // Cache des villes
        sauvegarderVilleDansCache($depart, $trajet['departLat'] ?? null, $trajet['departLon'] ?? null, $pdo);
        sauvegarderVilleDansCache($arrivee, $trajet['arriveeLat'] ?? null, $trajet['arriveeLon'] ?? null, $pdo);

        // Insert Trajet
        $stmt = $pdo->prepare("INSERT INTO trajet (numero, titre, depart, arrivee, mode_transport, date_trajet, heure_depart, road_trip_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$i + 1, "$depart → $arrivee", $depart, $arrivee, $mode, $date_trajet, $heure_depart, $roadTripId]);
        $trajetId = $pdo->lastInsertId();

        // Insert Sous-étapes
        foreach ($trajet['sousEtapes'] ?? [] as $j => $se) {
            $nom = $se['nom'] ?? '';
            $descSE = $se['remarque'] ?? ''; 
            $heureSE = $se['heure'] ?? '00:00';

            // Cache sous-étape
            sauvegarderVilleDansCache($nom, $se['lat'] ?? null, $se['lon'] ?? null, $pdo);

            $stmtSE = $pdo->prepare("INSERT INTO sous_etape (numero, ville, description, trajet_id, type_transport, heure) VALUES (?, ?, ?, ?, ?, ?)");
            $stmtSE->execute([$j + 1, $nom, $descSE, $trajetId, $mode, $heureSE]);
            $sousEtapeId = $pdo->lastInsertId();

            // SCAN DES IMAGES TINYMCE (Pour la table sous_etape_photos)
            // On cherche les images qui sont stockées dans /uploads/
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