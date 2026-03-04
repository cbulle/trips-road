<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * ... (Header inchangé) ...
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
        <?= $this->fetch('title') ?>
    </title>

    <script>
        (function() {
            try {
                if (localStorage.getItem("theme") === "dark") {
                    document.documentElement.classList.add("dark", "SombreBtn");
                }
                if (localStorage.getItem("Police") === "malvoyant") {
                    document.documentElement.classList.add("malvoyant", "MalvoyantBtn");
                }
                var typeDaltonien = localStorage.getItem("typeDaltonien");
                if (typeDaltonien && typeDaltonien !== "aucun") {
                    document.documentElement.classList.add("daltonien", typeDaltonien);
                }
            } catch (e) {}
        })();
    </script>

    <?= $this->Html->meta('icon') ?>

    <?= $this->Html->css([
        'https://fonts.googleapis.com/icon?family=Material+Icons',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css',
        'https://unpkg.com/leaflet.markercluster/dist/MarkerCluster.css',
        'https://unpkg.com/leaflet.markercluster/dist/MarkerCluster.Default.css',
        'https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css'
    ]) ?>

    <?= $this->Html->css('https://unpkg.com/leaflet@1.9.4/dist/leaflet.css', [
        'integrity' => 'sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=',
        'crossorigin' => ''
    ]) ?>
    <?= $this->Html->css('https://code.jquery.com/ui/1.13.3/themes/base/jquery-ui.css') ?>

    <?php
    $controller = $this->request->getParam('controller');
    $action = $this->request->getParam('action');
    if ($controller === 'Roadtrips' && in_array($action, ['add', 'edit'])):
        echo $this->Html->css('https://uicdn.toast.com/editor/latest/toastui-editor.min.css');
        ?>
        <style>
            .toastui-editor-defaultUI { z-index: 1000; }
            .subEtapeEditorContainer { background: white; }
        </style>
    <?php endif; ?>

    <?= $this->Html->css([
        'accessibilite',
        'creationRT',
        'favoris',
        'footer',
        'form',
        'header',
        'index',
        'messagerie',
        'messaging-realtime',
        'page_link',
        'profil',
        'roadTrip',
        'style',
        'view',
    ]) ?>

    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
</head>
<body>
<?php
$currentUser = $this->request->getAttribute('identity');
?>

