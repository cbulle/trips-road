<?php
// 1. Démarrage de session (OBLIGATOIRE pour stocker l'utilisateur connecté)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Définition du chemin racine (Utile pour les images, css, etc.)
// __DIR__ est le dossier 'include'. On remonte d'un cran (..) vers 'Code' puis 'webroot'
if (!defined('WEBROOT')) {
    define('WEBROOT', realpath(__DIR__ . '/../webroot/') . '/');
}

// 3. Chargement de la base de données
require_once __DIR__ . '/../bd/lec_bd.php';

// 4. Logique "Se souvenir de moi" (Votre code existant)
if (empty($_SESSION['utilisateur']['id']) && isset($_COOKIE['remember_me'])) {
    $parts = explode(':', $_COOKIE['remember_me']);
    if (count($parts) === 2) {
        $selector = $parts[0];
        $validator = $parts[1];
        // $pdo est accessible ici car lec_bd.php est chargé juste au-dessus
        try {
            $stmt = $pdo->prepare("SELECT * FROM user_tokens WHERE selector = ? AND expires_at > NOW()");
            $stmt->execute([$selector]);
            $auth_token = $stmt->fetch();

<<<<<<< HEAD
            if ($auth_token && hash_equals($auth_token['hashed_validator'], hash('sha256', $validator))) {
=======
        $stmt = $pdo->prepare("SELECT * FROM user_tokens WHERE selector = ? AND expires_at > NOW()");
        $stmt->execute([$selector]);
        $auth_token = $stmt->fetch();

        if ($auth_token) {
            if (hash_equals($auth_token['hashed_validator'], hash('sha256', $validator))) {
>>>>>>> d225ce3979b3d21acd6703fbce843cc23436292f
                $stmtUser = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
                $stmtUser->execute([$auth_token['user_id']]);
                $user = $stmtUser->fetch();
                if ($user) {
                    $_SESSION['utilisateur'] = $user;
                }
            }
        } catch (Exception $e) {}
    }
}

// 5. Logique Admin
if (empty($_SESSION['admin']) && !empty($_COOKIE['remember_login']) && !empty($_COOKIE['remember_password'])) {
    if ($_COOKIE['remember_login'] === "admin" && $_COOKIE['remember_password'] === "admin") {
        $_SESSION['admin'] = true;
    }
}
?>