<?php $this->assign('mainClass', ''); ?>

<div class="main">
    <h1>Identification</h1>
    <div class="formulaire">
        <div class="in_form">
            <div class="toggle-box">
                <a href="<?= $this->Url->build(['action' => 'login']) ?>" class="toggle-btn">Se connecter</a>
                <button class="toggle-btn active" disabled>S'inscrire</button>
            </div>

            <?= $this->Form->create($user, ['class' => 'form-box', 'type' => 'file']) ?>
            <h2 id="login-title">Inscription</h2>

            <?= $this->Form->control('username', ['label' => 'Pseudo', 'required' => true]) ?>
            <?= $this->Form->control('last_name', ['label' => 'Nom', 'required' => true]) ?>
            <?= $this->Form->control('first_name', ['label' => 'Prénom', 'required' => true]) ?>
            <?= $this->Form->control('email', ['label' => 'Email', 'required' => true]) ?>

            <?= $this->Form->control('password', ['label' => 'Mot de passe', 'required' => true]) ?>
            <?= $this->Form->control('address', ['label' => 'Adresse']) ?>
            <?= $this->Form->control('zipcode', ['label' => 'Code postal']) ?>
            <?= $this->Form->control('city', ['label' => 'Ville']) ?>
            <?= $this->Form->control('phone', ['label' => 'Téléphone']) ?>
            <?= $this->Form->control('birth_date', ['label' => 'Date de naissance', 'type' => 'date', 'min' => '1900-01-01']) ?>

            <label for="image">Photo de profil</label>
            <?= $this->Form->file('image', ['accept' => 'image/*']) ?>

            <button type="submit">S'inscrire</button>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