<header>
    <nav>
        <ul>
            <li class="nav-item">
                <div class="bar_rech">
                    <input type="search" id="searchInput" class="search-input" placeholder="Recherche..." autocomplete="off">
                    <div class="btn"><i class="fas fa-search"></i></div>
                </div>
            </li>

            <li class="title" id="link_Titre">
                <?= $this->Html->link('Trips & Roads', '/') ?>
            </li>

            <?php if ($currentUser): ?>
                <li class="nav-item" id="link_access">
                    <?= $this->Html->link(
                        '<i class="material-icons">settings_accessibility</i><span>Paramètres</span>',
                        ['controller' => 'Users', 'action' => 'accessibility'],
                        ['escape' => false]
                    ) ?>
                </li>
                <li class="nav-item" id="link_Chat">
                    <?= $this->Html->link(
                        '<i class="material-icons">chat_bubble</i><span>Messagerie</span>',
                        ['controller' => 'Messages', 'action' => 'index'],
                        ['escape' => false]
                    ) ?>
                </li>
                <li class="nav-item" id="link_Amis">
                    <?= $this->Html->link(
                        '<i class="material-icons">group</i><span>Amis</span>',
                        ['controller' => 'Friendships', 'action' => 'index'],
                        ['escape' => false]
                    ) ?>
                </li>
                <li class="nav-item" id="link_Crea">
                    <?= $this->Html->link(
                        '<i class="material-icons">add_box</i><span>Créer un Road-Trip</span>',
                        ['controller' => 'Roadtrips', 'action' => 'add'],
                        ['escape' => false]
                    ) ?>
                </li>
                <li class="nav-item" id="link_PP">
                    <span class="profil-box">
                        <?php
                        $fileName = $currentUser->profile_picture;
                        $physicalPath = WWW_ROOT . 'uploads' . DS . 'pp' . DS . $fileName;

                        if (!empty($fileName) && file_exists($physicalPath)) {
                            $ppImg = $this->Html->image('/uploads/pp/' . $fileName, ['class' => 'profil-photo', 'alt' => 'Profil']);
                        } else {
                            $ppImg = $this->Html->image('User.png', ['class' => 'profil-photo', 'alt' => 'Profil']);
                        }

                        echo $this->Html->link(
                            $ppImg,
                            ['controller' => 'Users', 'action' => 'profile'],
                            ['escape' => false]
                        );
                        ?>
                        <span class="profil-nom">
                            <?= h($currentUser->username ?? $currentUser->prenom) ?>
                        </span>
                    </span>
                </li>
                <li class="nav-item" id="link_Deco">
                    <?= $this->Html->link(
                        '<i class="material-icons">logout</i><span>Déconnexion</span>',
                        ['controller' => 'Users', 'action' => 'logout'],
                        ['escape' => false, 'class' => 'pp_logout']
                    ) ?>
                </li>
            <?php else: ?>
                <li class="nav-item" id="link_access">
                    <?= $this->Html->link(
                        '<i class="material-icons">settings_accessibility</i><span>Accessibilité</span>',
                        ['controller' => 'Users', 'action' => 'accessibility'],
                        ['escape' => false]
                    ) ?>
                </li>
                <li class="nav-item">
                    <?= $this->Html->link(
                        '<i class="material-icons">account_circle</i><span>Se connecter</span>',
                        ['controller' => 'Users', 'action' => 'login'],
                        ['escape' => false]
                    ) ?>
                </li>
            <?php endif; ?>
        </ul>

        <input type="checkbox" id="burger">
        <label for="burger" class="burger"><span></span></label>

        <ul class="ul_burger">
            <?php if ($currentUser): ?>
                <li><?= $this->Html->link('Roads-Trips Publics', ['controller' => 'Roadtrips', 'action' => 'publicRoadtrips']) ?></li>
                <li><?= $this->Html->link('Mes Roads-Trips', ['controller' => 'Roadtrips', 'action' => 'myRoadtrips']) ?></li>
                <li><?= $this->Html->link('Commentaires', ['controller' => 'Comments', 'action' => 'index']) ?></li>
                <li><?= $this->Html->link('Mon Compte', ['controller' => 'Users', 'action' => 'profile']) ?></li>
                <li><?= $this->Html->link('Favoris', ['controller' => 'Favorites', 'action' => 'index']) ?></li>
                <li><?= $this->Html->link('Historique', ['controller' => 'Roadtrips', 'action' => 'historique']) ?></li>
                <li><?= $this->Html->link('Aide / FAQ', ['controller' => 'PageLink', 'action' => 'faq']) ?></li>
                <li><?= $this->Html->link('A propos / Contact', ['controller' => 'PageLink', 'action' => 'contact']) ?></li>
                <li><?= $this->Html->link('Déconnexion', ['controller' => 'Users', 'action' => 'logout']) ?></li>
            <?php else: ?>
                <li><?= $this->Html->link('Voir les RoadTrips', ['controller' => 'Roadtrips', 'action' => 'publicRoadtrips']) ?></li>
                <li><?= $this->Html->link('Se connecter', ['controller' => 'Users', 'action' => 'login']) ?></li>
                <li><?= $this->Html->link('S\'inscrire', ['controller' => 'Users', 'action' => 'add']) ?></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>

<?php
$showMain = $this->fetch('showMain', true);
$mainClass = $this->fetch('mainClass', 'main-index');
?>

