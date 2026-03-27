<?php
/**
 * @var \App\View\AppView $this
 * @var int $userId
 */
$this->assign('title', 'Messagerie');
?>

<main class="main-index">
    <div class="messagerie-container">
        <div class="conversations-list mobile-full">
            <h2>Mes messages</h2>
            <?= $this->cell('Message', [$userId]) ?>
        </div>
        <div class="chat-area mobile-hidden">
            <div class="no-chat-selected">
                <i class="material-icons" id="chat_icon">chat_bubble</i>
                <p>Sélectionnez une conversation</p>
            </div>
        </div>
    </div>
</main>
