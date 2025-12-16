<?php
require_once __DIR__ . '/modules/init.php';
include_once __DIR__ . '/bd/lec_bd.php';

if (!isset($_SESSION['utilisateur']['id'])) {
    header('Location: /id.php');
    exit;
}

$id_utilisateur = $_SESSION['utilisateur']['id'];

// Récupération des road trips favoris
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
</head>
<body>
<?php include_once __DIR__ . "/modules/header.php"; ?>

<main class="main-index">
    <div class="index_container">
        <h1>Mes Road Trips Favoris</h1>

        <?php if (empty($favoris)): ?>
            <p style="text-align: center; margin-top: 40px; color: #666;">
                Vous n'avez pas encore de favoris. <br>
                <a href="Roadtrip.php" style="color: var(--orange); text-decoration: underline;">
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
                        <p style="font-size: 0.9em; color: #666; margin-top: 10px;">
                            Par <?= htmlspecialchars($fav['nom'] . ' ' . $fav['prenom']) ?>
                        </p>
                        <p style="font-size: 0.85em; color: #999;">
                            Ajouté le <?= date('d/m/Y', strtotime($fav['date_ajout'])) ?>
                        </p>

                        <div class="roadtrip-buttons">
                            <a class="btn-view" href="public_road.php?id=<?= $fav['id'] ?>">
                                Voir
                            </a>
                            <a class="btn-delete" href="/formulaire/toggle_favori.php?id=<?= $fav['id'] ?>&action=remove" 
                               onclick="return confirm('Retirer ce road trip de vos favoris ?');">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" 
                                          fill="red"/>
                                </svg>
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