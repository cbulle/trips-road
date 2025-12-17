<?php
// 1. Démarrer la tamporisation de sortie
// Cela empêche PHP d'envoyer quoi que ce soit au navigateur pour l'instant
ob_start();

// Configuration des erreurs (silence absolu)
ini_set('display_errors', 0);
error_reporting(0);

try {
    // -----------------------------------------------------------------------
    // TA LOGIQUE D'UPLOAD
    // -----------------------------------------------------------------------
    
    // Définition du dossier (Chemin absolu)
    $targetDir = __DIR__ . '/images_roadtrip/';
    
    // URL Web (A ADAPTER si ton projet est dans un sous-dossier !)
    // Exemple : si ton site est localhost/monSite/, mets '/monSite/uploads/images_roadtrip/'
    $targetUrlPath = '/uploads/images_roadtrip/'; 

    if (!file_exists($targetDir)) {
        if (!mkdir($targetDir, 0777, true)) {
            throw new Exception("Impossible de créer le dossier.");
        }
    }

    reset($_FILES);
    $temp = current($_FILES);

    if (!isset($temp['tmp_name']) || empty($temp['tmp_name'])) {
        throw new Exception("Aucun fichier reçu.");
    }

    // Vérification extension
    $ext = pathinfo($temp['name'], PATHINFO_EXTENSION);
    if (!in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
        throw new Exception("Format invalide.");
    }
    
    // Nommage et déplacement
    $fileName = date('Ymd_His') . '_' . uniqid() . '.' . $ext;
    $targetFile = $targetDir . $fileName;

    if (move_uploaded_file($temp['tmp_name'], $targetFile)) {
        $response = ['location' => $targetUrlPath . $fileName];
    } else {
        throw new Exception("Erreur lors de l'écriture du fichier.");
    }

    // -----------------------------------------------------------------------
    // LE NETTOYAGE FINAL (La partie magique)
    // -----------------------------------------------------------------------

    // On efface tout ce qui a pu être écrit avant (espaces, warnings, etc.)
    ob_end_clean(); 
    
    // On envoie les bons headers
    header('Content-Type: application/json');
    http_response_code(200);
    
    // On envoie UNIQUEMENT le JSON
    echo json_encode($response);

} catch (Exception $e) {
    // En cas d'erreur, on nettoie aussi
    ob_end_clean();
    
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

// On coupe tout immédiatement pour éviter qu'un saut de ligne traîne à la fin du fichier
exit;
?>