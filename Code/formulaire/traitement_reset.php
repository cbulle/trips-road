<?php
require_once __DIR__ . '/../include/init.php';
include_once __DIR__ . '/../bd/lec_bd.php';

/** @var PDO $pdo */

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = $_POST['token'];
    $password = $_POST['password'];
    $token_hash = hash('sha256', $token);

    // Re-vérification
    $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE reset_token_hash = ? AND reset_expires_at > NOW()");
    $stmt->execute([$token_hash]);
    $user = $stmt->fetch();

    if ($user) {
        // Hachage du nouveau mot de passe
        $new_hash = password_hash($password, PASSWORD_DEFAULT);

        // Mise à jour + Suppression du token (pour qu'il ne serve qu'une fois)
        $update = $pdo->prepare("UPDATE utilisateurs SET mot_de_passe = ?, reset_token_hash = NULL, reset_expires_at = NULL WHERE id = ?");
        $update->execute([$new_hash, $user['id']]);

        echo "Mot de passe modifié avec succès. <a href='../page_link/connexion.php'>Connectez-vous</a>.";
    } else {
        echo "Erreur : Lien invalide.";
    }
}