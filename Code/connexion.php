<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <link rel="stylesheet" href= "/css/style.css">
    

</head>



<body>
<main>

<form action ="formulaire/log.php" method="post">
    <label for="login"> Email ou identifiant :</label>
    <input type= "text" name="login" id="login" required>

    <label for="password"> Mot de passe :</label>
    <input type= "password" name="password" id="password" required>

    <button type="submit"> Se connecter</button>
</form>

</main>    
</body>
</html>
