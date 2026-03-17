<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Favorite> $favorites
 */
?>
<div class="favorites index content">
    <h1 class="sidebar-title">Mes Road Trips Favoris</h1>

    <?php if ($favorites->isEmpty()) : ?>
        <p class="empty-state">Vous n'avez pas encore de favoris.</p>
    <?php else : ?>
        <div class="roadtrip-grid">
            <?php foreach ($favorites as $fav): ?>
                <?php $rt = $fav->roadtrip; ?>
                <div class="roadtrip-card">
                    <?= $this->Html->image('/uploads/roadtrips/' . ($rt->photo_url ?: 'imgBase.png'), ['class' => 'roadtrip-photo']) ?>
                    <div class="card-body">
                        <h3><?= h($rt->title) ?></h3>
                        <p class="card-description"><?= h($this->Text->truncate($rt->description, 100)) ?></p>
                    </div>

                    <div class="roadtrip-actions">
                        <a class="action-btn view" href="<?= $this->Url->build(['controller' => 'Roadtrips', 'action' => 'view', $rt->id]) ?>">
                            <i class="material-icons">visibility</i>
                        </a>

                        <?= $this->Form->postLink(
                            '<i class="material-icons">favorite_border</i>',
                            ['controller' => 'Favorites', 'action' => 'delete', $fav->id],
                            ['confirm' => 'Retirer des favoris ?', 'escape' => false, 'class' => 'action-btn btn-delete']
                        ) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
