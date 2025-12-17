<?php
require_once __DIR__ . '/../modules/init.php';
include_once __DIR__ . '/../bd/lec_bd.php';

header('Content-Type: application/json');

if (!isset($_SESSION['utilisateur']['id'])) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$id_utilisateur = $_SESSION['utilisateur']['id'];
$conversation_id = $_GET['conv'] ?? null;
$last_message_id = (int)($_GET['last_id'] ?? 0);

if (!$conversation_id) {
    echo json_encode(['error' => 'No conversation']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT m.id, m.content, m.timestamp, u.nom AS sender_name
        FROM messages m
        JOIN utilisateurs u ON m.sender_id = u.id
        WHERE m.conversation_id = :conv_id AND m.id > :last_id
        ORDER BY m.timestamp ASC
    ");
    $stmt->execute(['conv_id' => $conversation_id, 'last_id' => $last_message_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("
        SELECT id FROM messages 
        WHERE conversation_id = :conv_id 
        ORDER BY timestamp DESC LIMIT 1
    ");
    $stmt->execute(['conv_id' => $conversation_id]);
    $lastMessage = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'new_messages' => count($messages) > 0,
        'messages' => $messages,
        'last_message_id' => $lastMessage['id'] ?? 0
    ]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error']);
}
