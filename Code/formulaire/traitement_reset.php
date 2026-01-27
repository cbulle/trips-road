<?php
/** @var PDO $pdo */

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = $_POST['token'];
    $password = $_POST['password'];
    $token_hash = hash('sha256', $token);

    $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE reset_token_hash = ? AND reset_expires_at > NOW()");
    $stmt->execute([$token_hash]);
    $user = $stmt->fetch();

    if ($user) {
        $new_hash = password_hash($password, PASSWORD_DEFAULT);

        $update = $pdo->prepare("UPDATE utilisateurs SET mot_de_passe = ?, reset_token_hash = NULL, reset_expires_at = NULL WHERE id = ?");
        $update->execute([$new_hash, $user['id']]);

        echo "Mot de passe modifié avec succès. <a href='../login.php'>Connectez-vous</a>.";
    } else {
        echo "Erreur : Lien invalide.";
    }
}