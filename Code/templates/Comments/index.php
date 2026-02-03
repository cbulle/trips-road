<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Comment $comment
 */
?>

<h1>Ajouter un commentaire</h1>

<?= $this->Form->create($comment) ?>

<fieldset>
    <legend>Votre commentaire</legend>

    <?= $this->Form->control('author', [
        'label' => 'Votre nom',
        'required' => true
    ]) ?>

    <?= $this->Form->control('content', [
        'label' => 'Commentaire',
        'type' => 'textarea',
        'required' => true
    ]) ?>
</fieldset>

<?= $this->Form->button('Envoyer le commentaire') ?>
<?= $this->Form->end() ?>

<br>

<?= $this->Html->link(
    'Retour',
    ['controller' => 'Roadtrips', 'action' => 'index'],
    ['class' => 'button']
) ?>
