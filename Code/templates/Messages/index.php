<?php
/**
 * @var \App\View\AppView $this
 * @var array $enriched
 * @var int $userId
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
            <div class="no-chat-selected">
                <i class="material-icons">chat_bubble</i>
                <p>Sélectionnez une conversation</p>
            </div>
        </div>
    </div>
</main>
