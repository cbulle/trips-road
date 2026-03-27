<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Friend $friendship
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading">Actions</h4>
            <?= $this->Html->link('Modifier l\'ami', ['action' => 'edit', $friendship->user_id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink('Supprimer l\'ami', ['action' => 'delete', $friendship->id], ['confirm' => 'Voulez-vous vraiment supprimer cet ami ?', 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link('Liste des amis', ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link('Nouveau', ['action' => 'add'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(
                '💬 Message',
                ['controller' => 'Messages', 'action' => 'start', $friendship->friend_id],
                ['class' => 'btn-message']
            ) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="friends view content">
            <h3>Détails de l'amitié</h3>
            <table>
                <tr>
                    <th>Utilisateur concerné</th>
                    <td><?= $friendship->hasValue('user') ? $this->Html->link($friendship->user->first_name . ' ' . $friendship->user->last_name, ['controller' => 'Users', 'action' => 'view', $friendship->user->id]) : '' ?></td>
                </tr>
                <tr>
                    <th>Ami</th>
                    <td><?= $friendship->hasValue('friends_user') ? $this->Html->link($friendship->friends_user->first_name . ' ' . $friendship->friends_user->last_name, ['controller' => 'Users', 'action' => 'view', $friendship->friends_user->id]) : '' ?></td>
                </tr>
                <tr>
                    <th>Statut</th>
                    <td><?= h($friendship->status) ?></td>
                </tr>
                <tr>
                    <th>Créé le</th>
                    <td><?= h($friendship->created) ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>
