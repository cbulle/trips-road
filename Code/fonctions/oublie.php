<?php require_once 'include/init.php'; ?>
<form action="formulaire/traitement_oublie.php" method="POST">
    <h2>Réinitialisation du mot de passe</h2>
    <label for="email">Votre adresse email :</label>
    <input type="email" name="email" required>
    <button type="submit">Envoyer le lien</button>
</form>