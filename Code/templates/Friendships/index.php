<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\User> $users
 * @var array $friendsList
 * @var iterable<\App\Model\Entity\Friend> $requests
 * @var string $search
 */
$this->assign('title', 'Mes Amis');
$this->assign('mainClass', 'main-index');
?>

<div class="index_container">

    <h2>Mes Amis</h2>

        <?php if (!empty($message)): ?>
            <p class="message" style="text-align:center;color:var(--orange);font-weight:bold;">
                <?= h($message) ?>
            </p>
        <?php endif; ?>

    <div class="container">

        <div class="column">
            <h3>Rechercher un utilisateur</h3>

            <?= $this->Form->create(null, ['type' => 'get']) ?>
            <?= $this->Form->control('search', [
                'label' => false,
                'placeholder' => 'Nom ou prénom',
                'value' => $search ?? '',
            ]) ?>
            <?= $this->Form->button('Rechercher') ?>
            <?= $this->Form->end() ?>

            <?php if (!empty($users)): ?>
                <ul style="list-style:none;padding:0;">
                    <?php foreach ($users as $searchUser): ?>
                        <li class="ami-item">
                            <div class="ami-info">
                                <?php if (!empty($searchUser->profile_picture)): ?>
                                    <img src="/uploads/pp/<?= h($searchUser->profile_picture) ?>" class="ami-photo"
                                         alt="Avatar">
                                <?php else: ?>
                                    <div class="ami-placeholder">
                                        <?= strtoupper(substr($searchUser->first_name, 0, 1)) ?>
                                    </div>
                                <?php endif; ?>

                                <span><?= h($searchUser->first_name . ' ' . $searchUser->last_name) ?></span>
                            </div>

                            <?php if ($searchUser->friendship_status === null): ?>
                                <?= $this->Form->postLink(
                                    'Ajouter',
                                    ['action' => 'add', $searchUser->id],
                                    [
                                        'class' => 'button',
                                        'confirm' => 'Envoyer une demande d’ami ?'
                                    ]
                                ) ?>
                            <?php elseif ($searchUser->friendship_status === 'pending'): ?>
                                <span>Demande envoyée</span>
                            <?php elseif ($searchUser->friendship_status === 'accepted'): ?>
                                <span>Ami</span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php elseif (!empty($search)): ?>
                <p>Aucun utilisateur trouvé.</p>
            <?php endif; ?>
        </div>

        <div class="column">
            <h3>Mes amis</h3>

            <?php if (!empty($friendsList)): ?>
                <ul style="list-style:none;padding:0;">
                    <?php foreach ($friendsList as $friendItem):
                        $friendUser = $friendItem['friend'];
                        $friendshipId = $friendItem['friendship_id'];
                        ?>
                        <li class="ami-item">
                            <div class="ami-info">
                                <?php if (!empty($friendUser->profile_picture)): ?>
                                    <img src="/uploads/pp/<?= h($friendUser->profile_picture) ?>" class="ami-photo"
                                         alt="Avatar">
                                <?php else: ?>
                                    <div class="ami-placeholder">
                                        <?= strtoupper(substr($friendUser->first_name, 0, 1) . substr($friendUser->last_name, 0, 1)) ?>
                                    </div>
                                <?php endif; ?>

                                <span><?= h($friendUser->first_name . ' ' . $friendUser->last_name) ?></span>
                            </div>

                            <div class="ami-actions">
                                <?= $this->Html->link(
                                    '<i class="material-icons">chat</i> Message',
                                    ['controller' => 'Messages', 'action' => 'start', $friendUser->id],
                                    ['escape' => false, 'class' => 'btn-message']
                                ) ?>

                                <?= $this->Form->postLink(
                                    '<i class="material-icons">delete</i> Supprimer',
                                    ['action' => 'delete', $friendshipId],
                                    [
                                        'escape' => false,
                                        'class' => 'btn-supprimer',
                                        'confirm' => 'Voulez-vous vraiment supprimer cet ami ?'
                                    ]
                                ) ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>Vous n'avez pas encore d'amis.</p>
            <?php endif; ?>

            <h3 style="margin-top:30px;">Demandes d'amis reçues</h3>

            <?php if (!empty($requests)): ?>
                <ul style="list-style:none;padding:0;">
                    <?php foreach ($requests as $request): ?>
                        <?php $requestUser = $request->user; ?>
                        <li class="ami-item">
                            <div class="ami-info">
                                <?php if (!empty($requestUser->profile_picture)): ?>
                                    <img src="/uploads/pp/<?= h($requestUser->profile_picture) ?>" class="ami-photo"
                                         alt="Avatar">
                                <?php else: ?>
                                    <div class="ami-placeholder">
                                        <?= strtoupper(substr($requestUser->first_name, 0, 1)) ?>
                                    </div>
                                <?php endif; ?>

                                <span><?= h($requestUser->first_name . ' ' . $requestUser->last_name) ?></span>
                            </div>

                            <div class="ami-actions">
                                <?= $this->Html->link(
                                    'Accepter',
                                    ['action' => 'accept', $request->id],
                                    ['class' => 'button']
                                ) ?>
                                <?= $this->Html->link(
                                    'Refuser',
                                    ['action' => 'reject', $request->id],
                                    ['class' => 'button']
                                ) ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>Aucune demande en attente.</p>
            <?php endif; ?>
        </div>

    </div>
</div>
