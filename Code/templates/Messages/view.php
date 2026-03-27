<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Message> $messages
 * @var \App\Model\Entity\User $friend
 * @var \App\Model\Entity\User $user
 * @var int $userId
 * @var int $friendId
 */
$this->assign('title', 'Discussion avec ' . h($friend->first_name));
$this->assign('mainClass', 'main-index');
?>

<div class="messagerie-container">
    <div class="conversations-list mobile-hidden">
        <h2>Mes messages</h2>
        <?= $this->cell('Message', [$userId, $friendId]) ?>
    </div>

    <div class="chat-area mobile-full">
        <?php if (!empty($friend)): ?>
            <div class="chat-header">
                <div class="chat-user-info">
                    <?php if (!empty($friend->profile_picture)): ?>
                        <img src="/uploads/pp/<?= h($friend->profile_picture) ?>" alt="Avatar">
                    <?php else: ?>
                        <div class="avatar-placeholder"><?= strtoupper(substr($friend->first_name, 0, 1)) ?></div>
                    <?php endif; ?>
                    <span><?= h($friend->first_name . ' ' . $friend->last_name) ?></span>
                </div>
            </div>

            <div class="messages-container" id="messagesContainer">
                <?php foreach ($messages as $msg): ?>
                    <div class="chat-bulle <?= ($msg->sender_id == $userId) ? 'sent' : 'received' ?>">

                        <div class="chat-avatar">
                            <?php if ($msg->sender_id == $userId): ?>
                                <?php if (!empty($user->profile_picture)): ?>
                                    <img src="/uploads/pp/<?= h($user->profile_picture) ?>" alt="Mon Avatar">
                                <?php else: ?>
                                    <div class="avatar-placeholder-small" style="background: var(--bleu_fonce);"><?= strtoupper(substr($user->first_name ?? 'M', 0, 1)) ?></div>
                                <?php endif; ?>
                            <?php else: ?>
                                <?php if (!empty($friend->profile_picture)): ?>
                                    <img src="/uploads/pp/<?= h($friend->profile_picture) ?>" alt="Avatar">
                                <?php else: ?>
                                    <div class="avatar-placeholder-small"><?= strtoupper(substr($friend->first_name, 0, 1)) ?></div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>

                        <div class="chat-content">
                            <p><?= nl2br(h($msg->content)) ?></p>
                            <span class="chat-time"><?= $msg->created->format('H:i') ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?= $this->Form->create(null, ['url' => ['action' => 'sendMessage'], 'class' => 'message-form']) ?>

            <?= $this->Form->hidden('friend_id', ['value' => $friendId]) ?>

            <?= $this->Form->control('body', [
            'type' => 'textarea',
            'id' => 'chat-input',
            'label' => false,
            'placeholder' => 'Écrivez votre message...',
            'required' => true,
            'templates' => [
                'inputContainer' => '{{content}}'
            ]
        ]) ?>

            <button type="submit" title="Envoyer"><i class="material-icons">send</i></button>

            <?= $this->Form->end() ?>
        <?php endif; ?>
    </div>
</div>
