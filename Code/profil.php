<?php
require_once __DIR__ . '/modules/init.php';


if (!isset($_SESSION['utilisateur'])) {
    header("Location: id.php");
    exit;
}


$user = $_SESSION['utilisateur'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/style.css"> 
    <link rel="stylesheet" href="css/form.css">
    <link rel="stylesheet" href="css/profil.css">

    <title>Profil</title>
</head>
<body>

<?php include_once __DIR__ . "/modules/header.php"; ?>

<main class="profil">

<div>
    <ul class="info_profil">
        <li><a href="mesRoadTrips.php">Mes Roads-Trips</a></li>
        <li><a href="profil.php">Paramètre de compte</a></li>
        <li><a href="/logout.php">Déconnexion</a></li>
    </ul>
</div>

<div class="form_profil_div">

<form id="profilForm" class="form_modif" action="formulaire/form_modif.php" method="post" enctype="multipart/form-data">

    <h2>Données personnelles</h2>

    <label for="pseudo">Pseudo</label>
    <input type="text" id="pseudo" name="pseudo" required
           value="<?= htmlspecialchars($user['pseudo']) ?>">

    <label for="name">Nom</label>
    <input type="text" id="name" name="name" required
           value="<?= htmlspecialchars($user['nom']) ?>">

    <label for="firstname">Prénom</label>
    <input type="text" id="firstname" name="firstname" required
           value="<?= htmlspecialchars($user['prenom']) ?>">

    <label for="email">Adresse email</label>
    <input type="email" id="email" name="email" required
           value="<?= htmlspecialchars($user['email']) ?>">

    <label for="password">Nouveau mot de passe (optionnel)</label>
    <input type="password" id="password" name="password">

    <label for="confirm_password">Confirmer le mot de passe</label>
    <input type="password" id="confirm_password" name="confirm_password">

    <label for="address">Adresse</label>
    <input type="text" id="address" name="address"
           value="<?= htmlspecialchars($user['adresse'] ?? "") ?>">

    <label for="postal">Code postal</label>
    <input type="text" id="postal" name="postal"
           value="<?= htmlspecialchars($user['postal'] ?? "") ?>">

    <label for="town">Ville</label>
    <input type="text" id="town" name="town"
           value="<?= htmlspecialchars($user['ville'] ?? "") ?>">

    <label for="phone">Votre numéro de téléphone</label>
    <input type="tel" id="phone" name="phone"
           value="<?= htmlspecialchars($user['tel'] ?? "") ?>">

    <label for="birthdate">Date de naissance</label>
    <input type="date" id="birthdate" name="birthdate"
           value="<?= htmlspecialchars($user['date_naissance'] ?? "") ?>">

    <label for="image">Nouvelle photo de profil</label>
    <input type="file" id="image" name="image" accept="image/*">


    <button type="submit">Enregistrer les modifications</button>

</form>

</div>

</main>

<?php include_once __DIR__ . "/modules/footer.php"; ?>

</body>
</html>
