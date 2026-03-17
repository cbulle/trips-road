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
$currentUser = $this->request->getAttribute('identity');
$isAdmin = $currentUser && isset($currentUser->role) && $currentUser->role === 'admin';
?>

<div class="dashboard-container <?= $isAdmin ? 'admin-mode' : '' ?>">

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
            <h3>
                <?= $nomUser ?>
                <?php if ($isAdmin): ?>
                    <br><span class="admin-badge">Admin</span>
                <?php endif; ?>
            </h3>
        </div>

        <h1 class="sidebar-title">Road Trips Publics</h1>

        <?php if (isset($userId)): ?>
            <a href="<?= $this->Url->build(['controller' => 'Roadtrips', 'action' => 'add']) ?>"
               class="sidebar-create-btn">
                <i class="material-icons">add_circle</i> Créer un Road Trip
            </a>
        <?php endif; ?>

        <nav class="profil-nav">
            <ul>
                <?php if (isset($userId)): ?>
                    <li><a href="<?= $this->Url->build(['controller' => 'Roadtrips', 'action' => 'myRoadtrips']) ?>">Mes
                            Road-Trips</a></li>
                <?php endif; ?>
                <li><a href="<?= $this->Url->build(['controller' => 'Roadtrips', 'action' => 'publicRoadtrips']) ?>"
                       class="active">Road-Trips Publics</a></li>
                <?php if (isset($userId)): ?>
                    <li>
                        <a href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'profile']) ?>">Paramètres</a>
                    </li>
                    <li><a href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'logout']) ?>"
                           class="logout">Déconnexion</a></li>
                <?php else: ?>
                    <li><a href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'login']) ?>">Se
                            connecter</a></li>
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
                    $isTermine = ($rt->status === 'completed' || $rt->status === 'termine');
                    $classeStatus = $isTermine ? 'statut-termine' : 'statut-brouillon';
                    $labelStatus = $isTermine ? 'Terminé' : 'En cours';

                    $urlImage = '/img/imgBase.png';
                    if (!empty($rt->photo_url)) {
                        $cheminPhysique = WWW_ROOT . 'uploads' . DS . 'roadtrips' . DS . $rt->photo_url;
                        if (file_exists($cheminPhysique)) {
                            $urlImage = '/uploads/roadtrips/' . $rt->photo_url;
                        }
                    }

                    $nbAvis = !empty($rt->comments) ? count($rt->comments) : 0;
                    $isOwner = $currentUser && $currentUser->getIdentifier() === $rt->user_id;
                    ?>

                    <div class="roadtrip-card <?= $isAdmin ? 'admin-card' : '' ?>">

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
                            <a class="action-btn view" href="<?= $this->Url->build(['action' => 'view', $rt->id]) ?>"
                               title="Voir">
                                <i class="material-icons">visibility</i>
                            </a>

                            <?php if ($currentUser): ?>
                                <?php if (in_array($rt->id, $favorisIds ?? [])): ?>
                                    <?= $this->Form->postLink('<i class="material-icons">favorite</i>',
                                        ['controller' => 'Favorites', 'action' => 'delete', $rt->id], // Adaptez l'action si besoin
                                        ['escape' => false, 'title' => 'Retirer des favoris']) ?>
                                <?php else: ?>
                                    <?= $this->Form->postLink('<i class="material-icons">favorite_border</i>',
                                        ['controller' => 'Favorites', 'action' => 'add', '?' => ['roadtrip_id' => $rt->id]],
                                        ['escape' => false, 'title' => 'Ajouter aux favoris']) ?>
                                <?php endif; ?>
                            <?php endif; ?>

                            <button type="button" class="action-btn"
                                    onclick="openRoadtripModal('modalAvis-<?= $rt->id ?>')" title="Voir les avis">
                                <i class="material-icons">rate_review</i>
                            </button>

                            <?php if ($currentUser): ?>
                                <button type="button" class="action-btn"
                                        onclick="openRoadtripModal('modalComment-<?= $rt->id ?>')"
                                        title="Laisser un avis">
                                    <i class="material-icons">add_comment</i>
                                </button>
                            <?php endif; ?>

                            <button type="button" class="action-btn"
                                    onclick="telechargerPackExport(
                                        '<?= $this->Url->build(['controller' => 'Roadtrips', 'action' => 'exportGpx', $rt->id]) ?>',
                                        '<?= $this->Url->build(['controller' => 'Roadtrips', 'action' => 'exportPdf', $rt->id]) ?>'
                                        )"
                                    title="Exporter en GPX et PDF">
                                <i class="material-icons">file_download</i>
                            </button>

                            <?php if ($isOwner || $isAdmin): ?>
                                <?= $this->Form->postLink('<i class="material-icons">delete</i>',
                                    ['controller' => 'Roadtrips', 'action' => 'delete', $rt->id],
                                    ['confirm' => 'Supprimer ?', 'escape' => false, 'class' => 'action-btn btn-delete', 'title' => 'Supprimer']) ?>
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
                                    aria-label="Fermer">&times;
                            </button>
                        </div>

                        <div class="modal-body">
                            <?php if (empty($rt->comments)): ?>
                                <div class="no-comments">
                                    <i class="material-icons">chat_bubble_outline</i>
                                    <p>Aucun avis pour le moment.</p>
                                    <?php if ($currentUser): ?>
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
                                        <?php $isCommentOwner = $currentUser && $currentUser->getIdentifier() === $comment->user_id; ?>
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

                                                <?php if ($isCommentOwner || $isAdmin): ?>
                                                    <?= $this->Form->postLink(
                                                        '<i class="material-icons">delete</i>',
                                                        ['controller' => 'Comments', 'action' => 'delete', $comment->id],
                                                        [
                                                            'confirm' => 'Supprimer cet avis ?',
                                                            'escape' => false,
                                                            'class' => 'comment-delete-btn',
                                                            'title' => 'Supprimer l\'avis'
                                                        ]
                                                    ) ?>
                                                <?php endif; ?>
                                            </div>
                                            <p class="comment-body"><?= h($comment->body) ?></p>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php if ($currentUser): ?>
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

                <?php if ($currentUser): ?>
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
                                        aria-label="Fermer">&times;
                                </button>
                            </div>

                            <div class="modal-body">
                                <p class="modal-trip-title">
                                    Road trip : <strong><?= h($rt->title) ?></strong>
                                </p>

                                <?= $this->Form->create($newComment, [
                                    'url' => ['controller' => 'Comments', 'action' => 'add']
                                ]) ?>

                                <?= $this->Form->hidden('roadtrip_id', ['value' => $rt->id]) ?>

                                <div class="form-group rating-group">
                                    <label class="form-label">Votre note</label>
                                    <div class="modern-star-rating">
                                        <?php for ($i = 5; $i >= 1; $i--): ?>
                                            <input type="radio" id="star<?= $i ?>-<?= $rt->id ?>" name="rating"
                                                   value="<?= $i ?>" required/>
                                            <label for="star<?= $i ?>-<?= $rt->id ?>"
                                                   title="<?= $i ?> étoiles">★</label>
                                        <?php endfor; ?>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Commentaire</label>
                                    <?= $this->Form->textarea('body', [
                                        'rows' => 4,
                                        'class' => 'form-textarea',
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
