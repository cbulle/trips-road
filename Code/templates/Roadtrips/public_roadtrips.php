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
            <p class="empty-state">Aucun road trip public pour le moment.</p>
        <?php else : ?>
            <div class="roadtrip-grid">
                <?php foreach ($roadtrips as $rt): ?>

                    <?php
                    $isTermine    = ($rt->status === 'completed' || $rt->status === 'termine');
                    $classeStatus = $isTermine ? 'statut-termine' : 'statut-brouillon';
                    $labelStatus  = $isTermine ? 'Terminé' : 'En cours';

                    $urlImage = '/img/imgBase.png';
                    if (!empty($rt->photo_url)) {
                        $cheminPhysique = WWW_ROOT . 'uploads' . DS . 'roadtrips' . DS . $rt->photo_url;
                        if (file_exists($cheminPhysique)) {
                            $urlImage = '/uploads/roadtrips/' . $rt->photo_url;
                        }
                    }

                    $nbAvis = !empty($rt->comments) ? count($rt->comments) : 0;
                    ?>

                    <div class="roadtrip-card">

                        <div class="card-badges">
                            <span class="badge-statut <?= $classeStatus ?>"><?= $labelStatus ?></span>
                        </div>

                        <?= $this->Html->image($urlImage, ['alt' => 'Photo du roadtrip', 'class' => 'roadtrip-photo']) ?>

                        <div class="card-body">
                            <h3><?= h($rt->title) ?></h3>
                            <p class="card-description"><?= h($this->Text->truncate($rt->description, 100)) ?></p>
                            <div class="creator-info">
                                Proposé par : <strong><?= h($rt->user->username ?? 'Anonyme') ?></strong>
                            </div>
                        </div>

                        <div class="roadtrip-actions">

                            <a class="action-btn view"
                               href="<?= $this->Url->build(['action' => 'view', $rt->id]) ?>"
                               title="Voir le road trip">
                                <i class="material-icons">visibility</i>
                            </a>

                            <button type="button"
                                    class="action-btn btn-open-avis"
                                    data-id="<?= $rt->id ?>"
                                    onclick="openRoadtripModal('modalAvis-<?= $rt->id ?>')"
                                    title="Voir les avis (<?= $nbAvis ?>)">
                                <i class="material-icons">rate_review</i>
                                <?php if ($nbAvis > 0): ?>
                                    <span class="avis-count"><?= $nbAvis ?></span>
                                <?php endif; ?>
                            </button>

                            <?php if ($this->request->getAttribute('identity')): ?>
                                <button type="button"
                                        class="action-btn btn-open-comment"
                                        data-id="<?= $rt->id ?>"
                                        onclick="openRoadtripModal('modalComment-<?= $rt->id ?>')"
                                        title="Laisser un avis">
                                    <i class="material-icons">add_comment</i>
                                    <span class="btn-label">À commenter</span>
                                </button>
                            <?php endif; ?>

                        </div>
                    </div>

                <?php endforeach; ?>
            </div>

            <?php foreach ($roadtrips as $rt): ?>

                <?php $nbAvis = !empty($rt->comments) ? count($rt->comments) : 0; ?>


                <div id="modalAvis-<?= $rt->id ?>"
                     class="custom-modal"
                     onclick="if(event.target===this) closeRoadtripModal('modalAvis-<?= $rt->id ?>')">

                    <div class="modal-content modal-avis">
                        <div class="modal-header">
                            <h3>
                                <i class="material-icons">rate_review</i>
                                Avis — <?= h($rt->title) ?>
                            </h3>
                            <button class="modal-close"
                                    onclick="closeRoadtripModal('modalAvis-<?= $rt->id ?>')"
                                    aria-label="Fermer">&times;</button>
                        </div>

                        <div class="modal-body">
                            <?php if (empty($rt->comments)): ?>
                                <div class="no-comments">
                                    <i class="material-icons">chat_bubble_outline</i>
                                    <p>Aucun avis pour le moment.</p>
                                    <?php if ($this->request->getAttribute('identity')): ?>
                                        <button type="button"
                                                class="btn-switch-to-comment"
                                                onclick="closeRoadtripModal('modalAvis-<?= $rt->id ?>'); openRoadtripModal('modalComment-<?= $rt->id ?>')">
                                            Soyez le premier à laisser un avis !
                                        </button>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="comments-list">
                                    <?php foreach ($rt->comments as $comment): ?>
                                        <div class="comment-item">
                                            <div class="comment-meta">
                                                <span class="comment-author">
                                                    <i class="material-icons">account_circle</i>
                                                    <?= h($comment->user->username ?? 'Anonyme') ?>
                                                </span>
                                                <?php if (!empty($comment->rating)): ?>
                                                    <span class="comment-rating">
                                                        <?= str_repeat('⭐', (int)$comment->rating) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            <p class="comment-body"><?= h($comment->body) ?></p>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php if ($this->request->getAttribute('identity')): ?>
                                    <div class="modal-footer-action">
                                        <button type="button"
                                                class="btn-switch-to-comment"
                                                onclick="closeRoadtripModal('modalAvis-<?= $rt->id ?>'); openRoadtripModal('modalComment-<?= $rt->id ?>')">
                                            <i class="material-icons">add_comment</i> Ajouter mon avis
                                        </button>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <?php /* ---- MODALE COMMENTER (connectés seulement) ---- */ ?>
                <?php if ($this->request->getAttribute('identity')): ?>
                    <div id="modalComment-<?= $rt->id ?>"
                         class="custom-modal"
                         onclick="if(event.target===this) closeRoadtripModal('modalComment-<?= $rt->id ?>')">

                        <div class="modal-content modal-comment-form">
                            <div class="modal-header">
                                <h3>
                                    <i class="material-icons">add_comment</i>
                                    Laisser un avis
                                </h3>
                                <button class="modal-close"
                                        onclick="closeRoadtripModal('modalComment-<?= $rt->id ?>')"
                                        aria-label="Fermer">&times;</button>
                            </div>

                            <div class="modal-body">
                                <p class="modal-trip-title">
                                    Road trip : <strong><?= h($rt->title) ?></strong>
                                </p>

                                <?= $this->Form->create($newComment, [
                                    'url' => ['controller' => 'Comments', 'action' => 'add']
                                ]) ?>

                                <?= $this->Form->hidden('roadtrip_id', ['value' => $rt->id]) ?>

                                <div class="form-group">
                                    <label class="form-label">Note</label>
                                    <?= $this->Form->select('rating', [
                                        5 => '5 ⭐ — Excellent',
                                        4 => '4 ⭐ — Très bien',
                                        3 => '3 ⭐ — Bien',
                                        2 => '2 ⭐ — Passable',
                                        1 => '1 ⭐ — Décevant',
                                    ], [
                                        'class' => 'form-select',
                                        'empty' => '-- Choisir une note --',
                                    ]) ?>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Commentaire</label>
                                    <?= $this->Form->textarea('body', [
                                        'rows'        => 4,
                                        'class'       => 'form-textarea',
                                        'placeholder' => 'Partagez votre expérience...',
                                    ]) ?>
                                </div>

                                <div class="form-actions">
                                    <button type="button"
                                            class="btn-cancel-modal"
                                            onclick="closeRoadtripModal('modalComment-<?= $rt->id ?>')">
                                        Annuler
                                    </button>
                                    <button type="submit" class="btn-submit-comment">
                                        <i class="material-icons">send</i> Publier
                                    </button>
                                </div>

                                <?= $this->Form->end() ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

            <?php endforeach; ?>

        <?php endif; ?>
    </div>
</div>
