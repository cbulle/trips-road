<?php

require_once __DIR__ . '/include/init.php';
include_once __DIR__ . '/bd/lec_bd.php';

if (!isset($_SESSION['utilisateur']['id'])) {
    header('Location: /id.php');
    exit;
}

$id_utilisateur = $_SESSION['utilisateur']['id'];

if (isset($_GET['delete_lieu'])) {
    $id_lieu_a_supprimer = intval($_GET['delete_lieu']);
    try {
        $stmtDel = $pdo->prepare("DELETE FROM lieux_favoris WHERE id = :id AND id_utilisateur = :uid");
        $stmtDel->execute(['id' => $id_lieu_a_supprimer, 'uid' => $id_utilisateur]);
        header('Location: favoris.php');
        exit;
    } catch (Exception $e) { }
}

$stmtRT = $pdo->prepare("
    SELECT r.*, u.nom, u.prenom, f.date_ajout
    FROM favoris f
    INNER JOIN roadtrip r ON f.id_roadtrip = r.id
    INNER JOIN utilisateurs u ON r.id_utilisateur = u.id
    WHERE f.id_utilisateur = :id_user
    ORDER BY f.date_ajout DESC
");
$stmtRT->execute(['id_user' => $id_utilisateur]);
$favorisRT = $stmtRT->fetchAll(PDO::FETCH_ASSOC);

$stmtLieux = $pdo->prepare("
    SELECT * FROM lieux_favoris 
    WHERE id_utilisateur = :id_user 
    ORDER BY date_ajout DESC
");
$stmtLieux->execute(['id_user' => $id_utilisateur]);
$favorisLieux = $stmtLieux->fetchAll(PDO::FETCH_ASSOC);

function getIconForCategory($cat) {
    $icons = [
        'restaurant' => '🍽️', 'fast_food' => '🍔', 'cafe' => '☕', 'bar' => '🍺',
        'hotel' => '🏨', 'camping' => '🏕️', 'fuel' => '⛽', 'parking' => '🅿️',
        'attraction' => '🎭', 'museum' => '🏛️', 'park' => '🌳', 'shop' => '🛒',
        'hospital' => '🏥', 'rando' => '🥾'
    ];
    return $icons[$cat] ?? '📍';
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes Favoris - Trips & Roads</title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/accessibilite.css">
    <link rel="stylesheet" href="/css/favoris.css">
    <script src="https://kit.fontawesome.com/d76759a8b0.js" crossorigin="anonymous"></script>
</head>
<body>
<?php include_once __DIR__ . "/modules/header.php"; ?>

<main class="main-index">
    <div class="index_container">
        
        <h2 class="section-title">🚙 Mes Road Trips Favoris</h2>

        <?php if (empty($favorisRT)): ?>
            <p>
                Vous n'avez pas encore de road trips favoris. <br>
                <a href="Roadtrip.php">Découvrez des road trips publics</a>
            </p>
        <?php else: ?>
            <div class="roadtrip-grid">
                <?php foreach ($favorisRT as $fav): ?>
                    <div class="roadtrip-card">
                        <?php 
                        // Gestion de l'image : on vérifie 'photo' et 'photo_cover' pour être sûr
                        $imagePath = "default_trip.jpg"; // Image par défaut
                        $hasImage = false;

                        if (!empty($fav['photo'])) {
                            $imagePath = $fav['photo'];
                            $hasImage = true;
                        } elseif (!empty($fav['photo_cover'])) {
                            $imagePath = $fav['photo_cover'];
                            $hasImage = true;
                        }
                        
                        if ($hasImage): ?>
                            <img src="/uploads/roadtrips/<?= htmlspecialchars($imagePath) ?>" 
                                 alt="Photo du road trip" class="roadtrip-photo">
                        <?php else: ?>
                             <img src="/img/default_trip.jpg" alt="RoadTrip" class="roadtrip-photo" style="background:#ddd;">
                        <?php endif; ?>

                        <h3><?= htmlspecialchars($fav['titre']) ?></h3>
                        <p><?= htmlspecialchars(substr($fav['description'], 0, 100)) ?>...</p>
                        <p><small>Par <?= htmlspecialchars($fav['nom'] . ' ' . $fav['prenom']) ?></small></p>
                        <p class="date-ajout"><small>Ajouté le <?= date('d/m/Y', strtotime($fav['date_ajout'])) ?></small></p>
                        
                        <div class="roadtrip-buttons">
                            <a class="btn-view" href="public_road.php?id=<?= $fav['id'] ?>">Voir</a>
                            <a class="btn-delete" href="/formulaire/favo.php?id=<?= $fav['id'] ?>&action=remove&redirect=favoris.php" 
                               onclick="return confirm('Retirer ce road trip de vos favoris ?');">
                                <i class="fas fa-trash"></i> Retirer
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <hr class="separator">

        <h2 class="section-title">📍 Mes Lieux Favoris</h2>

        <?php if (empty($favorisLieux)): ?>
            <p>
                Vous n'avez pas encore enregistré de lieux. <br>
                Allez sur la <a href="/index.php">carte interactive</a> pour en ajouter !
            </p>
        <?php else: ?>
            <div class="roadtrip-grid">
                <?php foreach ($favorisLieux as $lieu): ?>
                    <div class="roadtrip-card lieu-card">
                        <div style="padding: 15px;">
                            <span class="category-badge">
                                <?= getIconForCategory($lieu['categorie']) ?> <?= htmlspecialchars(ucfirst($lieu['categorie'])) ?>
                            </span>
                            
                            <h3><?= htmlspecialchars($lieu['nom_lieu']) ?></h3>
                            
                            <?php if(!empty($lieu['adresse'])): ?>
                                <p class="address-text"><?= htmlspecialchars($lieu['adresse']) ?></p>
                            <?php else: ?>
                                <p class="address-text">Coordonnées : <?= round($lieu['latitude'], 4) ?>, <?= round($lieu['longitude'], 4) ?></p>
                            <?php endif; ?>

                            <p><small>Ajouté le <?= date('d/m/Y', strtotime($lieu['date_ajout'])) ?></small></p>

                            <div class="roadtrip-buttons" style="margin-top: 15px;">
                                <a class="btn-map" target="_blank" 
                                   href="https://www.google.com/maps/search/?api=1&query=<?= $lieu['latitude'] ?>,<?= $lieu['longitude'] ?>">
                                    <i class="fas fa-map-marked-alt"></i> Carte
                                </a>

                                <a class="btn-delete" href="favoris.php?delete_lieu=<?= $lieu['id'] ?>" 
                                   onclick="return confirm('Retirer ce lieu de vos favoris ?');">
                                    <i class="fas fa-trash"></i> Retirer
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</main>

<?php include_once __DIR__ . "/modules/footer.php"; ?>
</body>
</html>