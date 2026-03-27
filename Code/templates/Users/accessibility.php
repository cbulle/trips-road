<?php
$this->assign('mainClass', 'accessibilite-container');
?>

<aside class="profil-sidebar">
    <div class="user-brief">
        <div class="avatar-circle small" style="background-image: url('<?= $this->Url->build($user->avatar_url) ?>');"></div>
        <h3><?= h($user->username) ?></h3>
    </div>

    <nav class="profil-nav">
        <ul>
            <li><?= $this->Html->link('Mes Road-Trips', ['controller' => 'Roadtrips', 'action' => 'myRoadtrips']) ?></li>
            <li><?= $this->Html->link('Road-Trips publics', ['controller' => 'Roadtrips', 'action' => 'publicRoadtrips']) ?></li>
            <li><?= $this->Html->link('Paramètres du compte', ['controller' => 'Users', 'action' => 'profile']) ?></li>
            <li><?= $this->Html->link('Accessibilité', '#', ['class' => 'active']) ?></li>
            <li><?= $this->Html->link('Déconnexion', ['controller' => 'Users', 'action' => 'logout'], ['class' => 'logout']) ?></li>
        </ul>
    </nav>
</aside>

<section class="cont_access">
    <?= $this->Form->create(null, ['class' => 'accessForm', 'id' => 'accessForm']) ?>
    <h2 id="access-title">Accessibilité</h2>

    <label for="checkboxSombre">Mode sombre :</label>
    <div class="btnSombre">
        <label class="switch">
            <?= $this->Form->checkbox('mode_sombre', ['id' => 'checkboxSombre', 'value' => '1', 'checked' => $isDarkMode]) ?>
            <div class="slider round"></div>
        </label>
    </div>

    <label for="checkboxMalvoyant">Mode malvoyant :</label>
    <div class="btnMalvoyant">
        <label class="switch">
            <?= $this->Form->checkbox('mode_Malvoyant', ['id' => 'checkboxMalvoyant', 'value' => '1', 'checked' => $isVisionMode]) ?>
            <div class="slider round"></div>
        </label>
    </div>

    <label>Mode daltonien :</label>
    <div class="daltonism-options">
        <?= $this->Form->radio('daltonism-type', [
            ['value' => 'aucun', 'text' => ' Aucun / Désactivé'],
            ['value' => 'protanopia', 'text' => ' Protanopie (Rouge/Vert)'],
            ['value' => 'deuteranopia', 'text' => ' Deutéranopie (Rouge/Vert)'],
            ['value' => 'tritanopia', 'text' => ' Tritanopie (Bleu/Jaune)']
        ], [
            'value' => $colorBlindType
        ]) ?>
    </div>

    <?= $this->Form->button('Enregistrer les préférences', ['type' => 'submit', 'id' => 'confirmed']) ?>
    <?= $this->Form->end() ?>
</section>
