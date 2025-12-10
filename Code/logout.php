<?php
require_once __DIR__ . '/modules/init.php' ;
session_unset();   
session_destroy(); 

//setcookie('remember_login', '', time() - 3600, '/');
//setcookie('remember_password', '', time() - 3600, '/');

header("Location: index.php");
exit;
