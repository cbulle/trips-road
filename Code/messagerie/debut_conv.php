<?php
require_once __DIR__ . '/../include/init.php';
include_once __DIR__ . '/../bd/lec_bd.php';

/** @var PDO $pdo */

if (!isset($_SESSION['utilisateur']['id'])) {
    header('Location: /id');
    exit;
}

$id_utilisateur = $_SESSION['utilisateur']['id'];
$ami_id = $_GET['ami_id'] ?? null;

if (!$ami_id) {
    header('Location: /amis');
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT id FROM conversations 
        WHERE (user1_id = :u1 AND user2_id = :u2) OR (user1_id = :u2 AND user2_id = :u1)
    ");
    $stmt->execute(['u1' => $id_utilisateur, 'u2' => $ami_id]);
    $conv = $stmt->fetch();
    
    if ($conv) {
        $conversation_id = $conv['id'];
    } else {
        $stmt = $pdo->prepare("INSERT INTO conversations (user1_id, user2_id) VALUES (:u1, :u2)");
        $stmt->execute(['u1' => $id_utilisateur, 'u2' => $ami_id]);
        $conversation_id = $pdo->lastInsertId();
    }
    
    header('Location: /messagerie?conv=' . $conversation_id);
} catch (PDOException $e) {
    error_log("Erreur création conversation: " . $e->getMessage());
    header('Location: /amis');
}
exit;