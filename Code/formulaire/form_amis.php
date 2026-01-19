<?php
require_once __DIR__ . '/../include/init.php';
include_once __DIR__ . '/../bd/lec_bd.php';

/** @var PDO $pdo */

if (!isset($_SESSION['utilisateur']['id'])) {
    header('Location: /id.php');
    exit;
}

$utilisateur_id = $_SESSION['utilisateur']['id'];
$message = '';
$recherche = $_GET['recherche'] ?? '';

// === AJOUTER UN AMI ===
if (isset($_GET['add'])) {
    $ami_id = (int)$_GET['add'];
    
    // Vérifier que ce n'est pas soi-même
    if ($ami_id == $utilisateur_id) {
        $message = "Vous ne pouvez pas vous ajouter vous-même.";
    } else {
        // Vérifier si une relation existe déjà (dans n'importe quel sens)
        $stmt = $pdo->prepare("
            SELECT * FROM amis 
            WHERE (id_utilisateur = ? AND id_ami = ?) OR (id_utilisateur = ? AND id_ami = ?)
        ");
        $stmt->execute([$utilisateur_id, $ami_id, $ami_id, $utilisateur_id]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            $message = "Une relation existe déjà avec cet utilisateur.";
        } else {
            // Créer la demande d'ami
            $stmt = $pdo->prepare("INSERT INTO amis (id_utilisateur, id_ami, statut) VALUES (?, ?, 'en_attente')");
            $stmt->execute([$utilisateur_id, $ami_id]);
            $message = "Demande d'ami envoyée !";
        }
    }
}

// === ACCEPTER UNE DEMANDE ===
if (isset($_GET['action']) && $_GET['action'] === 'accepter' && isset($_GET['id'])) {
    $demandeur_id = (int)$_GET['id'];
    
    // Mettre à jour le statut
    $stmt = $pdo->prepare("
        UPDATE amis SET statut = 'accepte' 
        WHERE id_utilisateur = ? AND id_ami = ? AND statut = 'en_attente'
    ");
    $stmt->execute([$demandeur_id, $utilisateur_id]);
    $message = "Demande acceptée !";
}

// === REFUSER UNE DEMANDE ===
if (isset($_GET['action']) && $_GET['action'] === 'refuser' && isset($_GET['id'])) {
    $demandeur_id = (int)$_GET['id'];
    
    // Supprimer la demande
    $stmt = $pdo->prepare("DELETE FROM amis WHERE id_utilisateur = ? AND id_ami = ?");
    $stmt->execute([$demandeur_id, $utilisateur_id]);
    $message = "Demande refusée.";
}

// === SUPPRIMER UN AMI ===
if (isset($_GET['delete'])) {
    $ami_id = (int)$_GET['delete'];
    
    // Supprimer la relation dans les deux sens (car elle peut être dans n'importe quel sens)
    $stmt = $pdo->prepare("
        DELETE FROM amis 
        WHERE (id_utilisateur = ? AND id_ami = ?) OR (id_utilisateur = ? AND id_ami = ?)
    ");
    $stmt->execute([$utilisateur_id, $ami_id, $ami_id, $utilisateur_id]);
    $message = "Ami supprimé. Vous pouvez le réajouter si vous le souhaitez.";
}

// === RECHERCHER DES UTILISATEURS ===
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

// === RÉCUPÉRER MES AMIS (statut accepté) ===
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

// === RÉCUPÉRER LES DEMANDES REÇUES ===
$stmt = $pdo->prepare("
    SELECT u.id, u.nom, u.prenom, u.photo_profil
    FROM amis a
    INNER JOIN utilisateurs u ON u.id = a.id_utilisateur
    WHERE a.id_ami = ? AND a.statut = 'en_attente'
");
$stmt->execute([$utilisateur_id]);
$demandes = $stmt->fetchAll(PDO::FETCH_ASSOC);