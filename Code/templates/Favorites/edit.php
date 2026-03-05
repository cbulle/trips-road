<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Favorite $favorite
 * @var string[]|\Cake\Collection\CollectionInterface $users
 * @var string[]|\Cake\Collection\CollectionInterface $roadtrips
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $favorite->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $favorite->id), 'class' => 'side-nav-item']
            ) ?>
            <?= $this->Html->link(__('List Favorites'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="favorites form content">
            <?= $this->Form->create($favorite) ?>
            <fieldset>
                <legend><?= __('Edit Favorite') ?></legend>
                <?php
                    echo $this->Form->control('user_id', ['options' => $users]);
                    echo $this->Form->control('roadtrip_id', ['options' => $roadtrips]);
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
