<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\History> $historyRecords
 */

$this->assign('title', '🕓 Mon Historique');
$this->assign('mainClass', 'history-page');
?>

<div>
    <div class="flex-header-tools">
        <h1>🕓 Mon Historique</h1>

        <?php if (!$historyRecords->isEmpty()): ?>
            <?= $this->Form->postLink(
                '<i class="material-icons icon-align-middle">delete_sweep</i> Tout effacer',
                ['action' => 'deleteHistory'],
                [
                    'escape' => false,
                    'class' => 'btn-clear-history btn-danger-custom',
                    'confirm' => 'Voulez-vous vraiment effacer tout votre historique ?'
                ]
            ) ?>
        <?php endif; ?>
    </div>

    <?= $this->Flash->render() ?>

    <?php if ($historyRecords->isEmpty()): ?>
        <p class="text-center-empty">
            Vous n'avez consulté aucun road trip récemment.
        </p>
        <div style="text-align: center;"> <?= $this->Html->link(
                'Explorer les road trips',
                ['controller' => 'Roadtrips', 'action' => 'publicRoadtrips'],
                ['class' => 'btn-view btn-padded']
            ) ?>
        </div>
    <?php else: ?>

        <div class="roadtrip-grid">
            <?php foreach ($historyRecords as $item): ?>
                <?php
                $rt = $item->roadtrip;
                if (!$rt) continue;
                ?>

                <div class="roadtrip-card">
                    <?= $this->Html->image($rt->cover_image, [
                        'alt' => 'Photo du road trip',
                        'class' => 'roadtrip-photo',
                        'url' => ['action' => 'view', $rt->id]
                    ]) ?>

                    <h3><?= h($rt->title) ?></h3>

                    <span class="status-badge badge-dark">
                        👁️ Vu le <?= $item->created->format('d/m/Y') ?>
                    </span>

                    <p><?= h($this->Text->truncate($rt->description, 100)) ?></p>

                    <p class="creator-info">
                        Proposé par :
                        <strong><?= h($rt->user->username ?? 'Utilisateur inconnu') ?></strong>
                    </p>

                    <div class="roadtrip-buttons">
                        <?= $this->Html->link(
                            '<i class="material-icons">visibility</i>',
                            ['controller' => 'Roadtrips', 'action' => 'view', $rt->id],
                            ['escape' => false, 'class' => 'btn-view', 'title' => 'Revoir ce roadtrip']
                        ) ?>

                        <?= $this->Html->link(
                            '<i class="material-icons">favorite_border</i>',
                            ['controller' => 'Favorites', 'action' => 'add', $rt->id, '?' => ['redirect' => 'history']],
                            ['escape' => false, 'class' => 'btn-favori', 'title' => 'Ajouter aux favoris']
                        ) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
