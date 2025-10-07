<?php
require_once __DIR__ . '/modules/init.php' ;

$login_valid = "admin" ;
$password_valid = "admin" ;

$login = $_POST['login'] ?? '';
$password = $_POST ['password']??'';

if($login === $login_valid && $password === $password_valid){
    $_SESSION['admin'] = true ;
    header("Location: creerRoad.php");
    exit;
}else{
    echo "<p>Id incorects</p>";
}
?>