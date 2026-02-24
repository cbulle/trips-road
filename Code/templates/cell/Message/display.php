<div class="message-sidebar">
    <h3>Vos Conversations</h3>
    <?php foreach ($enriched as $conv): ?>
        <div class="conv-item <?= (isset($amiId) && $amiId == $conv->id) ? 'active' : '' ?>">
            <a href="<?= $this->Url->build(['controller' => 'Messages', 'action' => 'view', $conv->id]) ?>">
                <strong><?= h($conv->ami->username) ?></strong>
                <p><?= h($conv->last_message) ?></p>
                <?php if ($conv->unread_count > 0): ?>
                    <span class="badge"><?= $conv->unread_count ?></span>
                <?php endif; ?>
            </a>
        </div>
    <?php endforeach; ?>
</div>
