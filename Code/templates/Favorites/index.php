<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Favorite> $favorites
 */
?>
<div class="favorites index content">
    <h3><?= __('Favorites') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('id') ?></th>
                    <th><?= $this->Paginator->sort('user_id') ?></th>
                    <th><?= $this->Paginator->sort('roadtrip_id') ?></th>
                    <th><?= $this->Paginator->sort('created') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($favorites as $favorite): ?>
                <tr>
                    <td><?= $favorite->hasValue('user') ? $this->Html->link($favorite->user->last_name, ['controller' => 'Users', 'action' => 'view', $favorite->user->id]) : '' ?></td>
                    <td><?= $favorite->hasValue('roadtrip') ? $this->Html->link($favorite->roadtrip->title, ['controller' => 'Roadtrips', 'action' => 'view', $favorite->roadtrip->id]) : '' ?></td>
                    <td><?= h($favorite->created) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $favorite->id]) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $favorite->id]) ?>
                        <?= $this->Form->postLink(
                            __('Delete'),
                            ['action' => 'delete', $favorite->id],
                            [
                                'method' => 'delete',
                                'confirm' => __('Are you sure you want to delete # {0}?', $favorite->id),
                            ]
                        ) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="paginator">
        <ul class="pagination">
            <?= $this->Paginator->first('<< ' . __('first')) ?>
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
            <?= $this->Paginator->last(__('last') . ' >>') ?>
        </ul>
        <p><?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?></p>
    </div>
</div>