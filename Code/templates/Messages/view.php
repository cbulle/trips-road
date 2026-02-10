<?php
/**
 * @var \App\View\AppView $this
 * @var object $ami
 * @var array $messages
 * @var int $userId
 * @var int $amiId
 */
?>



<main class="main-index">
    <div class="messagerie-container">

        <!-- Liste des conversations -->
        <div class="conversations-list">
            <h2>Mes messages</h2>
            <a href="<?= $this->Url->build(['action' => 'index']) ?>" class="back-button">
                <i class="material-icons">arrow_back</i> Retour
            </a>
        </div>

        <!-- Zone de chat -->
        <div class="chat-area">
            <div class="chat-header">
                <?= h($ami->prenom . ' ' . $ami->nom) ?>
            </div>

            <div class="messages-container" id="messagesContainer">
                <?php foreach ($messages as $message): ?>
                    <div class="message <?= ($message->sender_id === $userId) ? 'sent' : 'received' ?>">
                        <div class="message-text"><?= h($message->body) ?></div>
                        <span class="message-time"><?= h($message->created->format('H:i')) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <?= $this->Form->create(null, [
                'url' => ['action' => 'sendMessage'],
                 'class' => 'message-form'
            ]) ?>
            <?= $this->Form->hidden('ami_id', ['value' => $amiId]) ?>
            <?= $this->Form->textarea('body', ['placeholder' => 'Votre message...', 'required' => true]) ?>
            <button type="submit">
                <i class="material-icons">send</i>
            </button>
            <?= $this->Form->end() ?>

        </div>
    </div>
</main>



