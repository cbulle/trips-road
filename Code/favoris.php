<?php
require_once __DIR__ . '/modules/init.php';
include_once __DIR__ . '/bd/lec_bd.php';

if (!isset($_SESSION['utilisateur']['id'])) {
    header('Location: /id.php');
    exit;
}

$id_utilisateur = $_SESSION['utilisateur']['id'];

$stmt = $pdo->prepare("
    SELECT r.*, u.nom, u.prenom, f.date_ajout
    FROM favoris f
    INNER JOIN roadtrip r ON f.id_roadtrip = r.id
    INNER JOIN utilisateurs u ON r.id_utilisateur = u.id
    WHERE f.id_utilisateur = :id_user
    ORDER BY f.date_ajout DESC
");
$stmt->execute(['id_user' => $id_utilisateur]);
$favoris = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes Favoris - Trips & Roads</title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/accessibilite.css">
</head>
<body>
<?php include_once __DIR__ . "/modules/header.php"; ?>

<main class="main-index">
    <div class="index_container">
        <h1>Mes Road Trips Favoris</h1>

        <?php if (empty($favoris)): ?>
            <p>
                Vous n'avez pas encore de favoris. <br>
                <a href="Roadtrip.php">
                    Découvrez des road trips publics
                </a>
            </p>
        <?php else: ?>
            <div class="roadtrip-grid">
                <?php foreach ($favoris as $fav): ?>
                    <div class="roadtrip-card">
                        <?php if (!empty($fav['photo'])): ?>
                            <img src="/uploads/roadtrips/<?= htmlspecialchars($fav['photo']) ?>" 
                                 alt="Photo du road trip" class="roadtrip-photo">
                        <?php endif; ?>

                        <h3><?= htmlspecialchars($fav['titre']) ?></h3>
                        <p><?= htmlspecialchars($fav['description']) ?></p>
                        <p>
                            Par <?= htmlspecialchars($fav['nom'] . ' ' . $fav['prenom']) ?>
                        </p>
                        <p>
                            Ajouté le <?= date('d/m/Y', strtotime($fav['date_ajout'])) ?>
                        </p>

                        <div class="roadtrip-buttons">
                            <a class="btn-view" href="public_road.php?id=<?= $fav['id'] ?>">
                                Voir
                            </a>
                            <a class="btn-delete" href="/formulaire/favo.php?id=<?= $fav['id'] ?>&action=remove" 
                               onclick="return confirm('Retirer ce road trip de vos favoris ?');">
                                <i class="material-icons">favorite</i>
                                Retirer
                            </a>
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