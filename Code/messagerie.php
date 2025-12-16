<?php
require_once __DIR__ . '/modules/init.php';
include_once __DIR__ . '/bd/lec_bd.php';

if (!isset($_SESSION['utilisateur']['id'])) {
    header('Location: /id.php');
    exit;
}

$id_utilisateur = $_SESSION['utilisateur']['id'];
$conversation_id = $_GET['conv'] ?? null;

// Récupérer les conversations de l'utilisateur
$stmt = $pdo->prepare("
    SELECT 
        c.*,
        CASE 
            WHEN c.user1_id = :user_id THEN u2.id
            ELSE u1.id
        END as ami_id,
        CASE 
            WHEN c.user1_id = :user_id THEN u2.nom
            ELSE u1.nom
        END as ami_nom,
        CASE 
            WHEN c.user1_id = :user_id THEN u2.prenom
            ELSE u1.prenom
        END as ami_prenom,
        CASE 
            WHEN c.user1_id = :user_id THEN u2.photo_profil
            ELSE u1.photo_profil
        END as ami_photo,
        (SELECT COUNT(*) FROM messages m WHERE m.conversation_id = c.id AND m.destinataire_id = :user_id AND m.lu = 0) as non_lus,
        (SELECT message FROM messages m WHERE m.conversation_id = c.id ORDER BY m.date_envoi DESC LIMIT 1) as dernier_message
    FROM conversations c
    INNER JOIN utilisateurs u1 ON c.user1_id = u1.id
    INNER JOIN utilisateurs u2 ON c.user2_id = u2.id
    WHERE c.user1_id = :user_id OR c.user2_id = :user_id
    ORDER BY c.derniere_activite DESC
");
$stmt->execute(['user_id' => $id_utilisateur]);
$conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Si une conversation est sélectionnée, récupérer les messages
$messages = [];
$ami_info = null;
if ($conversation_id) {
    // Marquer les messages comme lus
    $stmt = $pdo->prepare("UPDATE messages SET lu = 1 WHERE conversation_id = :conv_id AND destinataire_id = :user_id");
    $stmt->execute(['conv_id' => $conversation_id, 'user_id' => $id_utilisateur]);
    
    // Récupérer les messages
    $stmt = $pdo->prepare("
        SELECT m.*, u.nom, u.prenom, u.photo_profil
        FROM messages m
        INNER JOIN utilisateurs u ON m.expediteur_id = u.id
        WHERE m.conversation_id = :conv_id
        ORDER BY m.date_envoi ASC
    ");
    $stmt->execute(['conv_id' => $conversation_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Récupérer les infos de l'ami
    $stmt = $pdo->prepare("
        SELECT 
            CASE 
                WHEN c.user1_id = :user_id THEN u2.id
                ELSE u1.id
            END as ami_id,
            CASE 
                WHEN c.user1_id = :user_id THEN u2.nom
                ELSE u1.nom
            END as ami_nom,
            CASE 
                WHEN c.user1_id = :user_id THEN u2.prenom
                ELSE u1.prenom
            END as ami_prenom,
            CASE 
                WHEN c.user1_id = :user_id THEN u2.photo_profil
                ELSE u1.photo_profil
            END as ami_photo
        FROM conversations c
        INNER JOIN utilisateurs u1 ON c.user1_id = u1.id
        INNER JOIN utilisateurs u2 ON c.user2_id = u2.id
        WHERE c.id = :conv_id
    ");
    $stmt->execute(['conv_id' => $conversation_id, 'user_id' => $id_utilisateur]);
    $ami_info = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Messagerie - Trips & Roads</title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/messagerie.css">
</head>
<body>
<?php include_once __DIR__ . "/modules/header.php"; ?>

<main class="main-index">
    <div class="messagerie-container">
        <div class="conversations-list">
            <h2>Messages</h2>
            
            <?php if (empty($conversations)): ?>
                <p class="no-conversations">Aucune conversation pour le moment.</p>
            <?php else: ?>
                <?php foreach ($conversations as $conv): ?>
                    <a href="?conv=<?= $conv['id'] ?>" 
                       class="conversation-item <?= ($conversation_id == $conv['id']) ? 'active' : '' ?>">
                        <div class="conv-avatar">
                            <?php if (!empty($conv['ami_photo'])): ?>
                                <img src="/uploads/profils/<?= htmlspecialchars($conv['ami_photo']) ?>" 
                                     alt="Photo de profil">
                            <?php else: ?>
                                <div class="avatar-placeholder">
                                    <?= strtoupper(substr($conv['ami_prenom'], 0, 1)) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="conv-info">
                            <div class="conv-header">
                                <span class="conv-name">
                                    <?= htmlspecialchars($conv['ami_prenom'] . ' ' . $conv['ami_nom']) ?>
                                </span>
                                <?php if ($conv['non_lus'] > 0): ?>
                                    <span class="badge-non-lu"><?= $conv['non_lus'] ?></span>
                                <?php endif; ?>
                            </div>
                            <p class="conv-preview">
                                <?= htmlspecialchars(mb_substr($conv['dernier_message'] ?? '', 0, 50)) ?>
                                <?= strlen($conv['dernier_message'] ?? '') > 50 ? '...' : '' ?>
                            </p>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="chat-area">
            <?php if ($conversation_id && $ami_info): ?>
                <div class="chat-header">
                    <div class="chat-user-info">
                        <?php if (!empty($ami_info['ami_photo'])): ?>
                            <img src="/uploads/profils/<?= htmlspecialchars($ami_info['ami_photo']) ?>" 
                                 alt="Photo de profil">
                        <?php else: ?>
                            <div class="avatar-placeholder">
                                <?= strtoupper(substr($ami_info['ami_prenom'], 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                        <span><?= htmlspecialchars($ami_info['ami_prenom'] . ' ' . $ami_info['ami_nom']) ?></span>
                    </div>
                </div>

                <div class="messages-container" id="messagesContainer">
                    <?php foreach ($messages as $msg): ?>
                        <div class="message <?= ($msg['expediteur_id'] == $id_utilisateur) ? 'sent' : 'received' ?>">
                            <?php if ($msg['expediteur_id'] != $id_utilisateur): ?>
                                <div class="message-avatar">
                                    <?php if (!empty($msg['photo_profil'])): ?>
                                        <img src="/uploads/pp/<?= htmlspecialchars($msg['photo_profil']) ?>" 
                                             alt="Photo">
                                    <?php else: ?>
                                        <div class="avatar-placeholder-small">
                                            <?= strtoupper(substr($msg['prenom'], 0, 1)) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="message-content">
                                <p><?= nl2br(htmlspecialchars($msg['message'])) ?></p>
                                <span class="message-time">
                                    <?= date('H:i', strtotime($msg['date_envoi'])) ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <form class="message-form" method="POST" action="/messagerie/send_mess.php" id="messageForm">
                    <input type="hidden" name="conversation_id" value="<?= $conversation_id ?>">
                    <input type="hidden" name="destinataire_id" value="<?= $ami_info['ami_id'] ?>">
                    <textarea name="message" placeholder="Écrivez votre message..." required></textarea>
                    <button type="submit">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <path d="M2 21l21-9L2 3v7l15 2-15 2v7z" fill="white"/>
                        </svg>
                    </button>
                </form>
            <?php else: ?>
                <div class="no-chat-selected">
                    <svg width="100" height="100" viewBox="0 0 24 24" fill="none">
                        <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z" 
                              stroke="#ccc" stroke-width="2"/>
                    </svg>
                    <p>Sélectionnez une conversation pour commencer</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<script>
const container = document.getElementById('messagesContainer');
if (container) {
    container.scrollTop = container.scrollHeight;
}

<?php if ($conversation_id): ?>

setInterval(() => {
    fetch('messagerie/get_mess.php?conv=<?= $conversation_id ?>')
        .then(response => response.json())
        .then(data => {
            if (data.new_messages) {
                const messagesContainer = document.getElementById('messages-container');
                messagesContainer.innerHTML = data.messages; 
            }
        });
}, 5000);

<?php endif; ?>
</script>

<?php include_once __DIR__ . "/modules/footer.php"; ?>
</body>
</html>