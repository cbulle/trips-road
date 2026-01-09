<?php
require_once __DIR__ . '/../modules/init.php';
include __DIR__ . '/../bd/lec_bd.php';
// On inclut votre fonction de compression
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
 * Fonction pour sauvegarder les coordonnées dans le cache
 */
function sauvegarderVilleDansCache($nomVille, $lat, $lon, $pdo) {
    if (empty($nomVille) || empty($lat) || empty($lon)) return;
    try {
        $stmt = $pdo->prepare("SELECT id FROM cache_coordonnees WHERE ville = ?");
        $stmt->execute([$nomVille]);
        if (!$stmt->fetch()) {
            $stmtInsert = $pdo->prepare("INSERT INTO cache_coordonnees (ville, lat, lon) VALUES (?, ?, ?)");
            $stmtInsert->execute([$nomVille, (string)$lat, (string)$lon]);
        }
    } catch (Exception $e) {
        // On ignore les erreurs de cache
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
        if ($existing['statut'] === 'termine') jsonError("Impossible de modifier un roadtrip déjà publié.");
        
        $anciennePhoto = $existing['photo'];
    }

    // ---------------------------------------------------------
    // 1. GESTION PHOTO AVEC COMPRESSION
    // ---------------------------------------------------------
    $photo_cover_name = null;
    $updatePhoto = false;

    // Vérification si un fichier est envoyé
    if (isset($_FILES['photo_cover']) && $_FILES['photo_cover']['error'] != UPLOAD_ERR_NO_FILE) {
        
        $fileError = $_FILES['photo_cover']['error'];
        
        if ($fileError !== UPLOAD_ERR_OK) {
            $errors = [
                UPLOAD_ERR_INI_SIZE   => "L'image est trop lourde (limite serveur php.ini).",
                UPLOAD_ERR_FORM_SIZE  => "L'image dépasse la limite du formulaire.",
                UPLOAD_ERR_PARTIAL    => "L'image n'a été que partiellement envoyée.",
                UPLOAD_ERR_NO_TMP_DIR => "Erreur serveur : Dossier temporaire manquant.",
                UPLOAD_ERR_CANT_WRITE => "Erreur serveur : Échec d'écriture sur le disque.",
                UPLOAD_ERR_EXTENSION  => "Une extension PHP a bloqué l'envoi."
            ];
            jsonError($errors[$fileError] ?? "Erreur inconnue lors de l'upload.");
        }

        // Configuration Dossier
        $uploadDir = __DIR__ . '/../uploads/roadtrips/';
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                jsonError("Impossible de créer le dossier 'uploads/roadtrips/'.");
            }
        }

        // Vérification Extension
        $ext = strtolower(pathinfo($_FILES['photo_cover']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            jsonError("Format d'image invalide (JPG, PNG, GIF, WEBP acceptés).");
        }

        // Vérification Taille (5 Mo)
        if ($_FILES['photo_cover']['size'] > 5 * 1024 * 1024) {
            jsonError("L'image est trop volumineuse (Max 5 Mo).");
        }

        // Nom unique
        $photo_cover_name = uniqid('rt_') . '.' . $ext;
        $sourcePath = $_FILES['photo_cover']['tmp_name'];
        $destinationPath = $uploadDir . $photo_cover_name;

        // --- TENTATIVE DE COMPRESSION ---
        // On essaie de compresser. La fonction renvoie false si le format n'est pas géré (ex: GIF/WEBP)
        // On compresse à 75% de qualité
        $compressionReussie = compressImage($sourcePath, $destinationPath, 75);

        if (!$compressionReussie) {
            // Si la compression échoue (ou format non supporté par le script), on fait un déplacement standard
            if (!move_uploaded_file($sourcePath, $destinationPath)) {
                jsonError("Erreur technique lors de l'enregistrement de l'image.");
            }
        }
        // --------------------------------

        $updatePhoto = true;

        // Suppression ancienne photo si mode édition
        if ($modeEdition && !empty($anciennePhoto)) {
            if (file_exists($uploadDir . $anciennePhoto)) {
                @unlink($uploadDir . $anciennePhoto);
            }
        }
    }

    // ---------------------------------------------------------
    // 2. ENREGISTREMENT SQL
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

        // Nettoyage des trajets existants
        $pdo->prepare("DELETE se FROM sous_etape se INNER JOIN trajet t ON se.trajet_id = t.id WHERE t.road_trip_id = ?")->execute([$roadTripId]);
        $pdo->prepare("DELETE FROM trajet WHERE road_trip_id = ?")->execute([$roadTripId]);

    } else {
        // INSERT
        $stmt = $pdo->prepare("INSERT INTO roadtrip (titre, description, visibilite, statut, id_utilisateur, photo, date_creation) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$titre, $description, $visibilite, $statut, $id_user, $photo_cover_name]);
        $roadTripId = $pdo->lastInsertId();
    }

    // ---------------------------------------------------------
    // 3. INSERT TRAJETS
    // ---------------------------------------------------------
    foreach ($trajets as $i => $trajet) {
        $depart = $trajet['depart'] ?? '';
        $arrivee = $trajet['arrivee'] ?? '';
        $mode = $trajet['mode'] ?? 'Voiture';
        $date_trajet = $trajet['date'] ?? null;
        $heure_depart = $trajet['heure_depart'] ?? '08:00';

        sauvegarderVilleDansCache($depart, $trajet['departLat'] ?? null, $trajet['departLon'] ?? null, $pdo);
        sauvegarderVilleDansCache($arrivee, $trajet['arriveeLat'] ?? null, $trajet['arriveeLon'] ?? null, $pdo);

        $stmt = $pdo->prepare("INSERT INTO trajet (numero, titre, depart, arrivee, mode_transport, date_trajet, heure_depart, road_trip_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$i + 1, "$depart → $arrivee", $depart, $arrivee, $mode, $date_trajet, $heure_depart, $roadTripId]);
        $trajetId = $pdo->lastInsertId();

        foreach ($trajet['sousEtapes'] ?? [] as $j => $se) {
            $nom = $se['nom'] ?? '';
            sauvegarderVilleDansCache($nom, $se['lat'] ?? null, $se['lon'] ?? null, $pdo);

            $stmt = $pdo->prepare("INSERT INTO sous_etape (numero, ville, description, trajet_id, type_transport, heure) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$j + 1, $nom, $se['remarque'] ?? '', $trajetId, $mode, $se['heure'] ?? null]);
        }
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'id' => $roadTripId]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    jsonError("Erreur Serveur: " . $e->getMessage());
}