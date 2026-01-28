<?php
require_once __DIR__ . '/../bd/lec_bd.php';

if (empty($_SESSION['utilisateur']['id']) && isset($_COOKIE['remember_me'])) {
    
    $parts = explode(':', $_COOKIE['remember_me']);
    
    if (count($parts) === 2) {
        $selector = $parts[0];
        $validator = $parts[1];

        $stmt = $pdo->prepare("SELECT * FROM user_tokens WHERE selector = ? AND expires_at > NOW()");
        $stmt->execute([$selector]);
        $auth_token = $stmt->fetch();

        if ($auth_token) {
            if (hash_equals($auth_token['hashed_validator'], hash('sha256', $validator))) {
                $stmtUser = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
                $stmtUser->execute([$auth_token['user_id']]);
                $user = $stmtUser->fetch();

                if ($user) {
                    $_SESSION['utilisateur'] = $user;
                    
                }
            }
        }
    }
}
if (empty($_SESSION['admin']) && !empty($_COOKIE['remember_login']) && !empty($_COOKIE['remember_password'])) {
    $login_valide = "admin";
    $password_valide = "admin";

    if ($_COOKIE['remember_login'] === $login_valide && $_COOKIE['remember_password'] === $password_valide) {
        $_SESSION['admin'] = true;
    }
}
