<?php
require_once __DIR__ . '/../include/init.php';

header('Content-Type: application/json');

if (isset($_FILES['file'])) {
    $file = $_FILES['file'];
    
    // Vérification extension
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $extensionsAutorisees = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (!in_array($extension, $extensionsAutorisees)) {
        http_response_code(400);
        echo json_encode(['error' => 'Extension non autorisée.']);
        exit;
    }

    $nouveauNom = "rt_img_" . uniqid() . "_" . bin2hex(random_bytes(4)) . "." . $extension;
    
    // Chemin PHYSIQUE (pour enregistrer le fichier sur le disque)
    $dossierCible = __DIR__ . '/../uploads/sousetapes/';
    if (!is_dir($dossierCible)) {
        mkdir($dossierCible, 0755, true);
    }
    $cheminFinal = $dossierCible . $nouveauNom;

    if (move_uploaded_file($file['tmp_name'], $cheminFinal)) {
        
        // --- CALCUL DU CHEMIN WEB (URL) CORRIGÉ ---
        $scriptPath = $_SERVER['SCRIPT_NAME'];
        $webRoot = dirname(dirname($scriptPath));
        
        // CORRECTION : Si le site est à la racine, dirname renvoie "/" ou "\".
        // On le vide pour éviter le double slash "//uploads"
        if ($webRoot === '/' || $webRoot === '\\') {
            $webRoot = '';
        }
        
        // On construit l'URL
        $location = $webRoot . '/uploads/sousetapes/' . $nouveauNom;
        
        // Nettoyage des backslashes éventuels sur Windows
        $location = str_replace('\\', '/', $location);

        echo json_encode(['location' => $location]);

    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Impossible de sauvegarder le fichier.']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Aucun fichier reçu.']);
}
?>