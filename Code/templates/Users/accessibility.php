<?php $this->assign('mainClass', 'accessibilite-container'); ?>

<aside class="profil-sidebar">
    <div class="user-brief">
        <?php
        $pp = $user->profile_picture ?: 'User.png';
        ?>
        <div class="avatar-circle small" style="background-image: url('<?= $this->Url->webroot('uploads/pp/' . $pp) ?>');"></div>
        <h3><?= h($user->username) ?></h3>
    </div>

    <nav class="profil-nav">
        <ul>
            <li><a href="<?= $this->Url->build(['controller' => 'Roadtrips', 'action' => '#']) ?>">Mes Road-Trips</a></li>
            <li><a href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'profile']) ?>">Paramètres du compte</a></li>
            <li><a href="#" class="active">Accessibilité</a></li>
            <li><a href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'logout']) ?>" class="logout">Déconnexion</a></li>
        </ul>
    </nav>
</aside>

<section class="cont_access">
    <?= $this->Form->create(null, ['class' => 'AccessForm', 'id' => 'AccessForm']) ?>
    <h2 id="login-title">Accessibilité</h2>

    <label for="checkboxSombre">Mode sombre :</label>
    <div class="btnSombre">
        <label class="switch">
            <input type="checkbox" name="mode_sombre" id="checkboxSombre" value="1"
                <?= $this->request->getCookie('modeSombre') ? 'checked' : '' ?> />
            <div class="slider round"></div>
        </label>
    </div>

    <label>Mode daltonien :</label>
    <div class="daltonism-options">
        <?php
        $cookieDaltonien = $this->request->getCookie('typeDaltonien');
        ?>
        <label>
            <input type="radio" name="daltonism-type" value="protanopia" <?= $cookieDaltonien == 'protanopia' ? 'checked' : '' ?>>
            Protanopie (Rouge/Vert)
        </label>
        <label>
            <input type="radio" name="daltonism-type" value="deuteranopia" <?= $cookieDaltonien == 'deuteranopia' ? 'checked' : '' ?>>
            Deutéranopie (Rouge/Vert)
        </label>
        <label>
            <input type="radio" name="daltonism-type" value="tritanopia" <?= $cookieDaltonien == 'tritanopia' ? 'checked' : '' ?>>
            Tritanopie (Bleu/Jaune)
        </label>
    </div>

    <button type="submit" style="margin-top:20px;">Enregistrer les préférences</button>
    <?= $this->Form->end() ?>
</section>
