<?php
<<<<<<< HEAD
// On supprime l'include vers init.php pour casser la boucle infinie
=======
>>>>>>> d225ce3979b3d21acd6703fbce843cc23436292f
$dsn = 'mysql:dbname=p2301500;host=iutbg-lamp.univ-lyon1.fr;charset=utf8mb4';
$user = 'p2301500';
$password = '12301500';

try {
    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    try {
        $pdo->exec("SET GLOBAL max_allowed_packet=67108864");
    } catch (Exception $e) {}

} catch (PDOException $e) {
    echo 'Connexion échouée : ' . $e->getMessage();
    die();
}