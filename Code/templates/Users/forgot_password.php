<div class="main">
    <h1>Mot de passe oublié</h1>
    <div class="formulaire">
        <?= $this->Form->create(null) ?>
        <p>Entrez votre email pour recevoir un lien de réinitialisation.</p>
        <?= $this->Form->control('email', ['label' => 'Votre adresse email', 'required' => true]) ?>
        <?= $this->Form->button('Envoyer le lien', ['class' => 'submit-btn']) ?>
        <?= $this->Form->end() ?>
    </div>
</div>
