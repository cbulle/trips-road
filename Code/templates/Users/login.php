<?php $this->assign('mainClass', ''); ?>

<div class="main">
    <h1>Identification</h1>
    <div class="formulaire">
        <div class="in_form">
            <div class="toggle-box">
                <button class="toggle-btn active" disabled>Se connecter</button>
                <a href="<?= $this->Url->build(['action' => 'add']) ?>" class="toggle-btn">S'inscrire</a>
            </div>

            <?= $this->Form->create(null, ['class' => 'form-box', 'id' => 'loginForm']) ?>
            <h2 id="register-title">Connexion</h2>

            <?= $this->Form->control('email', ['label' => 'Adresse email', 'required' => true]) ?>
            <?= $this->Form->control('password', ['label' => 'Mot de passe', 'required' => true]) ?>

            <label>
                <input type="checkbox" name="remember_me" value="1"> Se souvenir de moi
            </label>

            <div class="forgot-password">
                <?= $this->Html->link('Mot de passe oublié ?', ['action' => 'forgotPassword']) ?>
            </div>

            <button type="submit">Se connecter</button>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
