<?php
require_once __DIR__ . '/modules/init.php';

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/style.css"> 
    <title>Profil</title>
</head>
<body>
 <?php     
include_once __DIR__ . "/modules/header.php"
?>
<main class="profil">

<div>
    <ul class="info_profil">
          <li><a href="" >Mes Roads-Trips</a> </li>
          <li><a href="" >Paramètre de compte</a></li>
          <li><a href="" >Déconnexion</a></li>
        </ul>
</div>


<div class="form_profil_div">
<form id="registerForm" class="form_modif" action = "formulaire/form_modif.php" method = "post">
    <h2 id="login-title">Données personelles </h2>
        <label for="name">Nom</label>
        <input type="text" id="name" name="name" required>

        <label for="firstname">Prénom</label>
        <input type="text" id="firstname" name="firstname" required>

        <label for="email">Adresse email</label>
        <input type="email" id="email" name="email" required>

        <label for="password">Mot de passe</label>
        <input type="password" id="password" name="password" required>

        <label for= "confirm_password">Confirmation du mot de passe</label>
        <input type= "confirm_password" id= "confirm_password" name="confirm_password" required>

        <label for="address">Adresse</label>
        <input type="text" id="address" name="address">

        <label for="postal">Code postal</label>
        <input type="text" id="postal" name="postal">

        <label for="town">Ville</label>
        <input type="text" id="town" name="town">

        <label for="phone">Votre numéro de téléphone</label>
        <input type="tel"  id="phone"  name="phone" required />

        <label for="birthdate">Date de naissance</label>
        <input type="date" id="birthdate" name="birthdate" min="1900-01-01"  />

        <button type="submit">Enregistrer les modifications </button>
<form>
</div>

</main>
<?php     
include_once __DIR__ . "/modules/footer.php"
?>
</body>
</html>