<?php
require_once __DIR__ . '/include/init.php';
include_once __DIR__ . '/bd/lec_bd.php';

/** @var PDO $pdo */

if (!isset($_SESSION['utilisateur']['id'])) {
    header('Location: /id.php');
    exit;
}

$id_utilisateur = $_SESSION['utilisateur']['id'];
$show_share = $_GET['show_share'] ?? null;
$share_url = $_SESSION['share_url'] ?? null;

$stmt = $pdo->prepare("SELECT * FROM roadtrip WHERE id_utilisateur = :id ORDER BY id DESC");
$stmt->execute(['id' => $id_utilisateur]);
$roadtrips = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes Road Trips</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/page_link.css">

</head>
<body>
<?php include_once __DIR__ . "/modules/header.php"; ?>

<h1>Mes Road Trips</h1>

<?php if (empty($roadtrips)) : ?>
    <p>Aucun road trip pour le moment.</p>
<?php else : ?>
<div class="roadtrip-grid">
    <?php foreach ($roadtrips as $rt): ?>
        <div class="roadtrip-card">
            
            <?php 
                $estTermine = (isset($rt['statut']) && $rt['statut'] === 'termine');
                $classeCss = $estTermine ? 'statut-termine' : 'statut-brouillon';
                $texteStatut = $estTermine ? 'Terminé' : 'Brouillon';
            ?>

            <?php if (!empty($rt['photo'])): ?>
                <img src="/uploads/roadtrips/<?= htmlspecialchars($rt['photo']) ?>" 
                     alt="Photo du road trip" class="roadtrip-photo">
            <?php endif; ?>

            <div style="padding: 10px 10px 0 10px;">
                <span class="badge-statut <?= $classeCss ?>">
                    <?= $texteStatut ?>
                </span>
            </div>

            <h3><?= htmlspecialchars($rt['titre']) ?></h3>
            <p><?= htmlspecialchars($rt['description']) ?></p>

            <div class="roadtrip-buttons">
                <a class="btn-view" href="vuRoadTrip.php?id=<?= $rt['id'] ?>">
                    <i class="material-icons">visibility</i>
                </a>

                <a class="btn-edit" href="creationRoadTrip.php?id=<?= $rt['id'] ?>">
                    <i class="material-icons">edit</i> 
                </a>
                
                <a class="btn-share" href="generate_shared_link.php?id=<?= $rt['id'] ?>">
                    <i class="material-icons">share</i>                   
                </a>

                <a class="btn-delete" href="/formulaire/delete_RoadTrip.php?id=<?= $rt['id'] ?>" 
                   onclick="return confirm('Voulez-vous vraiment supprimer ce road trip ?');">
                                        <i class="material-icons">delete</i>                   

                </a>
            </div>

        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if ($show_share && $share_url): ?>
<div class="share-modal active" id="shareModal">
    <div class="share-modal-content">
        <span class="share-modal-close" onclick="closeShareModal()">&times;</span>
        <h2>Partager votre road trip</h2>
        <p>Copiez ce lien pour partager votre road trip :</p>
        
        <div class="share-url-container">
            <input type="text" class="share-url-input" id="shareUrl" value="<?= htmlspecialchars($share_url) ?>" readonly>
            <button class="copy-btn" onclick="copyShareUrl()">Copier</button>
        </div>
        
        <div class="copy-success" id="copySuccess">Lien copié !</div>
        
    </div>
</div>
<?php 
    unset($_SESSION['share_url']);
endif; 
?>

<script src ="/js/profil.js"></script>
<?php include_once __DIR__ . "/modules/footer.php"; ?>

</body>
</html>