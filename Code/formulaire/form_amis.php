<?php
/** @var PDO $pdo */

if (!isset($_SESSION['utilisateur']['id'])) {
    header('Location: /login');
    exit;
}

$utilisateur_id = $_SESSION['utilisateur']['id'];
$message = '';
$recherche = $_GET['recherche'] ?? '';

if (isset($_GET['add'])) {
    $ami_id = (int)$_GET['add'];
    
    if ($ami_id == $utilisateur_id) {
        $message = "Vous ne pouvez pas vous ajouter vous-même.";
    } else {
        $stmt = $pdo->prepare("
            SELECT * FROM amis 
            WHERE (id_utilisateur = ? AND id_ami = ?) OR (id_utilisateur = ? AND id_ami = ?)
        ");
        $stmt->execute([$utilisateur_id, $ami_id, $ami_id, $utilisateur_id]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            $message = "Une relation existe déjà avec cet utilisateur.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO amis (id_utilisateur, id_ami, statut) VALUES (?, ?, 'en_attente')");
            $stmt->execute([$utilisateur_id, $ami_id]);
            $message = "Demande d'ami envoyée !";
        }
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'accepter' && isset($_GET['id'])) {
    $demandeur_id = (int)$_GET['id'];
    
    $stmt = $pdo->prepare("
        UPDATE amis SET statut = 'accepte' 
        WHERE id_utilisateur = ? AND id_ami = ? AND statut = 'en_attente'
    ");
    $stmt->execute([$demandeur_id, $utilisateur_id]);
    $message = "Demande acceptée !";
}

if (isset($_GET['action']) && $_GET['action'] === 'refuser' && isset($_GET['id'])) {
    $demandeur_id = (int)$_GET['id'];
    
    $stmt = $pdo->prepare("DELETE FROM amis WHERE id_utilisateur = ? AND id_ami = ?");
    $stmt->execute([$demandeur_id, $utilisateur_id]);
    $message = "Demande refusée.";
}

if (isset($_GET['delete'])) {
    $ami_id = (int)$_GET['delete'];
    
    $stmt = $pdo->prepare("
        DELETE FROM amis 
        WHERE (id_utilisateur = ? AND id_ami = ?) OR (id_utilisateur = ? AND id_ami = ?)
    ");
    $stmt->execute([$utilisateur_id, $ami_id, $ami_id, $utilisateur_id]);
    $message = "Ami supprimé. Vous pouvez le réajouter si vous le souhaitez.";
}

$utilisateurs = [];
if ($recherche) {
    $stmt = $pdo->prepare("
        SELECT id, nom, prenom, photo_profil 
        FROM utilisateurs 
        WHERE (nom LIKE ? OR prenom LIKE ?) AND id != ?
        LIMIT 20
    ");
    $search = "%$recherche%";
    $stmt->execute([$search, $search, $utilisateur_id]);
    $utilisateurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$stmt = $pdo->prepare("
    SELECT u.id, u.nom, u.prenom, u.photo_profil
    FROM amis a
    INNER JOIN utilisateurs u ON (
        CASE 
            WHEN a.id_utilisateur = ? THEN u.id = a.id_ami
            WHEN a.id_ami = ? THEN u.id = a.id_utilisateur
        END
    )
    WHERE (a.id_utilisateur = ? OR a.id_ami = ?) AND a.statut = 'accepte'
");
$stmt->execute([$utilisateur_id, $utilisateur_id, $utilisateur_id, $utilisateur_id]);
$amis = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("
    SELECT u.id, u.nom, u.prenom, u.photo_profil
    FROM amis a
    INNER JOIN utilisateurs u ON u.id = a.id_utilisateur
    WHERE a.id_ami = ? AND a.statut = 'en_attente'
");
$stmt->execute([$utilisateur_id]);
$demandes = $stmt->fetchAll(PDO::FETCH_ASSOC);