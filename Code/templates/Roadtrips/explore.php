<?php
$this->assign('mainClass', '');
?>

<h1>Mes Road Trips</h1>

<?php if (empty($roadtrips)) : ?>
    <p>Aucun road trip pour le moment.</p>
<?php else : ?>
    <div class="roadtrip-grid">
        <?php foreach ($roadtrips as $rt): ?>
            <div class="roadtrip-card">

                <?php
                $estTermine = (isset($rt['statut']) && $rt['statut'] === 'termine');
                $classeCss = $estTermine ? 'statut-termine' : 'statut-brouillon';
                $texteStatut = $estTermine ? 'Terminé' : 'Brouillon';
                ?>

                <?php if (!empty($rt['photo'])): ?>
                    <img src="/uploads/roadtrips/<?= htmlspecialchars($rt['photo']) ?>"
                         alt="Photo du road trip" class="roadtrip-photo">
                <?php else : ?>
                    <img src="/img/imgBase.png" alt="Photo du road trip" class="roadtrip-photo">
                <?php endif; ?>


                <div style="padding: 10px 10px 0 10px;">
                <span class="badge-statut <?= $classeCss ?>">
                    <?= $texteStatut ?>
                </span>
                </div>

                <h3><?= htmlspecialchars($rt['titre']) ?></h3>
                <p><?= htmlspecialchars($rt['description']) ?></p>

                <div class="roadtrip-buttons">
                    <a class="btn-view" href="/vuRoadTrip?id=<?= $rt['id'] ?>">
                        <i class="material-icons">visibility</i>
                    </a>

                    <a class="btn-edit" href="/creationRoadTrip?id=<?= $rt['id'] ?>">
                        <i class="material-icons">edit</i>
                    </a>

                    <a class="btn-share" href="/generate_shared_link?id=<?= $rt['id'] ?>">
                        <i class="material-icons">share</i>
                    </a>

                    <a class="btn-delete" href="/delete_RoadTrip?id=<?= $rt['id'] ?>"
                       onclick="return confirm('Voulez-vous vraiment supprimer ce road trip ?');">
                        <i class="material-icons">delete</i>
                    </a>
                </div>

            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if ($show_share && $share_url): ?>
    <div class="share-modal active" id="shareModal">
        <div class="share-modal-content">
            <span class="share-modal-close" onclick="closeShareModal()">&times;</span>
            <h2>Partager votre road trip</h2>
            <p>Copiez ce lien pour partager votre road trip :</p>

            <div class="share-url-container">
                <input type="text" class="share-url-input" id="shareUrl" value="<?= htmlspecialchars($share_url) ?>" readonly>
                <button class="copy-btn" onclick="copyShareUrl()">Copier</button>
            </div>

            <div class="copy-success" id="copySuccess">Lien copié !</div>

        </div>
    </div>
    <?php
    unset($_SESSION['share_url']);
endif;
?>
