<?php
require_once __DIR__ . '/formulaire/form_amis.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes amis - Trips & Roads</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/form.css">
    <link rel="stylesheet" href="css/index.css">
   
</head>
<body>

<?php include_once __DIR__ . "/modules/header.php"; ?>

<main class="main-index">
    <div class="index_container">
        <h2>Mes Amis</h2>
        <?php if (isset($message)): ?>
            <p class="message" style="text-align: center; color: var(--orange); font-weight: bold;">
                <?= htmlspecialchars($message) ?>
            </p>
        <?php endif; ?>

        <div class="container">
            <div class="column">
                <h3>Rechercher un utilisateur</h3>
                <form method="GET">
                    <input type="text" name="recherche" placeholder="Nom ou prénom" value="<?= htmlspecialchars($recherche) ?>">
                    <button type="submit">Rechercher</button>
                </form>

                <?php if ($utilisateurs): ?>
                    <ul style="list-style: none; padding: 0;">
                        <?php foreach ($utilisateurs as $u): ?>
                            <li class="ami-item">
                                <div class="ami-info">
                                    <?php if (!empty($u['photo_profil'])): ?>
                                        <img src="/uploads/profils/<?= htmlspecialchars($u['photo_profil']) ?>" 
                                             class="ami-photo" alt="Photo">
                                    <?php else: ?>
                                        <div class="ami-placeholder">
                                            <?= strtoupper(substr($u['prenom'], 0, 1)) ?>
                                        </div>
                                    <?php endif; ?>
                                    <span><?= htmlspecialchars($u['nom'] . ' ' . $u['prenom']) ?></span>
                                </div>
                                
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
                                    <span style="color: #666;">Demande envoyée</span>
                                <?php elseif ($statut === 'accepte'): ?>
                                    <span style="color: green;">Ami</span>
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
                    <ul style="list-style: none; padding: 0;">
                        <?php foreach ($amis as $ami): ?>
                            <li class="ami-item">
                                <div class="ami-info">
                                    <?php if (!empty($ami['photo_profil'])): ?>
                                        <img src="/uploads/profils/<?= htmlspecialchars($ami['photo_profil']) ?>" 
                                             class="ami-photo" alt="Photo">
                                    <?php else: ?>
                                        <div class="ami-placeholder">
                                            <?= strtoupper(substr($ami['prenom'], 0, 1)) ?>
                                        </div>
                                    <?php endif; ?>
                                    <span><?= htmlspecialchars($ami['nom'] . ' ' . $ami['prenom']) ?></span>
                                </div>
                                
                                <div class="ami-actions">
                                    <a href="/messagerie/debut_conv.php?ami_id=<?= $ami['id'] ?>" 
                                       class="btn-message">
                                        💬 Message
                                    </a>
                                    <a href="?delete=<?= $ami['id'] ?>" 
                                       class="btn-supprimer"
                                       onclick="return confirm('Voulez-vous vraiment supprimer cet ami ?');">
                                        Supprimer
                                    </a>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>Vous n'avez pas encore d'amis.</p>
                <?php endif; ?>

                <h3 style="margin-top: 30px;">Demandes d'amis reçues</h3>
                <?php if ($demandes): ?>
                    <ul style="list-style: none; padding: 0;">
                        <?php foreach ($demandes as $demande): ?>
                            <li class="ami-item">
                                <div class="ami-info">
                                    <?php if (!empty($demande['photo_profil'])): ?>
                                        <img src="/uploads/profils/<?= htmlspecialchars($demande['photo_profil']) ?>" 
                                             class="ami-photo" alt="Photo">
                                    <?php else: ?>
                                        <div class="ami-placeholder">
                                            <?= strtoupper(substr($demande['prenom'], 0, 1)) ?>
                                        </div>
                                    <?php endif; ?>
                                    <span><?= htmlspecialchars($demande['nom'] . ' ' . $demande['prenom']) ?></span>
                                </div>
                                
                                <div class="ami-actions">
                                    <a href="?action=accepter&id=<?= $demande['id'] ?>">
                                        <button style="background: green; color: white;">Accepter</button>
                                    </a>
                                    <a href="?action=refuser&id=<?= $demande['id'] ?>">
                                        <button style="background: var(--rouge); color: white;">Refuser</button>
                                    </a>
                                </div>
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