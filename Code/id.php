<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Road Trip Planner</title>
    <link rel="stylesheet" href="css/style.css"> 
    <link rel="stylesheet" href="css/form.css">
</head>
<body>
<container class="container">    
<?php     
include_once __DIR__ . "/modules/header.php"
?>

<main>
<div class="main">
<h1>Identification </h1>
    <div class="formulaire">
        <div class="in_form">
            <div class="toggle-box">
                    <button class="toggle-btn" id="btnLogin" onclick="showLogin()">Se connecter</button>
                    <button class="toggle-btn" id="btnRegister" onclick="showRegister()">S'inscrire</button>
            </div>
<form id="registerForm" class="form-box" action = "formulaire/form_register.php" method = "post">
    <h2 id="login-title">Inscription </h2>
    
        <label for="pseudo">Pseudo</label>
        <input type="text" id="pseudo" name="pseudo" required>

        <label for="name">Nom</label>
        <input type="text" id="name" name="name" required>

                    <label for="firstname">Prénom</label>
                    <input type="text" id="firstname" name="firstname" required>

                    <label for="email">Adresse email</label>
                    <input type="email" id="email" name="email" required>

                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required>

                    <label for="confirm_password">Confirmation du mot de passe *</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>

                    <label for="address">Adresse *</label>
                    <input type="text" id="address" name="address" required>

                    <label for="postal">Code postal *</label>
                    <input type="text" id="postal" name="postal" required>

                    <label for="town">Ville *</label>
                    <input type="text" id="town" name="town" required>

                    <label for="phone">Votre numéro de téléphone *</label>
                    <input type="tel" id="phone" name="phone" required>

                    <label for="birthdate">Date de naissance *</label>
                    <input type="date" id="birthdate" name="birthdate" min="1900-01-01" required>

                    <label for="image">Photo de profil</label>
                    <input type="file" id="image" name="image" accept="image/*">

                    <button type="submit">S'inscrire</button>

                    <p>
                    Les informations avec astérisque sont nécessaires à la création et à la gestion de votre compte 
                    qui vous permet notamment d'être informés des nouveautés, bénéficiez d’offres
                    personnalisées.<br>Pour garder le contrôle sur la façon dont nous utilisons vos données, vous pouvez à 
                    tout moment revenir sur vos choix sur la page modifier mes préférences dans votre espace client.
                    </p>
            </form>


            <form id="loginForm" class="form-box" action = "formulaire/form_connect.php" method = "post">
                <h2 id="register-title">Connexion</h2>
                
                <label for="email">Adresse email</label>
                <input type="email" id="email" name="email" required>
                
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required>
                <input type="checkbox" name= "remember" id="remember">
                
                <label for="remember"> Se souvenir de moi</label>
                <button type="submit">Se connecter</button>
            </form>
        </div>
    </div>
</div>
</main>
<?php     
include_once __DIR__ . "/modules/footer.php"
?>
<script src = "js/recherche.js"></script>
</body>
</html>
