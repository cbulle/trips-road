<?php
require_once __DIR__ . '/modules/init.php';
include_once __DIR__ . '/bd/lec_bd.php';
 

if (!isset($_SESSION['utilisateur'])) {
    header("Location: /login.php");
    exit;
}

$utilisateur_id = $_SESSION['utilisateur']['id'];
$message = '';



// --- Ajouter un ami ---
if (isset($_GET['add'])) {
    $id_ami = (int)$_GET['add'];
    if ($id_ami != $utilisateur_id) {
        $stmt = $pdo->prepare("
            INSERT INTO amis (id_utilisateur, id_ami, statut)
            VALUES (?, ?, 'en_attente')
            ON DUPLICATE KEY UPDATE statut='en_attente'
        ");
        $stmt->execute([$utilisateur_id, $id_ami]);
        $message = "Demande envoyée.";
    }
}

// --- Accepter / refuser demande ---
if (isset($_GET['action'], $_GET['id'])) {
    $id_ami = (int)$_GET['id'];
    $action = $_GET['action'];

    if ($action === 'accepter') {
        $stmt = $pdo->prepare("
            UPDATE amis SET statut='accepte' 
            WHERE id_utilisateur=? AND id_ami=?
        ");
        $stmt->execute([$id_ami, $utilisateur_id]);
        $message = "Demande acceptée.";
    } elseif ($action === 'refuser') {
        $stmt = $pdo->prepare("
            DELETE FROM amis 
            WHERE id_utilisateur=? AND id_ami=?
        ");
        $stmt->execute([$id_ami, $utilisateur_id]);
        $message = "Demande refusée.";
    }
}

// --- Recherche utilisateurs ---
$recherche = $_GET['recherche'] ?? '';
$utilisateurs = [];
if ($recherche) {
    $stmt = $pdo->prepare("
        SELECT id, nom, prenom 
        FROM utilisateurs 
        WHERE CONCAT(nom,' ',prenom) LIKE ? AND id != ?
        LIMIT 10
    ");
    $stmt->execute(["%$recherche%", $utilisateur_id]);
    $utilisateurs = $stmt->fetchAll();
}

// --- Liste des amis acceptés ---
$stmt = $pdo->prepare("
    SELECT u.id, u.nom, u.prenom
    FROM utilisateurs u
    INNER JOIN amis a 
        ON (u.id = a.id_ami AND a.id_utilisateur = ?) 
        OR (u.id = a.id_utilisateur AND a.id_ami = ?)
    WHERE a.statut = 'accepte'
    ORDER BY u.nom ASC
");
$stmt->execute([$utilisateur_id, $utilisateur_id]);
$amis = $stmt->fetchAll();

// --- Liste des demandes reçues ---
$stmt = $pdo->prepare("
    SELECT u.id, u.nom, u.prenom
    FROM utilisateurs u
    INNER JOIN amis a 
        ON u.id = a.id_utilisateur
    WHERE a.id_ami = ? AND a.statut = 'en_attente'
");
$stmt->execute([$utilisateur_id]);
$demandes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Mes amis - Trips & Roads</title>
<link rel="stylesheet" href="css/style.css">
<style>
.container { display: flex; gap: 20px; }
.column { flex: 1; }
ul { list-style: none; padding: 0; }
li { margin-bottom: 8px; }
button { margin-left: 10px; }
.message { color: green; }
</style>
</head>
<body>

<?php include_once __DIR__ . "/modules/header.php"; ?>

<main class="main-index">
<div class="index_container">
    <h2>Mes Amis</h2>
    <?php if ($message): ?>
        <p class="message"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <div class="container">
        <!-- Recherche utilisateurs -->
        <div class="column">
            <h3>Rechercher un utilisateur</h3>
            <form method="GET">
                <input type="text" name="recherche" placeholder="Nom ou prénom" value="<?= htmlspecialchars($recherche) ?>">
                <button type="submit">Rechercher</button>
            </form>

            <?php if ($utilisateurs): ?>
                <ul>
                    <?php foreach ($utilisateurs as $u): ?>
                        <li>
                            <?= htmlspecialchars($u['nom'] . ' ' . $u['prenom']) ?>
                            <?php
                            $stmt2 = $pdo->prepare("
                                SELECT statut FROM amis 
                                WHERE (id_utilisateur=? AND id_ami=?) OR (id_utilisateur=? AND id_ami=?)
                            ");
                            $stmt2->execute([$utilisateur_id,$u['id'],$u['id'],$utilisateur_id]);
                            $statut = $stmt2->fetchColumn();
                            ?>
                            <?php if (!$statut): ?>
                                <a href="?add=<?= $u['id'] ?>"><button>Ajouter</button></a>
                            <?php elseif ($statut === 'en_attente'): ?>
                                <span>Demande envoyée</span>
                            <?php elseif ($statut === 'accepte'): ?>
                                <span>Ami</span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php elseif ($recherche): ?>
                <p>Aucun utilisateur trouvé.</p>
            <?php endif; ?>
        </div>

        <!-- Liste amis et demandes -->
        <div class="column">
            <h3>Mes amis</h3>
            <?php if ($amis): ?>
                <ul>
                    <?php foreach ($amis as $ami): ?>
                        <li><?= htmlspecialchars($ami['nom'] . ' ' . $ami['prenom']) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>Vous n'avez pas encore d'amis.</p>
            <?php endif; ?>

            <h3>Demandes d'amis reçues</h3>
            <?php if ($demandes): ?>
                <ul>
                    <?php foreach ($demandes as $demande): ?>
                        <li>
                            <?= htmlspecialchars($demande['nom'] . ' ' . $demande['prenom']) ?>
                            <a href="?action=accepter&id=<?= $demande['id'] ?>"><button>Accepter</button></a>
                            <a href="?action=refuser&id=<?= $demande['id'] ?>"><button>Refuser</button></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>Aucune demande en attente.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
</main>

<?php include_once __DIR__ . "/modules/footer.php"; ?>
</body>
</html>
