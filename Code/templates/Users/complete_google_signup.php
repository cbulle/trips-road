<?php $this->assign('mainClass', ''); ?>

<div class="main">
    <h1>Finaliser l'inscription</h1>
    <div class="formulaire">
        <div class="in_form">
            <?= $this->Form->create($user, ['class' => 'form-box']) ?>

            <h2 id="login-title" style="text-align: center; margin-bottom: 20px;">Dernière étape !</h2>

            <p style="text-align: center; color: #666; margin-bottom: 20px;">
                Votre compte Google a bien été reconnu.<br>
                Choisissez un pseudo pour votre aventure sur Trips & Roads :
            </p>

            <?= $this->Flash->render() ?>

            <div class="form-row" style="display: block; margin-bottom: 20px;">
                <?= $this->Form->control('username', [
                    'label' => 'Votre Pseudo',
                    'required' => true,
                    'placeholder' => 'Ex: VoyageurDu69',
                    'style' => 'width: 100%; padding: 10px; box-sizing: border-box;'
                ]) ?>
            </div>

            <?= $this->Form->button('Valider mon compte', ['class' => 'submit-btn', 'style' => 'width: 100%;']); ?>

            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
