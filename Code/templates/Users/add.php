<?php $this->assign('mainClass', ''); ?>

<div class="main">
    <h1>Identification</h1>
    <div class="formulaire">
        <div class="in_form">
            <div class="toggle-box">
                <?= $this->Html->link('Se connecter', ['action' => 'login'], ['class' => 'toggle-btn']) ?>
                <button class="toggle-btn active" disabled>S'inscrire</button>
            </div>

            <?= $this->Form->create($user, ['class' => 'form-box', 'type' => 'file']) ?>
            <h2 id="login-title">Inscription</h2>
            <?= $this->Flash->render() ?>
            <div class="form-row">
                <?= $this->Form->control('last_name', ['label' => 'Nom', 'required' => true]) ?>
                <?= $this->Form->control('first_name', ['label' => 'Prénom', 'required' => true]) ?>
                <?= $this->Form->control('username', ['label' => 'Pseudo', 'required' => true]) ?>
            </div>

            <div class="form-row">
                <?= $this->Form->control('email', ['label' => 'Email', 'required' => true]) ?>
                <?= $this->Form->control('password', ['label' => 'Mot de passe', 'required' => true]) ?>
            </div>

            <label class="date-label-block">Date de naissance</label> <div class="date-select-container">
                <?= $this->Form->control('birth_day', [
                    'label' => false, 'type' => 'select', 'options' => $days, 'empty' => 'Jour', 'class' => 'form-select'
                ]); ?>

                <?= $this->Form->control('birth_month', [
                    'label' => false, 'type' => 'select',
                    'options' => [
                        '01' => 'Janv.', '02' => 'Fevr.', '03' => 'Mars', '04' => 'Avril',
                        '05' => 'Mai', '06' => 'Juin', '07' => 'Juill', '08' => 'Août',
                        '09' => 'Sept', '10' => 'Oct', '11' => 'Nov', '12' => 'Déc',
                    ],
                    'empty' => 'Mois'
                ]); ?>

                <?= $this->Form->control('birth_year', [
                    'label' => false, 'type' => 'select', 'options' => $years, 'empty' => 'Année', 'class' => 'form-select'
                ]); ?>
            </div>

            <?= $this->Form->button('S\'inscrire', ['class' => 'submit-btn']); ?>
            <?= $this->Form->end() ?>
        </div>
        <div style="margin-top: 20px; text-align: center;">
            <p>Ou connectez-vous avec :</p>
            <a href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'loginGoogle']) ?>" class="btn-google">
                <img src="https://upload.wikimedia.org/wikipedia/commons/c/c1/Google_%22G%22_logo.svg" alt="G" style="width:20px; vertical-align:middle; margin-right:8px;">
                Continuer avec Google
            </a>
        </div>
    </div>
</div>

