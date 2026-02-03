<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Comment $comment
 * @var \Cake\Collection\CollectionInterface|string[] $users
 * @var \Cake\Collection\CollectionInterface|string[] $roadtrips
 * @var \Cake\Collection\CollectionInterface|string[] $pointsOfInterests
 */
?>

<h1>Ajouter un commentaire</h1>

<?= $this->Form->create($comment) ?>

<fieldset>
    <legend>Nouveau commentaire</legend>

    <?= $this->Form->control('user_id', [
        'label' => 'Utilisateur',
        'options' => $users
    ]) ?>

    <?= $this->Form->control('roadtrip_id', [
        'label' => 'Roadtrip',
        'options' => $roadtrips,
        'empty' => true
    ]) ?>

    <?= $this->Form->control('point_of_interest_id', [
        'label' => 'Point d’intérêt',
        'empty' => true
    ]) ?>
    <?= $this->Form->control('rating', [
        'label' => 'Note',
        'options' => [
            1 => '⭐',
            2 => '⭐⭐',
            3 => '⭐⭐⭐',
            4 => '⭐⭐⭐⭐',
            5 => '⭐⭐⭐⭐⭐',
            'empty' => false
        ],
        'legend' => false
    ]) ?>


    <?= $this->Form->control('body', [
        'label' => 'Commentaire',
        'type' => 'textarea',
        'rows' => 5
    ]) ?>
</fieldset>

<?= $this->Form->button('Enregistrer') ?>
<?= $this->Form->end() ?>

<?= $this->Html->link('Retour', ['action' => 'index']) ?>
