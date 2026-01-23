<?php
require_once __DIR__ . '/include/init.php';


if (!isset($_SESSION['utilisateur'])) {
    header("Location: /login");
    exit;
}

$user = $_SESSION['utilisateur'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/style.css"> 
    <link rel="stylesheet" href="css/profil.css">

    <title>Profil</title>
</head>
<body>

<?php include_once __DIR__ . "/modules/header.php"; ?>
<main class="profil-container">

    <aside class="profil-sidebar">
        <div class="user-brief">
            <?php
            $photoProfil = !empty($_SESSION['utilisateur']['photo_profil']) ? htmlspecialchars($_SESSION['utilisateur']['photo_profil']) : "User.png";
            $photoUrl = (file_exists(WEBROOT . "uploads/pp/".$photoProfil)) ? "/uploads/pp/$photoProfil" : "img/User.png";
            ?>
            <div class="avatar-circle small" style="background-image: url('<?= $photoUrl ?>');"></div>
            <h3><?= htmlspecialchars($user['prenom']) . ' ' . htmlspecialchars($user['nom']) ?></h3>
        </div>
        
        <nav class="profil-nav">
            <ul>
                <li><a href="mesRoadTrips.php">Mes Road-Trips</a></li>
                <li><a href="profil.php" class="active">Paramètres du compte</a></li>
                <li><a href="accessibilite.php" class="access">Accessibilité</a></li>
                <li><a href="/logout.php" class="logout">Déconnexion</a></li>
            </ul>
        </nav>
    </aside>

    <section class="profil-content">
        <div class="card-header">
            <h1>Mon Profil</h1>
            <p>Gérez vos informations personnelles et vos préférences de sécurité.</p>
        </div>

        <form id="profilForm" class="form_modif" action="/profil" method="POST" enctype="multipart/form-data">
            
            <div class="form-section photo-section">
                <div class="avatar-wrapper">
                    <div class="avatar-circle large" style="background-image: url('<?= $photoUrl ?>');"></div>
                    <div class="avatar-upload">
                        <label for="image" class="btn-upload">Changer la photo</label>
                        <input type="file" id="image" name="image" accept="image/*">
                    </div>
                </div>
            </div>

            <hr class="divider">

            <div class="form-section">
                <h2>Identité</h2>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="pseudo">Pseudo</label>
                        <input type="text" id="pseudo" name="pseudo" value="<?= htmlspecialchars($user['pseudo']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                         </div>

                    <div class="form-group">
                        <label for="firstname">Prénom</label>
                        <input type="text" id="firstname" name="firstname" value="<?= htmlspecialchars($user['prenom']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="name">Nom</label>
                        <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['nom']) ?>" required>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h2>Coordonnées</h2>
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label for="email">Adresse email</label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">Téléphone</label>
                        <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($user['tel'] ?? "") ?>">
                    </div>

                    <div class="form-group">
                        <label for="birthdate">Date de naissance</label>
                        <input type="date" id="birthdate" name="birthdate" value="<?= htmlspecialchars($user['date_naissance'] ?? "") ?>">
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h2>Adresse</h2>
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label for="address">Rue & Numéro</label>
                        <input type="text" id="address" name="address" value="<?= htmlspecialchars($user['adresse'] ?? "") ?>">
                    </div>

                    <div class="form-group">
                        <label for="postal">Code postal</label>
                        <input type="text" id="postal" name="postal" value="<?= htmlspecialchars($user['postal'] ?? "") ?>">
                    </div>

                    <div class="form-group">
                        <label for="town">Ville</label>
                        <input type="text" id="town" name="town" value="<?= htmlspecialchars($user['ville'] ?? "") ?>">
                    </div>
                </div>
            </div>

            <hr class="divider">

            <div class="form-section">
                <h2>Sécurité</h2>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="password">Nouveau mot de passe</label>
                        <input type="password" id="password" name="password" placeholder="Laisser vide si inchangé">
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirmer le mot de passe</label>
                        <input type="password" id="confirm_password" name="confirm_password">
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-save">Enregistrer les modifications</button>
            </div>

        </form>
    </section>
</main>

<?php include_once __DIR__ . "/modules/footer.php"; ?>

</body>
</html>
