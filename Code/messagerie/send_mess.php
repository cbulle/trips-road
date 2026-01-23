<?php
require_once __DIR__ . '/../include/init.php';
include_once __DIR__ . '/../bd/lec_bd.php';

/** @var PDO $pdo */

if (!isset($_SESSION['utilisateur']['id'])) {
    header('Location: /id.php');
    exit;
}

$id_utilisateur = $_SESSION['utilisateur']['id'];
$conversation_id = $_POST['conversation_id'] ?? null;
$destinataire_id = $_POST['destinataire_id'] ?? null;
$message = trim($_POST['message'] ?? '');

// Si Socket.IO n'est pas disponible, utiliser l'ancienne méthode
if (empty($message) || !$destinataire_id) {
    header('Location: /messagerie.php' . ($conversation_id ? '?conv=' . $conversation_id : ''));
    exit;
}

try {
    // Si pas de conversation_id, créer ou récupérer la conversation
    if (!$conversation_id) {
        $stmt = $pdo->prepare("
            SELECT id FROM conversations 
            WHERE (user1_id = :u1 AND user2_id = :u2) 
               OR (user1_id = :u2 AND user2_id = :u1)
        ");
        $stmt->execute(['u1' => $id_utilisateur, 'u2' => $destinataire_id]);
        $conv = $stmt->fetch();
        
        if ($conv) {
            $conversation_id = $conv['id'];
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO conversations (user1_id, user2_id) 
                VALUES (:u1, :u2)
            ");
            $stmt->execute(['u1' => $id_utilisateur, 'u2' => $destinataire_id]);
            $conversation_id = $pdo->lastInsertId();
        }
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO messages 
        (conversation_id, expediteur_id, destinataire_id, message, lu) 
        VALUES (:conv, :exp, :dest, :msg, 0)
    ");
    $stmt->execute([
        'conv' => $conversation_id,
        'exp' => $id_utilisateur,
        'dest' => $destinataire_id,
        'msg' => $message
    ]);
    
    $stmt = $pdo->prepare("
        UPDATE conversations 
        SET derniere_activite = NOW() 
        WHERE id = :id
    ");
    $stmt->execute(['id' => $conversation_id]);
    
    $_SESSION['message_sent'] = true;
    
} catch (PDOException $e) {
    error_log("Erreur send_message (fallback): " . $e->getMessage());
    $_SESSION['message_error'] = "Erreur lors de l'envoi du message";
}

header('Location: /messagerie.php?conv=' . $conversation_id);
exit;
?>