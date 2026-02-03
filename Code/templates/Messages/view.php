<?php
/**
 * @var \App\View\AppView $this
 * @var object $conversation
 * @var object $ami
 * @var array $messages
 * @var int $userId
 * @var int $amiId
 */
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messagerie</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="/css/messagerie.css">
</head>
<body>
<main class="main-index">
    <div class="messagerie-container">
        <div class="conversations-list">
            <h2>Mes messages</h2>
            <a href="<?= $this->Url->build(['action' => 'index']) ?>" class="back-button">
                <i class="material-icons">arrow_back</i> Retour
            </a>
        </div>

        <div class="chat-area">
            <div class="chat-header">
                <span><?= h($ami->prenom . ' ' . $ami->nom) ?></span>
            </div>

            <div class="messages-container" id="messagesContainer">
                <?php foreach ($messages as $message): ?>
                    <div class="message <?= ($message->sender_id === $userId) ? 'sent' : 'received' ?>">
                        <div class="message-text">
                            <?= h($message->body) ?>
                        </div>
                        <span class="message-time">
                                <?= $message->created->format('H:i') ?>
                            </span>
                    </div>
                <?php endforeach; ?>
            </div>

            <form class="message-form" id="messageForm" method="post" action="<?= $this->Url->build(['action' => 'sendMessage']) ?>">
                    <textarea
                        name="body"
                        id="messageInput"
                        placeholder="Votre message..."
                        required></textarea>
                <button type="submit">
                    <i class="material-icons">send</i>
                </button>
            </form>
        </div>
    </div>
</main>

<script>
    window.conversationId = <?= json_encode($conversation->id) ?>;
    window.userId = <?= json_encode($userId) ?>;
    window.amiId = <?= json_encode($amiId) ?>;
</script>

<script src="/js/messagerie.js"></script>
</body>
</html>
