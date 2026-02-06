<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Friend $friend
 * @var \App\Model\Entity\Friend $user
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit Friend'), ['action' => 'edit', $friend->user_id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Friend'), ['action' => 'delete', $friend->user_id], ['confirm' => __('Are you sure you want to delete # {0}?', $friend->user_id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Friendships'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Friend'), ['controller'=> 'Friendships','action' => 'add', $user->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(
                '💬 Message',
                ['controller' => 'Messages', 'action' => 'conversation', $friend->id],
                ['class' => 'btn-message']
            ) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="friends view content">
            <h3><?= h($friend->friend->first_name . ' ' . $friend->friend->last_name) ?></h3>
            <table>
                <tr>
                    <th><?= __('User') ?></th>
                    <td><?= $friend->hasValue('user') ? $this->Html->link($friend->user->last_name, ['controller' => 'Users', 'action' => 'view', $friend->user->id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('Friend') ?></th>
                    <td><?= $friend->hasValue('friend') ? $this->Html->link($friend->friend->Array, ['controller' => 'Friendships', 'action' => 'view', $friend->friend->user_id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('Status') ?></th>
                    <td><?= h($friend->status) ?></td>
                </tr>
                <tr>
                    <th><?= __('Created') ?></th>
                    <td><?= h($friend->created) ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>
