<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Roadtrip> $roadtrips
 * @var array $favorisIds
 * @var string|null $userId
 * @var \App\Model\Entity\User $user
 * @var \App\Model\Entity\Comment $newComment
 */

$this->assign('mainClass', 'dashboard-page');
?>

<div class="dashboard-container">

    <aside class="profil-sidebar">
        <div class="user-brief">
            <?php
            if (isset($user)) {
                $pp = $user->profile_picture ?: 'User.png';
                $nomUser = h($user->username);
                $bgStyle = "background-image: url('" . $this->Url->webroot('uploads/pp/' . $pp) . "');";
            } else {
                $nomUser = "Visiteur";
                $bgStyle = "background-color: #ccc;";
            }
            ?>
            <div class="avatar-circle small" style="<?= $bgStyle ?>"></div>
            <h3><?= $nomUser ?></h3>
        </div>

        <h1 class="sidebar-title">Road Trips Publics</h1>

        <?php if (isset($userId)): ?>
            <a href="<?= $this->Url->build(['controller' => 'Roadtrips', 'action' => 'add']) ?>" class="sidebar-create-btn">
                <i class="material-icons">add_circle</i> Créer un Road Trip
            </a>
        <?php endif; ?>

        <nav class="profil-nav">
            <ul>
                <?php if (isset($userId)): ?>
                    <li><a href="<?= $this->Url->build(['controller' => 'Roadtrips', 'action' => 'myRoadtrips']) ?>">Mes Road-Trips</a></li>
                <?php endif; ?>

                <li><a href="<?= $this->Url->build(['controller' => 'Roadtrips', 'action' => 'publicRoadtrips']) ?>" class="active">Road-Trips Publics</a></li>

                <?php if (isset($userId)): ?>
                    <li><a href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'profile']) ?>">Paramètres</a></li>
                    <li><a href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'logout']) ?>" class="logout">Déconnexion</a></li>
                <?php else: ?>
                    <li><a href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'login']) ?>">Se connecter</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </aside>

    <div class="main-content">
        <?= $this->Flash->render() ?>

        <?php if ($roadtrips->isEmpty()) : ?>
            <p style="text-align: center; margin-top: 50px; font-size: 1.2rem; color: #666;">Aucun road trip public pour le moment.</p>
        <?php else : ?>
            <div class="roadtrip-grid">
                <?php foreach ($roadtrips as $rt): ?>
                    <div class="roadtrip-card">
                        <div class="card-badges">
                            <?php
                            $isTermine = ($rt->status === 'completed' || $rt->status === 'termine');
                            $classeStatus = $isTermine ? 'statut-termine' : 'statut-brouillon';
                            $labelStatus = $isTermine ? 'Terminé' : 'En cours';
                            ?>
                            <span class="badge-statut <?= $classeStatus ?>"><?= $labelStatus ?></span>
                        </div>

                        <?php
                        $urlImage = '/img/imgBase.png';
                        if (!empty($rt->photo_url)) {
                            $cheminPhysique = WWW_ROOT . 'uploads' . DS . 'roadtrips' . DS . $rt->photo_url;
                            if (file_exists($cheminPhysique)) {
                                $urlImage = '/uploads/roadtrips/' . $rt->photo_url;
                            }
                        }
                        ?>
                        <?= $this->Html->image($urlImage, ['alt' => 'Photo', 'class' => 'roadtrip-photo']) ?>

                        <div class="card-body">
                            <h3><?= h($rt->title) ?></h3>
                            <p><?= h($this->Text->truncate($rt->description, 80)) ?></p>
                            <div class="creator-info">
                                Proposé par : <strong><?= h($rt->user->username ?? 'Anonyme') ?></strong>
                            </div>
                        </div>

                        <div class="roadtrip-actions">
                            <a class="action-btn view" href="<?= $this->Url->build(['action' => 'view', $rt->id]) ?>">
                                <i class="material-icons">visibility</i>
                            </a>

                            <button type="button" class="action-btn btn-open-avis" data-id="<?= $rt->id ?>">
                                <i class="material-icons">rate_review</i>
                            </button>

                            <?php if ($this->request->getAttribute('identity')): ?>
                                <button type="button" class="action-btn btn-open-comment" data-id="<?= $rt->id ?>">
                                    <i class="material-icons">add_comment</i>
                                </button>
                            <?php endif; ?>
                        </div>

                        <div id="modalAvis-<?= $rt->id ?>" class="custom-modal" style="display:none;">
                            <div class="modal-content">
                                <span class="close" onclick="closeModal('modalAvis-<?= $rt->id ?>')">&times;</span>
                                <h3>Avis sur <?= h($rt->title) ?></h3>
                                <div class="comments-list">
                                    <?php if (empty($rt->comments)): ?>
                                        <p>Aucun avis pour le moment.</p>
                                    <?php else: ?>
                                        <?php foreach ($rt->comments as $comment): ?>
                                            <div class="comment-item">
                                                <strong><?= h($comment->user->username) ?></strong> : <?= h($comment->body) ?>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="modalAvis-<?= $rt->id ?>" class="custom-modal" style="display:none;">
                        <div class="modal-content">
                            <span class="close" onclick="closeModal('modalAvis-<?= $rt->id ?>')">&times;</span>
                            <h3>Avis sur <?= h($rt->title) ?></h3>
                            <div class="comments-list">
                                <?php if (empty($rt->comments)): ?>
                                    <p>Aucun avis pour le moment.</p>
                                <?php else: ?>
                                    <?php foreach ($rt->comments as $comment): ?>
                                        <div class="comment-item">
                                            <strong><?= h($comment->user->username) ?></strong> : <?= h($comment->body) ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <?php if ($this->request->getAttribute('identity')): ?>
                        <div id="modalComment-<?= $rt->id ?>" class="custom-modal" style="display:none;">
                            <div class="modal-content">
                                <span class="close" onclick="closeModal('modalComment-<?= $rt->id ?>')">&times;</span>
                                <h3>Laisser un avis</h3>
                                <?= $this->Form->create($newComment, ['url' => ['controller' => 'Comments', 'action' => 'add']]) ?>
                                <?= $this->Form->hidden('roadtrip_id', ['value' => $rt->id]) ?>
                                <?= $this->Form->control('rating', ['type' => 'select', 'options' => [5=>'5 ⭐', 4=>'4 ⭐', 3=>'3 ⭐', 2=>'2 ⭐', 1=>'1 ⭐']]) ?>
                                <?= $this->Form->control('body', ['label' => 'Commentaire', 'type' => 'textarea', 'rows' => 3]) ?>
                                <button type="submit" class="btn-submit">Envoyer</button>
                                <?= $this->Form->end() ?>
                            </div>
                        </div>
                    <?php endif; ?>

                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
