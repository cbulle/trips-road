<?php
$this->assign('mainClass', 'comment-edit-page');

/**
 * Page containing the form to create a new comment.
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Comment $comment New empty comment entity
 * @var \Cake\Collection\CollectionInterface|string[] $roadtrips List of roadtrips
 * @var \Cake\Collection\CollectionInterface|string[] $pointsOfInterests List of points of interest
 */
?>

<div class="comment-edit-container">
    <div class="comment-edit-card">

        <div class="card-header">
            <h2><i class="material-icons">add_comment</i> Ajouter un avis</h2>
        </div>

        <div class="card-body">
            <?= $this->Form->create($comment, ['class' => 'custom-form']) ?>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Roadtrip associé</label>
                    <div class="select-wrapper">
                        <?= $this->Form->control('roadtrip_id', [
                            'options' => $roadtrips ?? [],
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
                            'options' => $pointsOfInterests ?? [],
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
                    <?php for ($i = 5; $i >= 1; $i--): ?>
                        <input type="radio" id="star<?= $i ?>" name="rating" value="<?= $i ?>" <?= $i === 5 ? 'checked' : '' ?> />
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
                    <i class="material-icons">send</i> Publier
                </button>
            </div>

            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
