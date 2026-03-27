<div class="main">
    <h1>Nouveau mot de passe</h1>
    <div class="formulaire">
        <?= $this->Form->create($user) ?>
        <?= $this->Form->control('password', ['label' => 'Nouveau mot de passe', 'required' => true]) ?>
        <?= $this->Form->control('confirm_password', ['type' => 'password', 'label' => 'Confirmer le mot de passe', 'required' => true]) ?>
        <?= $this->Form->button('Modifier le mot de passe', ['class' => 'submit-btn']) ?>
        <?= $this->Form->end() ?>
    </div>
</div>
