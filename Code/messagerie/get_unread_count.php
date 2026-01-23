<?php
require_once __DIR__ . '/../include/init.php';
include_once __DIR__ . '/../bd/lec_bd.php';

header('Content-Type: application/json');

// Vérifier authentification
if (!isset($_SESSION['utilisateur']['id'])) {
    http_response_code(401);
    echo json_encode(['count' => 0]);
    exit;
}

try {
    $userId = $_SESSION['utilisateur']['id'];
    
    // Compter les messages non lus
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM messages 
        WHERE destinataire_id = ? 
        AND lu = 0
    ");
    $stmt->execute([$userId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'count' => (int)$result['count']
    ]);
    
} catch (PDOException $e) {
    error_log("Erreur get_unread_count: " . $e->getMessage());
    echo json_encode(['count' => 0]);
}
?>