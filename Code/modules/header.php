<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="/css/style.css">
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

            <li class="nav-item">
                <a href="/accessibilite.php">
                    <i class="material-icons">settings_accessibility</i>
                    <span>Paramètres</span>
                </a>
            </li>

            <li class="title">
                <a href="/index.php" <?= ($_SERVER['REQUEST_URI'] === '/index.php') ? 'class="active"' : '' ?>>
                    Trips & Roads
                </a>
            </li>

            <?php if (isset($_SESSION['utilisateur'])): ?>

                <li class="nav-item">
                    <a href="/messagerie.php">
                        <i class="material-icons">chat_bubble</i>
                        <span>Messagerie</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="/amis.php">
                        <i class="material-icons">group</i>
                        <span>Amis</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="/creationRoadTrip.php" <?= ($_SERVER['REQUEST_URI'] === '/creationRoadTrip.php') ? 'class="active"' : '' ?>>
                        <i class="material-icons">add_box</i>
                        <span>Créer un Road-Trip</span>
                    </a>
                </li>

            <?php endif; ?>

            
            <li class="nav-item">
                <?php if (isset($_SESSION['utilisateur'])): ?>

                    <span> 
                        <?= htmlspecialchars($_SESSION['utilisateur']['nom']) ?>
                        <?= htmlspecialchars($_SESSION['utilisateur']['prenom']) ?>
                        <img src="/uploads/pp/<?= htmlspecialchars($_SESSION['utilisateur']['photo_profil']) ?>" alt="Photo de profil">
                        <a href="/logout.php">Déconnexion</a>                        
                        <a href= "/profil.php"> Profil</a>
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

                <li><a href="../mesRoadTrips.php">Mes Roads-Trips</a></li>
                <li><a href="../historique.php">Historique</a></li>
                <li><a href="../favoris.php">Favoris</a></li>
                <li><a href="../compte.php">Paramètres de compte</a></li>
                <li><a href="../aide.php">Aide / FAQ</a></li>
                <li><a href="../contact.php">À propos / Contact</a></li>
                <li><a href="/logout.php">Déconnexion</a></li>

            <?php else: ?>

                <li><a href="/id.php">Se connecter</a></li>

            <?php endif; ?>

        </ul>

    </nav>
</header>
</html>
