<?php
include_once __DIR__ . '/../bd/lec_bd.php'; 

if (!isset($_SESSION['utilisateur'])) {
    header("Location: /login.php");
    exit;
}

$utilisateur_id = $_SESSION['utilisateur']['id']; 

$recherche = '';
if (isset($_GET['recherche'])) {
    $recherche = $_GET['recherche'];
    $stmt = $pdo->prepare("
        SELECT id, nom, prenom
        FROM utilisateurs
        WHERE nom LIKE ? OR prenom LIKE ?
    ");
    $stmt->execute(['%' . $recherche . '%', '%' . $recherche . '%']);
    $utilisateurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $utilisateurs = [];
}

$stmt = $pdo->prepare("
    SELECT u.id, u.nom, u.prenom
    FROM utilisateurs u
    INNER JOIN amis a ON u.id = a.id_ami
    WHERE a.id_utilisateur = ? AND a.statut != 'supprime'
    ORDER BY u.nom ASC
");
$stmt->execute([$utilisateur_id]);
$amis = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("
    SELECT u.id, u.nom, u.prenom
    FROM utilisateurs u
    INNER JOIN amis a ON u.id = a.id_utilisateur
    WHERE a.id_ami = ? AND a.statut = 'en_attente'
");
$stmt->execute([$utilisateur_id]);
$demandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_GET['add'])) {
    $id_ami = (int) $_GET['add'];

    $stmt = $pdo->prepare("
        SELECT statut FROM amis 
        WHERE (id_utilisateur=? AND id_ami=?) OR (id_utilisateur=? AND id_ami=?)
    ");
    $stmt->execute([$utilisateur_id, $id_ami, $id_ami, $utilisateur_id]);
    $statut = $stmt->fetchColumn();

    if (!$statut) {
        $stmt = $pdo->prepare("INSERT INTO amis (id_utilisateur, id_ami, statut) VALUES (?, ?, 'en_attente')");
        $stmt->execute([$utilisateur_id, $id_ami]);
        $message = "Demande d'ami envoyée.";
    } elseif ($statut === 'accepte') {
        $message = "Vous êtes déjà amis.";
    } elseif ($statut === 'en_attente') {
        $message = "Demande d'ami déjà envoyée.";
    }
}

if (isset($_GET['delete'])) {
    $id_ami = (int) $_GET['delete'];

    $stmt = $pdo->prepare("
        DELETE FROM amis 
        WHERE (id_utilisateur=? AND id_ami=?) OR (id_utilisateur=? AND id_ami=?)
    ");
    $stmt->execute([$utilisateur_id, $id_ami, $id_ami, $utilisateur_id]);

    $message = "Ami supprimé avec succès.";
}

?>