<?php if ($showMain): ?>
    <main class="<?= $mainClass ?>">
        <?= $this->Flash->render() ?>
        <?= $this->fetch('content') ?>
    </main>
<?php else: ?>
    <?= $this->Flash->render() ?>
    <?= $this->fetch('content') ?>
<?php endif; ?>

<footer>
    <div class="footer-container">
        <div class="image-container">
            <?= $this->Html->image('logoProjet.png', ['alt' => 'Logo du site web']) ?>
        </div>
        <div class="social-media">
            <?= $this->Html->link('<i class="fab fa-instagram"></i>', 'https://www.instagram.com', ['class' => 'social-icon', 'target' => '_blank', 'escape' => false]) ?>
            <?= $this->Html->link('<i class="fab fa-facebook-f"></i>', 'https://www.facebook.com', ['class' => 'social-icon', 'target' => '_blank', 'escape' => false]) ?>
            <?= $this->Html->link('<i class="fa-brands fa-x-twitter"></i>', 'https://www.x.com', ['class' => 'social-icon', 'target' => '_blank', 'escape' => false]) ?>
        </div>
        <ul class="footer-links">
            <li><?= $this->Html->link('Contact', ['controller' => 'PageLink', 'action' => 'contact'], ['class' => 'un']) ?></li>
            <li><?= $this->Html->link('CGU', ['controller' => 'PageLink', 'action' => 'cgu'], ['class' => 'deux']) ?></li>
            <li><?= $this->Html->link('Politique de confidentialité', ['controller' => 'PageLink', 'action' => 'politique'], ['class' => 'trois']) ?></li>
            <li><?= $this->Html->link('FAQ', ['controller' => 'PageLink', 'action' => 'faq'], ['class' => 'quatre']) ?></li>
            <li><?= $this->Html->link('Road-Trip', ['controller' => 'Roadtrips', 'action' => 'index'], ['class' => 'cinq']) ?></li>
            <li><?= $this->Html->link('Gestion des cookies', ['controller' => 'PageLink', 'action' => 'cookie'], ['class' => 'six']) ?></li>
        </ul>
    </div>
</footer>

<?= $this->Html->script('https://kit.fontawesome.com/d76759a8b0.js', ['crossorigin' => 'anonymous']) ?>
<?= $this->Html->script('https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', [
    'integrity' => 'sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=',
    'crossorigin' => ''
]) ?>

<?= $this->Html->script([
    'https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js',
    'https://unpkg.com/leaflet.markercluster/dist/leaflet.markercluster.js'
]) ?>

<?= $this->Html->script('https://code.jquery.com/jquery-3.6.0.min.js') ?>
<?= $this->Html->script('https://code.jquery.com/ui/1.13.3/jquery-ui.min.js') ?>

<?php if ($controller === 'Roadtrips' && in_array($action, ['add', 'edit'])): ?>
    <?= $this->Html->script([
        'https://uicdn.toast.com/editor/latest/toastui-editor-all.min.js',
        'https://uicdn.toast.com/editor/latest/i18n/fr-fr.min.js'
    ]) ?>
<?php endif; ?>

<?php if ($controller === 'Roadtrips' && $action === 'view'): ?>
    <?= $this->Html->script([
        'https://cdn.jsdelivr.net/npm/marked/marked.min.js',
        'https://cdnjs.cloudflare.com/ajax/libs/dompurify/3.0.6/purify.min.js'
    ]) ?>
<?php endif; ?>


<?php if ($controller === 'Roadtrips' && $action === 'index'): ?>
    <?= $this->Html->script('https://cdn.jsdelivr.net/npm/leaflet@1.7.1/dist/leaflet.js') ?>
<?php endif; ?>

<?= $this->Html->script([
    'encryption',
    'index',
    'map',
    'messagerie',
    'modal',
    'profil',
    'recherche',
    'viewRoadtrip',
    'accessibility',
]) ?>

<?= $this->fetch('script') ?>
</body>
</html>
