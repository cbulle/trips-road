<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Favorite $favorite
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit Favorite'), ['action' => 'edit', $favorite->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Favorite'), ['action' => 'delete', $favorite->id], ['confirm' => __('Are you sure you want to delete # {0}?', $favorite->id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Favorites'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Favorite'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="favorites view content">
            <h3>Détail du favori #<?= h($favorite->id) ?></h3>
            <table>
                <tr>
                    <th><?= __('Roadtrip') ?></th>
                    <td><?= $favorite->hasValue('roadtrip') ? $this->Html->link($favorite->roadtrip->titre, ['controller' => 'Roadtrips', 'action' => 'view', $favorite->roadtrip->id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('Ajouté le') ?></th>
                    <td><?= h($favorite->created) ?></td>
                </tr>
            </table>
            <?= $this->Html->link(__('Retour'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </div>
</div>
