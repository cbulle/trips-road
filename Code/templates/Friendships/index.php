<main class="main-index">
    <div class="index_container">
        <h2>Mes Amis</h2>

        <?php if (!empty($message)): ?>
            <p class="message" style="text-align:center;color:var(--orange);font-weight:bold;">
                <?= h($message) ?>
            </p>
        <?php endif; ?>

        <div class="container">

            <!-- 🔍 Recherche utilisateurs -->
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
                        <?php foreach ($users as $u): ?>
                            <li class="ami-item">
                                <div class="ami-info">

                                    <?php if (!empty($u->profile_picture)): ?>
                                        <img src="/uploads/pp/<?= h($u->profile_picture) ?>"
                                             class="ami-photo">
                                    <?php else: ?>
                                        <div class="ami-placeholder">
                                            <?= strtoupper(substr($u->first_name, 0, 1)) ?>
                                        </div>
                                    <?php endif; ?>

                                    <span><?= h($u->last_name . ' ' . $u->first_name) ?></span>
                                </div>

                                <?php if ($u->friendship_status === null): ?>
                                    <?= $this->Html->link(
                                        'Ajouter',
                                        ['action' => 'add', $u->id],
                                        ['class' => 'button']
                                    ) ?>

                                <?php elseif ($u->friendship_status === 'pending'): ?>
                                    <span>Demande envoyée</span>

                                <?php elseif ($u->friendship_status === 'accepted'): ?>
                                    <span>Ami</span>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                <?php elseif (!empty($search)): ?>
                    <p>Aucun utilisateur trouvé.</p>
                <?php endif; ?>
            </div>

            <!-- 👥 Amis -->
            <div class="column">
                <h3>Mes amis</h3>

                <?php if (!empty($friends)): ?>
                    <ul style="list-style:none;padding:0;">
                        <?php foreach ($friends as $friend): ?>
                            <?php $u = $friend->friend; ?>

                            <li class="ami-item">
                                <div class="ami-info">
                                    <?php if (!empty($u->profile_picture)): ?>
                                        <img src="/uploads/pp/<?= h($u->profile_picture) ?>"
                                             class="ami-photo">
                                    <?php else: ?>
                                        <div class="ami-placeholder">
                                            <?= strtoupper($u->first_name[0] . $u->last_name[0]) ?>
                                        </div>
                                    <?php endif; ?>

                                    <span><?= h($u->last_name . ' ' . $u->first_name) ?></span>
                                </div>

                                <div class="ami-actions">
                                    <?= $this->Html->link(
                                        '<i class="material-icons">chat</i> Message',
                                        ['controller' => 'Messages', 'action' => 'start', $u->id],
                                        ['escape' => false, 'class' => 'btn-message']
                                    ) ?>

                                    <?= $this->Form->postLink(
                                        '<i class="material-icons">delete</i> Supprimer',
                                        ['action' => 'delete', $friend->id],
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

                <!-- 📩 Demandes reçues -->
                <h3 style="margin-top:30px;">Demandes d'amis reçues</h3>

                <?php if (!empty($requests)): ?>
                    <ul style="list-style:none;padding:0;">
                        <?php foreach ($requests as $request): ?>
                            <?php $u = $request->user; ?>

                            <li class="ami-item">
                                <div class="ami-info">
                                    <?php if (!empty($u->profile_picture)): ?>
                                        <img src="/uploads/pp/<?= h($u->profile_picture) ?>"
                                             class="ami-photo">
                                    <?php else: ?>
                                        <div class="ami-placeholder">
                                            <?= strtoupper($u->first_name[0]) ?>
                                        </div>
                                    <?php endif; ?>

                                    <span><?= h($u->last_name . ' ' . $u->first_name) ?></span>
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
</main>
