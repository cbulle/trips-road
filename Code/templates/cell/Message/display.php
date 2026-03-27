<?php
/**
 * @var \App\View\AppView $this
 * @var array $formattedConversations
 * @var int $userId
 */
?>

<?php if (empty($formattedConversations)): ?>
    <div class="no-conversations">
        <p>Aucune conversation pour le moment.</p>
    </div>
<?php else: ?>
    <?php foreach ($formattedConversations as $item): ?>
        <?php
        $friend = $item['friend'];
        $lastMsg = $item['lastMessage'];
        $isActive = $item['isActive'];

        $friendName = h($friend->username ?? $friend->first_name . ' ' . $friend->last_name);
        ?>

        <a href="<?= $this->Url->build(['controller' => 'Messages', 'action' => 'view', $friend->id]) ?>"
           class="conversation-item <?= $isActive ? 'active' : '' ?>">

            <div class="conv-avatar">
                <?php if (!empty($friend->profile_picture)): ?>
                    <img src="<?= $this->Url->build('/uploads/pp/' . h($friend->profile_picture)) ?>" alt="Avatar">
                <?php else: ?>
                    <div class="avatar-placeholder">
                        <?= strtoupper(substr($friendName, 0, 1)) ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="conv-info">
                <div class="conv-header">
                    <span class="conv-name"><?= $friendName ?></span>

                    <?php if ($lastMsg && !$lastMsg->is_read && $lastMsg->sender_id !== $userId): ?>
                        <span class="badge-non-lu">Nouveau</span>
                    <?php endif; ?>
                </div>

                <p class="conv-preview">
                    <?php if ($lastMsg): ?>
                        <?= $lastMsg->sender_id === $userId ? '<strong>Vous:</strong> ' : '' ?>

                        <?= h($lastMsg->content) ?>
                    <?php else: ?>
                        <em>Nouvelle conversation</em>
                    <?php endif; ?>
                </p>
            </div>
        </a>
    <?php endforeach; ?>
<?php endif; ?>
