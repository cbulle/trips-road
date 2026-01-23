<?php
require_once __DIR__ . '/../include/init.php';
include_once __DIR__ . '/../bd/lec_bd.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Récupérer l'ID utilisateur demandé
$userId = $_GET['user_id'] ?? null;

// Validation
if (!$userId || !is_numeric($userId)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Invalid user ID'
    ]);
    exit;
}

try {
    // Récupérer la clé publique
    $stmt = $pdo->prepare("
        SELECT public_key, nom, prenom 
        FROM utilisateur 
        WHERE id = ?
    ");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'User not found'
        ]);
        exit;
    }
    
    if (!$user['public_key']) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Public key not found',
            'message' => 'User has not generated keys yet'
        ]);
        exit;
    }
    
    // Retourner la clé publique
    echo json_encode([
        'success' => true,
        'publicKey' => $user['public_key'],
        'userName' => $user['prenom'] . ' ' . $user['nom']
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    error_log("Erreur get_public_key: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'error' => 'Database error'
    ]);
}
?>