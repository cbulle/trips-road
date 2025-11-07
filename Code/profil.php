<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Road Trip Planner</title>
    <link rel="stylesheet" href="css/style.css"> 
</head>
<body>
<?php     
include_once __DIR__ . "/modules/header.php"
?>
<h1>Profil </h1>
<main>


<form id="register-form" class="active" action = "formulaire/form_register.php" method = "post">
    <h2 id="login-title">Inscription </h2>
    <a id="show-register">Inscris-toi </a>
        <label for="name">Nom</label>
        <input type="text" id="name" name="name" required>

        <label for="firstname">Prénom</label>
        <input type="text" id="firstname" name="firstname" required>

        <label for="email">Adresse email</label>
        <input type="email" id="email" name="email" required>

        <label for="password">Mot de passe</label>
        <input type="password" id="password" name="password" required>

        <label for= "confirm_password">Confirmation du mot de passe</label>
        <input type= "password" id= "password" name="password" required>

        <label for="address">Adresse</label>
        <input type="text" id="address" name="address">

        <label for="postal">Code postal</label>
        <input type="text" id="postal" name="postal">

        <label for="town">Ville</label>
        <input type="text" id="town" name="town">

        <label for="phone">Votre numéro de téléphone :<br /></label>
        <input type="tel"  id="phone"  name="phone" required />

        <label for="birthdate">Date de naissance:</label>
        <input type="date" id="birthdate" name="birthdate" min="1900-01-01"  />

        <button type="submit">S'inscrire</button>
      </form>


<form id="login-form" action = "formulaire/form_connect.php" method = "post">
    <h2 id="register-title">Connexion</h2>
    <a id="show-login">Connecte-toi ici</a>
        <label for="email">Adresse email</label>
        <input type="email" id="email" name="email" required>

        <label for="password">Mot de passe</label>
        <input type="password" id="password" name="password" required>

        <button type="submit">Se connecter</button>
</form>

</main>
    
<?php     
include_once __DIR__ . "/modules/footer.php"
?>
<script src = "js/map.js"></script>
</body>
</html>
