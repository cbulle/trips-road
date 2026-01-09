<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_me'])) {
    
    $parts = explode(':', $_COOKIE['remember_me']);
    
    if (count($parts) === 2) {
        $selector = $parts[0];
        $validator = $parts[1];

        $stmt = $pdo->prepare("SELECT * FROM user_tokens WHERE selector = ? AND expires_at > NOW()");
        $stmt->execute([$selector]);
        $auth_token = $stmt->fetch();

        if ($auth_token) {
            if (hash_equals($auth_token['hashed_validator'], hash('sha256', $validator))) {
                
                $_SESSION['user_id'] = $auth_token['user_id'];
                
               
            }
        }
    }
}


