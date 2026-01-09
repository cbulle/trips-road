<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. D'ABORD : Connexion à la BDD
require_once __DIR__ . '/../bd/lec_bd.php'; 

// 2. ENSUITE : Logique "Se souvenir de moi"
// Si l'utilisateur n'est pas connecté MAIS a le cookie
if (empty($_SESSION['utilisateur']['id']) && isset($_COOKIE['remember_me'])) {
    
    $parts = explode(':', $_COOKIE['remember_me']);
    
    if (count($parts) === 2) {
        $selector = $parts[0];
        $validator = $parts[1];

        // On cherche le token dans la table
        // Note : Assurez-vous d'avoir créé la table `user_tokens` comme indiqué précédemment
        $stmt = $pdo->prepare("SELECT * FROM user_tokens WHERE selector = ? AND expires_at > NOW()");
        $stmt->execute([$selector]);
        $auth_token = $stmt->fetch();

        if ($auth_token) {
            if (hash_equals($auth_token['hashed_validator'], hash('sha256', $validator))) {
                // Token valide ! On récupère les infos de l'utilisateur
                $stmtUser = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
                $stmtUser->execute([$auth_token['user_id']]);
                $user = $stmtUser->fetch();

                if ($user) {
                    // On connecte l'utilisateur
                    $_SESSION['utilisateur'] = $user;
                    
                    // (Optionnel) Ici, vous pouvez régénérer le token pour plus de sécurité
                }
            }
        }
    }
}

// 3. ENFIN : Votre logique Admin existante
if (empty($_SESSION['admin']) && !empty($_COOKIE['remember_login']) && !empty($_COOKIE['remember_password'])) {
    $login_valide = "admin";
    $password_valide = "admin";

    if ($_COOKIE['remember_login'] === $login_valide && $_COOKIE['remember_password'] === $password_valide) {
        $_SESSION['admin'] = true;
    }
}
?>