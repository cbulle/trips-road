<?php
require_once __DIR__ . '/formulaire/form_amis.php' ;

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes amis - Trips & Roads</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/form.css">
</head>
<body>

<?php include_once __DIR__ . "/modules/header.php"; ?>

<main class="main-index">
    <div class="index_container">
        <h2>Mes Amis</h2>
        <?php if (isset($message)): ?>
            <p class="message"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <div class="container">
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
                                $stmt2->execute([$utilisateur_id, $u['id'], $u['id'], $utilisateur_id]);
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

            <div class="column">
                <h3>Mes amis</h3>
                <?php if ($amis): ?>
                    <ul>
                        <?php foreach ($amis as $ami): ?>
                            <li>
                                <?= htmlspecialchars($ami['nom'] . ' ' . $ami['prenom']) ?>
                                <a href="?delete=<?= $ami['id'] ?>"><button>Supprimer</button></a>
                            </li>
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
