<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['admin']) && !empty($_COOKIE['remember_login']) && !empty($_COOKIE['remember_password'])) {
    $login_valide = "admin";
    $password_valide = "admin";

    if ($_COOKIE['remember_login'] === $login_valide && $_COOKIE['remember_password'] === $password_valide) {
        $_SESSION['admin'] = true;
    }
}
