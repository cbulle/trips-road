<?php
require_once __DIR__ . '/modules/init.php';
include_once __DIR__ . '/bd/lec_bd.php';

if (!isset($_SESSION['utilisateur']['id'])) {
    header('Location: /id.php');
    exit;
}

$id_utilisateur = $_SESSION['utilisateur']['id'];

// --- Récupération des road trips ---
$stmt = $pdo->prepare("SELECT * FROM roadtrip WHERE id_utilisateur = :id ORDER BY id DESC");
$stmt->execute(['id' => $id_utilisateur]);
$roadtrips = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes Road Trips</title>
    <link rel="stylesheet" href="/css/style.css">
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

            <?php if (!empty($rt['photo'])): ?>
                <img src="/uploads/roadtrips/<?= htmlspecialchars($rt['photo']) ?>" 
                     alt="Photo du road trip" class="roadtrip-photo">
            <?php endif; ?>

            <h3><?= htmlspecialchars($rt['titre']) ?></h3>
            <p><?= htmlspecialchars($rt['description']) ?></p>

            <div class="roadtrip-buttons">
                <a class="btn-view" href="roadtrip_view.php?id=<?= $rt['id'] ?>">
                    Voir
                </a>

                <a class="btn-edit" href="roadtrip_edit.php?id=<?= $rt['id'] ?>">
                    <!-- Icône stylo -->
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                        <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25z" stroke="black" stroke-width="2"/>
                        <path d="M20.71 7.04a1 1 0 0 0 0-1.41l-2.34-2.34a1 1 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z" stroke="black" stroke-width="2"/>
                    </svg>
                </a>

                <a class="btn-delete" href="/formulaire/delete_RoadTrip.php?id=<?= $rt['id'] ?>" 
                   onclick="return confirm('Voulez-vous vraiment supprimer ce road trip ?');">
                    <!-- Icône corbeille -->
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                        <path d="M3 6h18" stroke="white" stroke-width="2"/>
                        <path d="M8 6v14h8V6" stroke="white" stroke-width="2"/>
                        <path d="M10 10v6" stroke="white" stroke-width="2"/>
                        <path d="M14 10v6" stroke="white" stroke-width="2"/>
                        <path d="M9 6l1-2h4l1 2" stroke="white" stroke-width="2"/>
                    </svg>
                </a>
            </div>

        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php include_once __DIR__ . "/modules/footer.php"; ?>

</body>
</html>
