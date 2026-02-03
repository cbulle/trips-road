<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Roadtrip> $roadtrips
 * @var string|null $share_url
 * @var string|null $show_share
 */

$this->assign('mainClass', '');
?>

<h1>Mes Road Trips</h1>

<?php if ($roadtrips->isEmpty()) : ?>
    <div class="container">
        <p>Aucun road trip pour le moment.</p>
        <a href="<?= $this->Url->build(['action' => 'add']) ?>" class="btn-save">En créer un</a>
    </div>
<?php else : ?>
    <div class="roadtrip-grid">
        <?php foreach ($roadtrips as $rt): ?>
            <div class="roadtrip-card">

                <?php
                $estTermine = ($rt->status === 'completed');
                $classeCss = $estTermine ? 'statut-termine' : 'statut-brouillon';
                $texteStatut = $estTermine ? 'Terminé' : 'Brouillon';

                $urlImage = '/img/imgBase.png';

                if (!empty($rt->photo_url)) {
                    $cheminPhysique = WWW_ROOT . 'uploads' . DS . 'roadtrips' . DS . $rt->photo_url;
                    if (file_exists($cheminPhysique)) {
                        $urlImage = '/uploads/roadtrips/' . $rt->photo_url;
                    }
                }
                ?>

                <?= $this->Html->image($urlImage, [
                    'alt' => 'Photo du road trip',
                    'class' => 'roadtrip-photo'
                ]) ?>

                <div style="padding: 10px 10px 0 10px;">
                    <span class="badge-statut <?= $classeCss ?>">
                        <?= $texteStatut ?>
                    </span>
                </div>

                <h3><?= h($rt->title) ?></h3>
                <p><?= h($this->Text->truncate($rt->description, 100)) ?></p>

                <div class="roadtrip-buttons">
                    <a class="btn-view" href="<?= $this->Url->build(['action' => 'view', $rt->id]) ?>">
                        <i class="material-icons">visibility</i>
                    </a>

                    <a class="btn-edit" href="<?= $this->Url->build(['action' => 'edit', $rt->id]) ?>">
                        <i class="material-icons">edit</i>
                    </a>

                    <a class="btn-share" href="<?= $this->Url->build(['action' => 'share', $rt->id]) ?>">
                        <i class="material-icons">share</i>
                    </a>

                    <?= $this->Form->postLink(
                        '<i class="material-icons">delete</i>',
                        ['action' => 'delete', $rt->id],
                        [
                            'escape' => false,
                            'class' => 'btn-delete',
                            'confirm' => 'Voulez-vous vraiment supprimer ce road trip ?'
                        ]
                    ) ?>
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
                <input type="text" class="share-url-input" id="shareUrl" value="<?= h($share_url) ?>" readonly>
                <button class="copy-btn" onclick="copyShareUrl()">Copier</button>
            </div>

            <div class="copy-success" id="copySuccess">Lien copié !</div>
        </div>
    </div>
<?php endif; ?>

<script>
    function closeShareModal() {
        document.getElementById('shareModal').style.display = 'none';
        window.history.replaceState({}, document.title, window.location.pathname);
    }

    function copyShareUrl() {
        var copyText = document.getElementById("shareUrl");
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(copyText.value);

        var successMsg = document.getElementById("copySuccess");
        successMsg.style.display = "block";
        setTimeout(function() { successMsg.style.display = "none"; }, 2000);
    }
</script>
