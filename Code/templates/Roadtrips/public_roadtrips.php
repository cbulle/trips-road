<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Roadtrip> $roadtrips
 * @var array $favorisIds
 * @var string|null $userId
 * @var \App\Model\Entity\User $user
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
                    <li><a href="<?= $this->Url->build(['controller' => 'Roadtrips', 'action' => 'myRoadtrips']) ?>">Mes
                            Road-Trips</a></li>
                <?php endif; ?>

                <li><a href="<?= $this->Url->build(['controller' => 'Roadtrips', 'action' => 'publicRoadtrips']) ?>"
                       class="active">Road-Trips Publics</a></li>

                <?php if (isset($userId)): ?>
                    <li><a href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'profile']) ?>">Paramètres
                            du compte</a></li>
                    <li><a href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'accessibility']) ?>">Accessibilité</a>
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

    <?= $this->Flash->render() ?>

    <?php if ($roadtrips->isEmpty()) : ?>
        <p style="text-align: center; margin-top: 50px; font-size: 1.2rem; color: #666;">Aucun road trip public pour le
            moment.</p>
    <?php else : ?>
        <div class="roadtrip-grid">
            <?php foreach ($roadtrips as $rt): ?>
                <div class="roadtrip-card">

                    <div class="card-badges">
                        <?php
                        $isTermine = ($rt->status === 'completed');
                        $classeStatus = $isTermine ? 'statut-termine' : 'statut-brouillon';
                        $labelStatus = $isTermine ? 'Terminé' : 'En cours';
                        ?>
                        <span class="badge-statut <?= $classeStatus ?>">
                                <?= $labelStatus ?>
                            </span>
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

                    <?= $this->Html->image($urlImage, ['alt' => 'Photo du road trip', 'class' => 'roadtrip-photo']) ?>

                    <div class="card-body">
                        <h3><?= h($rt->title) ?></h3>
                        <p><?= h($this->Text->truncate($rt->description, 80)) ?></p>

                        <div class="creator-info">
                            Proposé par : <strong><?= h($rt->user->username ?? 'Utilisateur inconnu') ?></strong>
                        </div>
                    </div>

                    <div class="roadtrip-actions">
                        <a class="action-btn view" title="Consulter"
                           href="<?= $this->Url->build(['controller' => 'Roadtrips', 'action' => 'view', $rt->id]) ?>">
                            <i class="material-icons">visibility</i>
                        </a>

                        <?php if ($userId): ?>
                            <?php
                            $isFavori = in_array($rt->id, $favorisIds);
                            $styleFav = $isFavori ? 'color: var(--rouge);' : '';
                            ?>
                            <a class="action-btn" style="<?= $styleFav ?>" title="Mettre en favori"
                               href="<?= $this->Url->build(['controller' => 'Favorites', 'action' => 'toggle', $rt->id]) ?>">
                                <i class="material-icons"><?= $isFavori ? 'favorite' : 'favorite_border' ?></i>
                            </a>
                        <?php endif; ?>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
