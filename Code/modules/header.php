<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/header.css">

    <script src="https://kit.fontawesome.com/d76759a8b0.js" crossorigin="anonymous"></script>
</head>


<header>
    <nav>
        <ul>
            <li class="nav-item">
                <div class="bar_rech">
                    <input type="search" id="searchInput" class="search-input" placeholder="Recherche..." autocomplete="off">
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
                <a href="/accessibilite.php">
                    <i class="material-icons">settings_accessibility</i>
                    <span>Paramètres</span>
                </a>
            </li>

            <li class="title" id="link_Titre">
                <a href="/index.php" <?= ($_SERVER['REQUEST_URI'] === '/index.php') ? 'class="active"' : '' ?>>Trips & Roads</a>
            </li>

            <?php if (isset($_SESSION['utilisateur'])): ?>

                <li class="nav-item" id="link_Chat">
                    <a href="/messagerie.php">
                        <i class="material-icons">chat_bubble</i>
                        <span>Messagerie</span>
                    </a>
                </li>

                <li class="nav-item" id="link_Amis">
                    <a href="/amis.php">
                        <i class="material-icons">group</i>
                        <span>Amis</span>
                    </a>
                </li>

                <li class="nav-item" id="link_Crea">
                    <a href="/creationRoadTrip.php" <?= ($_SERVER['REQUEST_URI'] === '/creationRoadTrip.php') ? 'class="active"' : '' ?>>
                        <i class="material-icons">add_box</i>
                        <span>Créer un Road-Trip</span>
                    </a>
                </li>

            <?php endif; ?>

            
            <li class="nav-item" id="link_PP">
                <?php if (isset($_SESSION['utilisateur'])): ?>

                    <span class="profil-box"> 
                         
                             <?php
                            if (isset($_SESSION['utilisateur']['photo_profil']) && !empty($_SESSION['utilisateur']['photo_profil'])) {
                                $photoProfil = htmlspecialchars($_SESSION['utilisateur']['photo_profil']);
                            
                            } else {
                                $photoProfil = "User.png"; 
                                
                            }
                            $serverPathUploads = __DIR__ . "/../uploads/pp/$photoProfil";                       
                            if (!file_exists($serverPathUploads)) {
                                $photoPath = __DIR__ . "/../img/$photoProfil"; 
                                
                            }
                             else {
                                $photoPath = "/uploads/pp/$photoProfil";
                            }
                            ?>
                            <a  href="/profil.php"><img class="profil-photo" src="<?= $photoPath ?>" alt="Photo de profil"> </a>
                    <span class="profil-nom">
                        <?= htmlspecialchars($_SESSION['utilisateur']['nom']) ?>
                        <?= htmlspecialchars($_SESSION['utilisateur']['prenom']) ?>
                        </span>
                    <li class = "nav-item" id="link_Deco">                     
                    <a class = "pp_logout" href="/logout.php">
                        <i class="material-icons">logout</i>
                        <span>Déconnexion</span>
                    </a>                        
                    </li>
                    </span>

                <?php else: ?>

                    <a href="/id.php" <?= ($_SERVER['REQUEST_URI'] === '/id.php') ? 'class="active"' : '' ?>>
                        <i class="material-icons">account_circle</i>
                        <span>Se connecter</span>
                    </a>

                <?php endif; ?>
            </li>

        </ul>

       
        <input type="checkbox" id="burger">
        <label for="burger" class="burger"><span></span></label>

        <ul class="ul_burger">

            <?php if (isset($_SESSION['utilisateur'])): ?>

                <li><a href="../Roadtrip.php">Roads-Trips</a></li>
                <li><a href="../mesRoadTrips.php">Mes Roads-Trips</a></li>
                <li><a href="../favoris.php">Favoris</a></li>
                <li><a href="../historique.php">Historique</a></li>
                <li><a href="../profil.php">Paramètres de compte</a></li>
                <li><a href="../page_link/faq.php">Aide / FAQ</a></li>
                <li><a href="../page_link/contact.php">À propos / Contact</a></li>
                <li><a href="/logout.php">Déconnexion</a></li>

            <?php else: ?>

                <li><a href="../Roadtrip.php">Roads-Trips</a></li>
                <li><a href="/id.php">Se connecter</a></li>

            <?php endif; ?>

        </ul>

    </nav>
</header>
</html>
