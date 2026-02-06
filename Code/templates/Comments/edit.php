<h1>Modifier le commentaire</h1>

<?= $this->Form->create($comment) ?>

<fieldset>
    <legend>Modification</legend>

    <?= $this->Form->control('user_id', [
        'options' => $users
    ]) ?>

    <?= $this->Form->control('roadtrip_id', [
        'options' => $roadtrips,
        'empty' => true
    ]) ?>

    <?= $this->Form->control('point_of_interest_id', [
        'options' => $pointsOfInterests,
        'empty' => true
    ]) ?>
    <?= $this->Form->control('rating', [
        'options' => [
            1 => '⭐',
            2 => '⭐⭐',
            3 => '⭐⭐⭐',
            4 => '⭐⭐⭐⭐',
            5 => '⭐⭐⭐⭐⭐',
        ],
        'legend' => false
    ]) ?>


    <?= $this->Form->control('body', [
        'type' => 'textarea',
        'rows' => 5
    ]) ?>
</fieldset>

<?= $this->Form->button('Mettre à jour') ?>
<?= $this->Form->end() ?>

<?= $this->Html->link('Retour', ['action' => 'myRoadtrips']) ?>
