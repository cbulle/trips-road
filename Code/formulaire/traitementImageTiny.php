<?php
header('Content-Type: application/json');

if (isset($_FILES['file'])) {
    $file = $_FILES['file'];

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $extensionsAutorisees = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (!in_array($extension, $extensionsAutorisees)) {
        http_response_code(400);
        echo json_encode(['error' => 'Extension non autorisée.']);
        exit;
    }

    $nouveauNom = "rt_img_" . uniqid() . "_" . bin2hex(random_bytes(4)) . "." . $extension;

    $sousDossier = 'uploads/sousetapes/';
    $dossierCible = WEBROOT . $sousDossier;

    // Vérifier et créer le dossier s'il n'existe pas
    if (!is_dir($dossierCible)) {
        mkdir($dossierCible, 0755, true);
    }

    $cheminFinal = $dossierCible . $nouveauNom;

    if (move_uploaded_file($file['tmp_name'], $cheminFinal)) {

        $scriptPath = $_SERVER['SCRIPT_NAME'];
        $webRoot = dirname(dirname($scriptPath));

        if ($webRoot === '/' || $webRoot === '\\') {
            $webRoot = '';
        }
        $location = $webRoot . '/' . $sousDossier . $nouveauNom;

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