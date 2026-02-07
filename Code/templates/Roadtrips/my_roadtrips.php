<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Roadtrip> $roadtrips
 * @var string|null $share_url
 * @var string|null $show_share
 * @var \App\Model\Entity\User $user
 */

$this->assign('mainClass', '');
?>

<div class="dashboard-container">

    <aside class="profil-sidebar">
        <div class="user-brief">
            <?php
            $pp = (isset($user) && $user->profile_picture) ? $user->profile_picture : 'User.png';
            $username = (isset($user) && $user->username) ? $user->username : 'Mon Compte';
            ?>
            <div class="avatar-circle small"
                 style="background-image: url('<?= $this->Url->webroot('uploads/pp/' . $pp) ?>');"></div>
            <h3><?= h($username) ?></h3>
        </div>

        <h1 class="sidebar-title">Mes Road Trips</h1>

        <a href="<?= $this->Url->build(['controller' => 'Roadtrips', 'action' => 'add']) ?>" class="sidebar-create-btn">
            <i class="material-icons">add_circle</i> Créer un Road Trip
        </a>

        <nav class="profil-nav">
            <ul>
                <li><a href="<?= $this->Url->build(['controller' => 'Roadtrips', 'action' => 'myRoadtrips']) ?>"
                       class="active">Mes Road-Trips</a></li>
                <li><a href="<?= $this->Url->build(['controller' => 'Roadtrips', 'action' => 'publicRoadtrips']) ?>">Road-Trips
                        Publics</a></li>
                <li><a href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'profile']) ?>">Paramètres du
                        compte</a></li>
                <li><a href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'accessibility']) ?>">Accessibilité</a>
                </li>
                <li><a href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'logout']) ?>" class="logout">Déconnexion</a>
                </li>
            </ul>
        </nav>
    </aside>

    <?= $this->Flash->render() ?>

    <?php if ($roadtrips->isEmpty()) : ?>
        <div
            style="text-align: center; padding: 50px; background: white; border-radius: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
            <p style="font-size: 1.2rem; margin-bottom: 20px; color: #666;">Vous n'avez pas encore créé de road
                trip.</p>
            <p>Utilisez le bouton dans le menu de gauche pour commencer !</p>
        </div>
    <?php else : ?>
        <div class="roadtrip-grid">
            <?php foreach ($roadtrips as $rt): ?>
                <div class="roadtrip-card">

                    <div class="card-badges">
                        <?php
                        $estTermine = ($rt->status === 'completed');
                        $classeCss = $estTermine ? 'statut-termine' : 'statut-brouillon';
                        $texteStatut = $estTermine ? 'Terminé' : 'Brouillon';
                        ?>
                        <span class="badge-statut <?= $classeCss ?>"><?= $texteStatut ?></span>
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
                        <p><?= h($this->Text->truncate($rt->description, 80, ['ellipsis' => '...'])) ?></p>
                    </div>

                    <div class="roadtrip-actions">
                        <a class="action-btn view" title="Voir"
                           href="<?= $this->Url->build(['controller' => 'Roadtrips', 'action' => 'view', $rt->id]) ?>">
                            <i class="material-icons">visibility</i>
                        </a>

                        <a class="action-btn edit" title="Modifier"
                           href="<?= $this->Url->build(['action' => 'edit', $rt->id]) ?>">
                            <i class="material-icons">edit</i>
                        </a>

                        <a class="action-btn share" title="Partager"
                           href="<?= $this->Url->build(['action' => 'share', $rt->id]) ?>">
                            <i class="material-icons">share</i>
                        </a>

                        <?= $this->Form->postLink(
                            '<i class="material-icons">delete</i>',
                            ['action' => 'delete', $rt->id],
                            [
                                'escape' => false,
                                'class' => 'action-btn delete',
                                'title' => 'Supprimer',
                                'confirm' => 'Voulez-vous vraiment supprimer ce road trip ?'
                            ]
                        ) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php if ($show_share && $share_url): ?>
    <div class="share-modal active" id="shareModal">
        <div class="share-modal-content">
            <span class="share-modal-close" onclick="closeShareModal()">&times;</span>
            <h2>Partager votre road trip</h2>
            <p>Copiez ce lien pour partager votre road trip :</p>
            <div class="share-url-container">
                <input type="text" class="share-url-input" id="shareUrl" value="<?= h($share_url) ?>" readonly>
                <button class="copy-btn" onclick="copyShareUrl()">Copier</button>
            </div>
            <div class="copy-success" id="copySuccess">Lien copié !</div>
        </div>
    </div>
<?php endif; ?>
