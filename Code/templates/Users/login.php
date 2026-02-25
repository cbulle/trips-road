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
        <div style="margin-top: 20px; text-align: center;">
            <p>Ou connectez-vous avec :</p>
            <a href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'loginGoogle']) ?>" class="btn-google">
                <img src="https://upload.wikimedia.org/wikipedia/commons/c/c1/Google_%22G%22_logo.svg" alt="G" style="width:20px; vertical-align:middle; margin-right:8px;">
                Continuer avec Google
            </a>
        </div>

        <style>
            .btn-google {
                display: inline-block;
                background: white;
                color: #444;
                border: 1px solid #ddd;
                padding: 10px 20px;
                border-radius: 5px;
                text-decoration: none;
                font-weight: bold;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                transition: background 0.3s;
            }
            .btn-google:hover {
                background: #f1f1f1;
            }
        </style>
    </div>
</div>
