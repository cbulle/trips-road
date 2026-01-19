<?php
require_once __DIR__ . '/include/init.php';
include_once __DIR__ . '/bd/lec_bd.php';

/** @var PDO $pdo */

if (isset($_COOKIE['remember_me'])) {
    $parts = explode(':', $_COOKIE['remember_me']);
    if(count($parts) === 2) {
        $selector = $parts[0];
        $stmt = $pdo->prepare("DELETE FROM user_tokens WHERE selector = ?");
        $stmt->execute([$selector]);
    }
    setcookie("remember_me", "", time() - 3600, "/", "", true, true);
}

session_destroy();
header("Location: index.php");
exit;

