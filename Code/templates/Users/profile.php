<?php
$this->assign('mainClass', 'profil-container');
$fileName = $user->profile_picture;
$physicalPath = WWW_ROOT . 'uploads' . DS . 'pp' . DS . $fileName;

if (!empty($fileName) && file_exists($physicalPath)) {
    $urlImage = '/uploads/pp/' . $fileName;
} else {
    $urlImage = '/img/User.png';
}
?>

<aside class="profil-sidebar">
    <div class="user-brief">
        <div class="avatar-circle small" style="background-image: url('<?= $urlImage ?>');"></div>
        <h3><?= $user->first_name . ' ' . $user->last_name ?></h3>
    </div>

    <nav class="profil-nav">
        <ul>
            <li><a href="<?= $this->Url->build(['controller' => 'Roadtrips', 'action' => 'myRoadtrips']) ?>">Mes Road-Trips</a></li>
            <li><a href="<?= $this->Url->build(['controller' => 'Roadtrips', 'action' => 'publicRoadtrips']) ?>">Road-Trips publics</a></li>
            <li><a href="#" class="active">Paramètres du compte</a></li>
            <li>
                <a href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'accessibility']) ?>">Accessibilité</a>
            </li>
            <li><a href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'logout']) ?>" class="logout">Déconnexion</a>
            </li>
        </ul>
    </nav>
</aside>

<section class="profil-content">
    <div class="card-header">
        <h1>Mon Profil</h1>
        <p>Gérez vos informations personnelles et vos préférences de sécurité.</p>
    </div>

    <?= $this->Form->create($user, [
        'id' => 'profilForm',
        'class' => 'form_modif',
        'type' => 'file',
    ]) ?>

    <div class="form-section photo-section">
        <div class="avatar-wrapper">
            <div class="avatar-circle large"
                 style="background-image: url('<?= $this->Url->build($urlImage) ?>');"></div>

            <div class="avatar-upload">
                <label for="image" class="btn-upload">Changer la photo</label>
                <?= $this->Form->control('profile_picture_file', [
                    'type' => 'file',
                    'id' => 'image',
                    'label' => false,
                    'accept' => 'image/*',
                    'templates' => ['inputContainer' => '{{content}}'] // On retire le wrapper div de Cake
                ]) ?>
            </div>
        </div>
    </div>

    <hr class="divider">

    <div class="form-section">
        <h2>Identité</h2>
        <div class="form-grid">

            <div class="form-group">
                <?= $this->Form->control('username', [
                    'label' => 'Pseudo',
                    'required' => true,
                    'templates' => ['inputContainer' => '{{content}}']
                ]) ?>
            </div>

            <div class="form-group"></div>

            <div class="form-group">
                <?= $this->Form->control('first_name', [
                    'label' => 'Prénom',
                    'required' => true,
                    'templates' => ['inputContainer' => '{{content}}']
                ]) ?>
            </div>

            <div class="form-group">
                <?= $this->Form->control('last_name', [
                    'label' => 'Nom',
                    'required' => true,
                    'templates' => ['inputContainer' => '{{content}}']
                ]) ?>
            </div>
        </div>
    </div>

    <div class="form-section">
        <h2>Coordonnées</h2>
        <div class="form-grid">

            <div class="form-group full-width">
                <?= $this->Form->control('email', [
                    'label' => 'Adresse email',
                    'required' => true,
                    'templates' => ['inputContainer' => '{{content}}']
                ]) ?>
            </div>

            <div class="form-group">
                <?= $this->Form->control('phone', [
                    'label' => 'Téléphone',
                    'type' => 'tel',
                    'templates' => ['inputContainer' => '{{content}}']
                ]) ?>
            </div>

            <div class="form-group">
                <?= $this->Form->control('birth_date', [
                    'label' => 'Date de naissance',
                    'type' => 'date',
                    'templates' => ['inputContainer' => '{{content}}']
                ]) ?>
            </div>
        </div>
    </div>

    <div class="form-section">
        <h2>Adresse</h2>
        <div class="form-grid">

            <div class="form-group full-width">
                <?= $this->Form->control('address', [
                    'label' => 'Rue & Numéro',
                    'templates' => ['inputContainer' => '{{content}}']
                ]) ?>
            </div>

            <div class="form-group">
                <?= $this->Form->control('zipcode', [
                    'label' => 'Code postal',
                    'templates' => ['inputContainer' => '{{content}}']
                ]) ?>
            </div>

            <div class="form-group">
                <?= $this->Form->control('city', [
                    'label' => 'Ville',
                    'templates' => ['inputContainer' => '{{content}}']
                ]) ?>
            </div>
        </div>
    </div>

    <hr class="divider">

    <div class="form-section">
        <h2>Sécurité</h2>
        <div class="form-grid">
            <div class="form-group">
                <?= $this->Form->control('password', [
                    'label' => 'Nouveau mot de passe',
                    'value' => '',
                    'required' => false,
                    'placeholder' => 'Laisser vide si inchangé',
                    'templates' => ['inputContainer' => '{{content}}']
                ]) ?>
            </div>

            <div class="form-group">
                <?= $this->Form->control('confirm_password', [
                    'type' => 'password',
                    'label' => 'Confirmer le mot de passe',
                    'value' => '',
                    'required' => false,
                    'templates' => ['inputContainer' => '{{content}}']
                ]) ?>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <?= $this->Form->button('Enregistrer les modifications', ['class' => 'btn-save']) ?>
    </div>

    <?= $this->Form->end() ?>
</section>
