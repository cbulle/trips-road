<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Friend $friendship
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading">Actions</h4>
            <?= $this->Html->link('Liste des amis', ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="friends form content">
            <?= $this->Form->create($friendship) ?>
            <fieldset>
                <legend>Ajouter un ami</legend>
                <?php
                echo $this->Form->control('status', ['label' => 'Statut de la demande']);
                ?>
            </fieldset>
            <?= $this->Form->button('Envoyer') ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
