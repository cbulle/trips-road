<?php
require_once __DIR__ . '/../include/init.php';
include_once __DIR__ . '/../bd/lec_bd.php';

if (!isset($_SESSION['utilisateur']['id'])) {
    header('Location: /id.php');
    exit;
}

$id_utilisateur = $_SESSION['utilisateur']['id'];
$id_roadtrip = $_GET['id'] ?? null;
$action = $_GET['action'] ?? 'toggle';
$redirect = $_GET['redirect'] ?? 'Roadtrip.php';

if (!$id_roadtrip) {
    header('Location: /' . $redirect);
    exit;
}

$stmt = $pdo->prepare("SELECT id FROM roadtrip WHERE id = :id AND visibilite = 'public'");
$stmt->execute(['id' => $id_roadtrip]);
if (!$stmt->fetch()) {
    header('Location: /' . $redirect);
    exit;
}

try {
    if ($action === 'remove') {
        $stmt = $pdo->prepare("DELETE FROM favoris WHERE id_utilisateur = :user_id AND id_roadtrip = :rt_id");
        $stmt->execute(['user_id' => $id_utilisateur, 'rt_id' => $id_roadtrip]);
        $_SESSION['message'] = "Road trip retiré de vos favoris.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM favoris WHERE id_utilisateur = :user_id AND id_roadtrip = :rt_id");
        $stmt->execute(['user_id' => $id_utilisateur, 'rt_id' => $id_roadtrip]);
        
        if ($stmt->fetch()) {
            $stmt = $pdo->prepare("DELETE FROM favoris WHERE id_utilisateur = :user_id AND id_roadtrip = :rt_id");
            $stmt->execute(['user_id' => $id_utilisateur, 'rt_id' => $id_roadtrip]);
            $_SESSION['message'] = "Road trip retiré de vos favoris.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO favoris (id_utilisateur, id_roadtrip) VALUES (:user_id, :rt_id)");
            $stmt->execute(['user_id' => $id_utilisateur, 'rt_id' => $id_roadtrip]);
            $_SESSION['message'] = "Road trip ajouté à vos favoris !";
        }
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur lors de l'opération.";
}

header('Location: /' . 'favoris.php');
exit;