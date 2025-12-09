<?php
include_once __DIR__ .'/../include/init.php';
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

// On récupère : titre, visibilite et id_utilisateur
$sql = 'SELECT id, titre, visibilite, id_utilisateur FROM roadtrip';
$stmt = $pdo->query($sql);
$resultats = $stmt->fetchAll(PDO::FETCH_ASSOC);

// On renvoie aussi l'ID de l'utilisateur connecté
$response = [
    "userId" => $_SESSION['id'] ?? null,
    "roadtrips" => $resultats
];
