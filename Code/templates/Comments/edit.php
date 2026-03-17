<?php
$this->assign('mainClass', 'comment-edit-page');
/**
 * Page contenant le formulaire de modification d'un commentaire existant.
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Comment $comment Le commentaire en cours de modification
 * @var \Cake\Collection\CollectionInterface|string[] $users Liste des utilisateurs
 * @var \Cake\Collection\CollectionInterface|string[] $roadtrips Liste des roadtrips
 * @var \Cake\Collection\CollectionInterface|string[] $pointsOfInterests Liste des points d'intérêt
 */

?>

<div class="comment-edit-container">
    <div class="comment-edit-card">

        <div class="card-header">
            <h2><i class="material-icons">edit</i> Modifier votre avis !</h2>
        </div>

        <div class="card-body">
            <?= $this->Form->create($comment, ['class' => 'custom-form']) ?>

            <div style="display: none;">
                <?= $this->Form->control('user_id', ['options' => $users]) ?>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Roadtrip associé</label>
                    <div class="select-wrapper">
                        <?= $this->Form->control('roadtrip_id', [
                            'options' => $roadtrips,
                            'empty' => '-- Sélectionner un roadtrip --',
                            'label' => false,
                            'class' => 'form-select'
                        ]) ?>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Point d'intérêt</label>
                    <div class="select-wrapper">
                        <?= $this->Form->control('point_of_interest_id', [
                            'options' => $pointsOfInterests,
                            'empty' => '-- Sélectionner un lieu --',
                            'label' => false,
                            'class' => 'form-select'
                        ]) ?>
                    </div>
                </div>
            </div>

            <div class="form-group rating-group">
                <label class="form-label">Votre note</label>
                <div class="modern-star-rating">
                    <?php
                    $currentRating = $comment->rating ?? 0;
                    for ($i = 5; $i >= 1; $i--):
                        $checked = ($i == $currentRating) ? 'checked' : '';
                        ?>
                        <input type="radio" id="star<?= $i ?>" name="rating" value="<?= $i ?>" <?= $checked ?> />
                        <label for="star<?= $i ?>" title="<?= $i ?> étoiles">★</label>
                    <?php endfor; ?>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Votre commentaire</label>
                <?= $this->Form->control('body', [
                    'type' => 'textarea',
                    'rows' => 6,
                    'label' => false,
                    'class' => 'form-textarea',
                    'placeholder' => 'Détaillez votre expérience ici...'
                ]) ?>
            </div>

            <div class="card-actions">
                <?= $this->Html->link(
                    '<i class="material-icons">close</i> Annuler',
                    ['action' => 'index'],
                    ['escape' => false, 'class' => 'action-btn btn-cancel']
                ) ?>

                <button type="submit" class="action-btn btn-save">
                    <i class="material-icons">save</i> Mettre à jour
                </button>
            </div>

            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
