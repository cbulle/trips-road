<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Roadtrip> $roadtrips
 * @var array $favorisIds
 * @var string|null $userId
 */

$this->assign('mainClass', '');
?>

    <h1>Road Trips Publics</h1>

<?= $this->Flash->render() ?>

<?php if ($roadtrips->isEmpty()) : ?>
    <p style="text-align: center; margin-top: 20px;">Aucun road trip public pour le moment.</p>
<?php else : ?>
    <div class="roadtrip-grid">
        <?php foreach ($roadtrips as $rt): ?>
            <div class="roadtrip-card">

                <?php
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

                <h3><?= h($rt->title) ?></h3>

                <?php
                $isTermine = ($rt->status === 'completed');
                $classeStatus = $isTermine ? 'status-termine' : 'status-brouillon';
                $labelStatus = $isTermine ? '✅ Terminé' : '🚧 En cours';
                ?>

                <span class="status-badge <?= $classeStatus ?>">
                    <?= $labelStatus ?>
                </span>

                <p><?= h($this->Text->truncate($rt->description, 100)) ?></p>

                <p class="creator-info">
                    Proposé par :
                    <strong>
                        <?= h($rt->user->username ?? 'Utilisateur inconnu') ?>
                    </strong>
                </p>

                <div class="roadtrip-buttons">
                    <a class="btn-view" href="<?= $this->Url->build(['controller' => 'Roadtrips', 'action' => 'viewPublic', $rt->id]) ?>">
                        <i class="material-icons">visibility</i>
                    </a>

                    <?php if ($userId): ?>
                        <?php
                        $isFavori = in_array($rt->id, $favorisIds);
                        $classFavori = $isFavori ? 'active' : '';
                        ?>

                        <a class="btn-favori <?= $classFavori ?>"
                           href="<?= $this->Url->build(['controller' => 'Favorites', 'action' => 'toggle', $rt->id]) ?>">
                            <i class="material-icons">favorite</i>
                        </a>
                    <?php endif; ?>
                </div>

            </div>
        <?php endforeach; ?>
    </div>

    <div class="paginator">
        <ul class="pagination">
            <?= $this->Paginator->first('<< ' . __('Premier')) ?>
            <?= $this->Paginator->prev('< ' . __('Précédent')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('Suivant') . ' >') ?>
            <?= $this->Paginator->last(__('Dernier') . ' >>') ?>
        </ul>
    </div>
<?php endif; ?>
