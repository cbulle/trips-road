<?php
require_once __DIR__ . '/modules/init.php';
require_once __DIR__ . '/bd/lec_bd.php' ; 

$login_valide = "admin";
$password_valide = "admin";

if ($email === $login_valide && $password === $password_valide) {
    echo $email;
}else {
    echo 'faiil' ; 
}