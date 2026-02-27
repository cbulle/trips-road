<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * ... (Header inchangé) ...
 */
?>
<!DOCTYPE html>
<html>
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
        <?= $this->fetch('title') ?>
    </title>
    <?= $this->Html->meta('icon') ?>

    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <?= $this->Html->css('https://code.jquery.com/ui/1.13.3/themes/base/jquery-ui.css') ?>

    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster/dist/MarkerCluster.css"/>
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster/dist/MarkerCluster.Default.css"/>
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css"/>

    <?php
    $controller = $this->request->getParam('controller');
    $action = $this->request->getParam('action');
    if ($controller === 'Roadtrips' && in_array($action, ['add', 'edit'])):
        ?>
        <link rel="stylesheet" href="https://uicdn.toast.com/editor/latest/toastui-editor.min.css" />
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
                    <input type="search" id="searchInput" class="search-input" placeholder="Recherche..."
                           autocomplete="off">
                    <div class="btn"><i class="fas fa-search"></i></div>
                </div>
            </li>

            <li class="title" id="link_Titre">
                <a href="<?= $this->Url->build('/') ?>">Trips & Roads</a>
            </li>

            <?php if ($currentUser): ?>
                <li class="nav-item" id="link_access">
                    <a href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'accessibility']) ?>">
                        <i class="material-icons">settings_accessibility</i>
                        <span>Paramètres</span>
                    </a>
                </li>
                <li class="nav-item" id="link_Chat">
                    <a href="<?= $this->Url->build(['controller' => 'Messages', 'action' => 'index']) ?>">
                        <i class="material-icons">chat_bubble</i>
                        <span>Messagerie</span>
                    </a>
                </li>
                <li class="nav-item" id="link_Amis">
                    <a href="<?= $this->Url->build(['controller' => 'Friendships', 'action' => 'index']) ?>">
                        <i class="material-icons">group</i>
                        <span>Amis</span>
                    </a>
                </li>
                <li class="nav-item" id="link_Crea">
                    <a href="<?= $this->Url->build(['controller' => 'Roadtrips', 'action' => 'add']) ?>">
                        <i class="material-icons">add_box</i>
                        <span>Créer un Road-Trip</span>
                    </a>
                </li>
                <li class="nav-item" id="link_PP">
                    <span class="profil-box">
                        <?php
                        $fileName = $currentUser->profile_picture;
                        $physicalPath = WWW_ROOT . 'uploads' . DS . 'pp' . DS . $fileName;
                        if (!empty($fileName) && file_exists($physicalPath)) {
                            $ppUrl = $this->Url->build('/uploads/pp/' . $fileName);
                        } else {
                            $ppUrl = $this->Url->build('/img/User.png');
                        }
                        ?>
                        <a href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'profile']) ?>">
                            <img src="<?= $ppUrl ?>" class="profil-photo" alt="Profil">
                        </a>
                        <span class="profil-nom">
                            <?= h($currentUser->username ?? $currentUser->prenom) ?>
                        </span>
                    </span>
                </li>
                <li class="nav-item" id="link_Deco">
                    <a class="pp_logout"
                       href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'logout']) ?>">
                        <i class="material-icons">logout</i>
                        <span>Déconnexion</span>
                    </a>
                </li>
            <?php else: ?>
                <li class="nav-item" id="link_access">
                    <a href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'accessibility']) ?>">
                        <i class="material-icons">settings_accessibility</i>
                        <span>Accessibilité</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'login']) ?>">
                        <i class="material-icons">account_circle</i>
                        <span>Se connecter</span>
                    </a>
                </li>
            <?php endif; ?>
        </ul>

        <input type="checkbox" id="burger">
        <label for="burger" class="burger"><span></span></label>

        <ul class="ul_burger">
            <?php if ($currentUser): ?>
                <li><a href="<?= $this->Url->build(['controller' => 'Roadtrips', 'action' => 'publicRoadtrips']) ?>">Roads-Trips Publics</a></li>
                <li><a href="<?= $this->Url->build(['controller' => 'Roadtrips', 'action' => 'myRoadtrips']) ?>">Mes Roads-Trips</a></li>
                <li><a href="<?= $this->Url->build(['controller' => 'Comments', 'action' => 'index']) ?>">Commentaire</a></li>
                <li><a href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'profile']) ?>">Mon Compte</a></li>
                <li><a href="<?= $this->Url->build(['controller' => 'Favorites', 'action' => 'index']) ?>">Favoris</a></li>
                <li><a href="<?= $this->Url->build(['controller' => 'Roadtrips', 'action' => 'historique']) ?>">Historique</a></li>
                <li><a href="<?= $this->Url->build(['controller' => 'PageLink', 'action' => 'faq']) ?>">Aide / FAQ</a></li>
                <li><a href="<?= $this->Url->build(['controller' => 'PageLink', 'action' => 'contact']) ?>">A propos / Contact</a></li>
                <li><a href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'logout']) ?>">Déconnexion</a></li>
            <?php else: ?>
                <li><a href="<?= $this->Url->build(['controller' => 'Roadtrips', 'action' => 'publicRoadtrips']) ?>">Voir les RoadTrips</a></li>
                <li><a href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'login']) ?>">Se connecter</a></li>
                <li><a href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'add']) ?>">S'inscrire</a></li>
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
            <img src="<?= $this->Url->webroot('img/logoProjet.png') ?>" alt="Logo du site web">
        </div>
        <div class="social-media">
            <a href="https://www.instagram.com" class="social-icon" target="_blank"><i class="fab fa-instagram"></i></a>
            <a href="https://www.facebook.com" class="social-icon" target="_blank"><i class="fab fa-facebook-f"></i></a>
            <a href="https://www.x.com" class="social-icon" target="_blank"><i class="fa-brands fa-x-twitter"></i></a>
        </div>
        <ul class="footer-links">
            <li><a href="<?= $this->Url->build(['controller' => 'PageLink', 'action' => 'contact']) ?>" class="un">Contact </a></li>
            <li><a href="<?= $this->Url->build(['controller' => 'PageLink', 'action' => 'cgu']) ?>" class="deux">CGU</a></li>
            <li><a href="<?= $this->Url->build(['controller' => 'PageLink', 'action' => 'politique']) ?>" class="trois">Politique de confidentialité</a></li>
            <li><a href="<?= $this->Url->build(['controller' => 'PageLink', 'action' => 'faq']) ?>" class="quatre">FAQ</a></li>
            <li><a href="<?= $this->Url->build(['controller' => 'Roadtrips', 'action' => 'index']) ?>" class="cinq">Road-Trip</a></li>
            <li><a href="<?= $this->Url->build(['controller' => 'PageLink', 'action' => 'cookie']) ?>" class="six">Gestion des cookies</a></li>
        </ul>
    </div>
</footer>

<script src="https://kit.fontawesome.com/d76759a8b0.js" crossorigin="anonymous"></script>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
<script src="https://unpkg.com/leaflet.markercluster/dist/leaflet.markercluster.js"></script>
<?= $this->Html->script('https://code.jquery.com/jquery-3.6.0.min.js') ?>
<?= $this->Html->script('https://code.jquery.com/ui/1.13.3/jquery-ui.min.js') ?>

<?php
if ($controller === 'Roadtrips' && in_array($action, ['add', 'edit'])):
    ?>
    <script src="https://uicdn.toast.com/editor/latest/toastui-editor-all.min.js"></script>
    <script src="https://uicdn.toast.com/editor/latest/i18n/fr-fr.min.js"></script>
<?php endif; ?>

<?php if ($controller === 'Roadtrips' && $action === 'view'): ?>
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/3.0.6/purify.min.js"></script>
<?php endif; ?>

<?php if ($controller === 'Roadtrips' && $action === 'index'): ?>
    <script src="https://cdn.jsdelivr.net/npm/leaflet@1.7.1/dist/leaflet.js"></script>
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
