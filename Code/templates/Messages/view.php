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
        <div class="conversations-list">
            <h2>Mes messages</h2>

            <?php if (empty($enriched)): ?>
                <p class="no-conversations">Aucune conversation</p>
            <?php else: ?>
                <?php foreach ($enriched as $conv): ?>
                    <a href="<?= $this->Url->build(['action' => 'view', $conv->id]) ?>"
                       class="conversation-item">
                        <div class="conv-header">
                                <span class="conv-name">
                                    <?= h($conv->ami->prenom . ' ' . $conv->ami->nom) ?>
                                </span>
                            <?php if ($conv->unread_count > 0): ?>
                                <span class="badge-unread"><?= $conv->unread_count ?></span>
                            <?php endif; ?>
                        </div>
                        <p class="conv-preview">
                            <?= h(mb_substr($conv->last_message, 0, 50)) ?>
                        </p>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="chat-area">

            <?php if (!empty($ami)): ?>

                <div class="chat-header">
                    <div class="chat-user-info">
                        <?php if (!empty($ami->profile_picture)): ?>
                            <img src="/uploads/pp/<?= h($ami->profile_picture) ?>">
                        <?php else: ?>
                            <div class="avatar-placeholder">
                                <?= strtoupper(substr($ami->prenom, 0, 1)) ?>
                            </div>
                        <?php endif; ?>

                        <span><?= h($ami->prenom . ' ' . $ami->nom) ?></span>
                    </div>
                </div>

                <div class="messages-container" id="messagesContainer">
                    <?php foreach ($messages as $msg): ?>
                        <div class="message <?= ($msg->sender_id == $userId) ? 'sent' : 'received' ?>">

                            <?php if ($msg->sender_id != $userId): ?>
                                <div class="message-avatar">
                                    <?php if (!empty($ami->profile_picture)): ?>
                                        <img src="/uploads/pp/<?= h($ami->profile_picture) ?>">
                                    <?php else: ?>
                                        <div class="avatar-placeholder-small">
                                            <?= strtoupper(substr($ami->prenom, 0, 1)) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <div class="message-content">
                                <p><?= nl2br(h($msg->body)) ?></p>
                                <span class="message-time">
                                    <?= $msg->created->format('H:i') ?>
                                </span>
                            </div>

                        </div>
                    <?php endforeach; ?>
                </div>

                <?= $this->Form->create(null, [
                'url' => ['action' => 'sendMessage'],
                'class' => 'message-form'
            ]) ?>
                <?= $this->Form->hidden('ami_id', ['value' => $amiId]) ?>
                <?= $this->Form->control('body', [
                'type' => 'textarea',
                'label' => false,
                'placeholder' => 'Écrivez votre message...',
                'required' => true
            ]) ?>
                <button type="submit">
                    <i class="material-icons">send</i>
                </button>
                <?= $this->Form->end() ?>

            <?php else: ?>

                <div class="no-chat-selected">
                    <i class="material-icons" id="chat_icon">chat_bubble</i>
                    <p class="aaaa">Sélectionnez une conversation</p>
                </div>

            <?php endif; ?>

        </div>
    </div>
</main>
