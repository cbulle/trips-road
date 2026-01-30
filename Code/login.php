<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Road Trip Planner</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href= "css/form.css">
    <script src="https://accounts.google.com/gsi/client" async defer></script>
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
                        <button class="toggle-btn active" id="btnLogin" onclick="window.location.href='login'">Se connecter</button>
                        <button class="toggle-btn" id="btnRegister" onclick="window.location.href='register'">S'inscrire</button>
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
                        <div id="g_id_onload"
                            data-client_id="995499019090-anmh1d4m4obifri1fs9egue2p5417f8h.apps.googleusercontent.com"
                            data-context="signin"
                            data-ux_mode="popup"
                            data-callback="handleCredentialResponse"
                            data-auto_prompt="false">
                        </div>
                        
                        <div class="g_id_signin"
                            data-type="standard"
                            data-shape="rectangular"
                            data-theme="outline"
                            data-text="signin_with"
                            data-size="large"
                            data-logo_alignment="left">
                        </div>
                    
                    </form>
                </div>
            </div>
        </div>
    </main>
</container>
    <?php
    include_once __DIR__ . "/modules/footer.php"
    ?>
    <script src="webroot/js/profil.js"></script>
    <script src="webroot/js/login.js"></script>
</body>
</html>