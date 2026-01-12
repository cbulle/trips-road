<?php require_once '../include/init.php'; ?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes Favoris - Trips & Roads</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
<?php include_once __DIR__ . "/../modules/header.php"; ?>

<main class="main-index">
    <div class="index_container">

<form action="/../formulaire/traitement_oublie.php" method="POST">
    <h2>Réinitialisation du mot de passe</h2>
    <label for="email">Votre adresse email :</label>
    <input type="email" name="email" required>
    <button type="submit">Envoyer le lien</button>
</form>
</main>

<?php include_once __DIR__ . "/../modules/footer.php"; ?>
</body>
</html>
