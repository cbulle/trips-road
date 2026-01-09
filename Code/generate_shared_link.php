<?php
require_once __DIR__ . '/include/init.php';
include_once __DIR__ . '/bd/lec_bd.php';

if (!isset($_SESSION['utilisateur']['id'])) {
    header('Location: /id.php');
    exit;
}

$id_utilisateur = $_SESSION['utilisateur']['id'];
$id_roadtrip = $_GET['id'] ?? null;

if (!$id_roadtrip) {
    header('Location: /mesRoadTrips.php');
    exit;
}

$stmt = $pdo->prepare("SELECT id FROM roadtrip WHERE id = :id AND id_utilisateur = :user_id");
$stmt->execute(['id' => $id_roadtrip, 'user_id' => $id_utilisateur]);
if (!$stmt->fetch()) {
    header('Location: /mesRoadTrips.php');
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT token FROM roadtrip_partages WHERE id_roadtrip = :id");
    $stmt->execute(['id' => $id_roadtrip]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        $token = $existing['token'];
    } else {
        $token = bin2hex(random_bytes(32));
        
        $stmt = $pdo->prepare("INSERT INTO roadtrip_partages (id_roadtrip, token) VALUES (:id, :token)");
        $stmt->execute(['id' => $id_roadtrip, 'token' => $token]);
    }
    
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $shareUrl = $protocol . '://' . $host . '/shared.php?t=' . $token;
    
    $_SESSION['share_url'] = $shareUrl;
    
} catch (PDOException $e) {
    error_log("Erreur génération lien: " . $e->getMessage());
}

header('Location: /mesRoadTrips.php?show_share=' . $id_roadtrip);
exit;