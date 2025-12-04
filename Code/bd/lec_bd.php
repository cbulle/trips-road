<?php

$dsn = 'mysql:dbname=p2301500;host=iutbg-lamp.univ-lyon1.fr';
$user = 'p2301500';
$password = '12301500';
try {
$pdo = new PDO($dsn, $user, $password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
echo 'Connexion échouée : ' . $e->getMessage();
die();
}
;

$sql = 'SELECT  titre FROM roadtrip WHERE visibilite="public"';
$stmt = $pdo->query($sql);
$resultats = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($resultats);