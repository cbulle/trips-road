<?php
require_once __DIR__ . '/../include/init.php';
include_once __DIR__ . '/../bd/lec_bd.php';

header('Content-Type: application/json');

// Vérifier authentification
if (!isset($_SESSION['utilisateur']['id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'Not authenticated'
    ]);
    exit;
}

try {
    $sessionId = session_id();
    $userId = $_SESSION['utilisateur']['id'];
    
    // Sauvegarder le session_id en BDD pour vérification Socket.IO
    $stmt = $pdo->prepare("
        UPDATE utilisateur 
        SET session_id = ? 
        WHERE id = ?
    ");
    $stmt->execute([$sessionId, $userId]);
    
    echo json_encode([
        'success' => true,
        'sessionId' => $sessionId,
        'userId' => $userId,
        'userName' => $_SESSION['utilisateur']['prenom'] . ' ' . $_SESSION['utilisateur']['nom']
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    error_log("Erreur get_session_id: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'error' => 'Database error'
    ]);
}
?>