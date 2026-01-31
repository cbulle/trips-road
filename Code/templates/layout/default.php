<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         0.10.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @var \App\View\AppView $this
 */

$cakeDescription = 'CakePHP: the rapid development php framework';
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.7.1/dist/leaflet.css" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <script src="https://kit.fontawesome.com/d76759a8b0.js" crossorigin="anonymous"></script>

    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
        <?= $this->fetch('title') ?>
    </title>
    <?= $this->Html->meta('icon') ?>

    <?= $this->Html->css([
        'accessibilite',
        'favoris',
        'footer',
        'form',
        'header',
        'index',
        'messagerie',
        'messaging-realtime',
        'page_link',
        'profil',
        'style'
    ]) ?>

    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
</head>
<body>
<header>
    <nav>
        <ul>
            <li class="nav-item">
                <div class="bar_rech">
                    <input type="search" id="searchInput" class="search-input" placeholder="Recherche..."
                           autocomplete="off">
                    <table class="search-results" id="results-table">
                        <tbody>
                        </tbody>
                    </table>
                    <div class="btn">
                        <i class="fas fa-search"></i>
                    </div>
                </div>
            </li>

            <li class="nav-item" id="link_access">
                <a href="/accessibilite">
                    <i class="material-icons">settings_accessibility</i>
                    <span>Paramètres</span>
                </a>
            </li>

            <li class="title" id="link_Titre">
                <a href="/index" <?= ($_SERVER['REQUEST_URI'] === '/index.php') ? 'class="active"' : '' ?>>Trips &
                    Roads</a>
            </li>

            <?php //if (isset($_SESSION['utilisateur'])): ?>

                <li class="nav-item" id="link_Chat">
                    <a href="/messagerie">
                        <i class="material-icons">chat_bubble</i>
                        <span>Messagerie</span>
                    </a>
                </li>

                <li class="nav-item" id="link_Amis">
                    <a href="/amis">
                        <i class="material-icons">group</i>
                        <span>Amis</span>
                    </a>
                </li>

                <li class="nav-item" id="link_Crea">
                    <a href="/add_r_t" <?= ($_SERVER['REQUEST_URI'] === '/creationRoadTrip.php') ? 'class="active"' : '' ?>>
                        <i class="material-icons">add_box</i>
                        <span>Créer un Road-Trip</span>
                    </a>
                </li>

            <?php //endif; ?>


            <li class="nav-item" id="link_PP">
                <?php //if (isset($_SESSION['utilisateur'])): ?>

                <span class="profil-box">

                             <?php
                             /*if (isset($_SESSION['utilisateur']['photo_profil']) && !empty($_SESSION['utilisateur']['photo_profil'])) {
                                 $photoProfil = htmlspecialchars($_SESSION['utilisateur']['photo_profil']);

                             } else {
                                 $photoProfil = "User.png";

                             }
                             $serverPathUploads = WEBROOT . "uploads/pp/" . $photoProfil;
                             if (!file_exists($serverPathUploads)) {
                                 $photoPath = __DIR__ . "/../img/$photoProfil";

                             } else {
                                 $photoPath = "/uploads/pp/$photoProfil";
                             }*/
                             ?>
                            <a href="/view"><img class="profil-photo" src="<?= "" ?>" alt="Photo de profil"> </a>
                    <span class="profil-nom">
                        <?= "Test" ?>
                        <?= "TEST" ?>
                        </span>
                    <li class="nav-item" id="link_Deco">
                    <a class="pp_logout" href="/logout">
                        <i class="material-icons">logout</i>
                        <span>Déconnexion</span>
                    </a>
                    </li>
                    </span>

                <?php //else: ?>

                <a href="/login">
                    <i class="material-icons">account_circle</i>
                    <span>Se connecter</span>
                </a>

                <?php //endif; ?>
            </li>

        </ul>


        <input type="checkbox" id="burger">
        <label for="burger" class="burger"><span></span></label>

        <ul class="ul_burger">

            <?php //if (isset($_SESSION['utilisateur'])): ?>

            <li><a href="/Roadtrip">Roads-Trips</a></li>
            <li><a href="/mesRoadTrips">Mes Roads-Trips</a></li>
            <li><a href="/favoris">Favoris</a></li>
            <li><a href="/historique">Historique</a></li>
            <li><a href="/view">Paramètres de compte</a></li>
            <li><a href="/page_link/faq">Aide / FAQ</a></li>
            <li><a href="/page_link/contact">À propos / Contact</a></li>
            <li><a href="/logout">Déconnexion</a></li>

            <?php //else: ?>

            <li><a href="../Roadtrip">Roads-Trips</a></li>
            <li><a href="/id">Se connecter</a></li>

            <?php //endif; ?>

        </ul>

    </nav>
</header>
<?php
$showMain = $this->fetch('showMain', true);
$mainClass = $this->fetch('mainClass', 'main-index');
?>

<?php if ($showMain): ?>
    <main class=<?= $mainClass ?>>
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
            <img src="../img/logoProjet.png" alt="Logo du site web">
        </div>

        <div class="social-media">
            <a href="https://www.instagram.com" class="social-icon" target="_blank">
                <i class="fab fa-instagram"></i>
            </a>
            <a href="https://www.facebook.com" class="social-icon" target="_blank">
                <i class="fab fa-facebook-f"></i>
            </a>
            <a href="https://www.x.com" class="social-icon" target="_blank">
                <i class="fa-brands fa-x-twitter"></i>
            </a>
        </div>

        <ul class="footer-links">
            <li><a href="../page_link/contact" class="un"> Contact </a></li>
            <li><a href="../page_link/cgu" class="deux">CGU</a></li>
            <li><a href="../page_link/politique" class="trois">Politique de confidentialité</a></li>
            <li><a href="../page_link/faq" class="quatre">FAQ</a></li>
            <li><a href="../Roadtrip" class="cinq">Road-Trip</a></li>
            <li><a href="../page_link/cookie" class="six">Gestion des cookies</a></li>
        </ul>
    </div>
</footer>
<?= $this->Html->script([
    'encryption',
    'index',
    'map',
    'messagerie',
    'profil',
    'recherche',
    'roadtrip',
    'vuRoadTrip',
]) ?>
<script src="https://cdn.jsdelivr.net/npm/leaflet@1.7.1/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
<script src="https://unpkg.com/leaflet-markercluster/dist/leaflet.markercluster.js"></script>
<?= $this->fetch('script') ?>
</body>
</html>
