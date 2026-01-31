<?php
$this->assign('mainClass', '');
?>

<div class="main">
    <h1>Identification </h1>
    <div class="formulaire">
        <div class="in_form">
            <div class="toggle-box">
                <button class="toggle-btn active" id="btnLogin" onclick="window.location.href='/login'">Se connecter</button>
                <button class="toggle-btn" id="btnRegister" onclick="window.location.href='/add'">S'inscrire</button>
            </div>

            <form id="loginForm" class="form-box" action="/login" method="post">
                <h2 id="register-title">Connexion</h2>

                <label for="email">Adresse email</label>
                <input type="email" id="email" name="email" required>

                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required>

                <label> <input type="checkbox" name="remember_me" value="1"> Se souvenir de moi</label>
                <div class="forgot-password">
                    <a href="fonctions/oublie.php">Mot de passe oublié ?</a>
                </div>
                <button type="submit">Se connecter</button>
            </form>
        </div>
    </div>
</div>
