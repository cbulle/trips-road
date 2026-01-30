<?php
/** @var PDO $pdo
 * @var int    $utilisateur_id
 * @var string $recherche
 * @var array  $utilisateurs
 * @var array  $amis
 * @var array  $demandes
 */
?>



<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes amis - Trips & Roads</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">    
    <link rel="stylesheet" href="css/form.css">
    <link rel="stylesheet" href="css/index.css">
   
</head>
<body>

<?php include_once ROOT . "/modules/header.php"; ?>

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
                                        <img src="/uploads/pp/<?= htmlspecialchars($u['photo_profil']) ?>" 
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
                                    <span>Demande envoyée</span>
                                <?php elseif ($statut === 'accepte'): ?>
                                    <span >Ami</span>
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
                                        <img src="/uploads/pp/<?= htmlspecialchars($ami['photo_profil']) ?>" 
                                             class="ami-photo" alt="Photo">
                                    <?php else: ?>
                                        <div class="ami-placeholder">
                                            <?= strtoupper(substr($ami['prenom'], 0, 1)),
                                                strtoupper(substr($ami['nom'], 0, 1)) ?>
                                        </div>
                                    <?php endif; ?>
                                    <span><?= htmlspecialchars($ami['nom'] . ' ' . $ami['prenom']) ?></span>
                                </div>
                                
                                <div class="ami-actions">
                                    <a href="/debut_conv?ami_id=<?= $ami['id'] ?>"
                                       class="btn-message">
                                        <i class="material-icons" >chat</i> Message
                                    </a>
                                    <a href="?delete=<?= $ami['id'] ?>" 
                                       class="btn-supprimer"
                                       onclick="return confirm('Voulez-vous vraiment supprimer cet ami ?');">
                                        <i class="material-icons">delete</i>Supprimer
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
                                        <img src="/uploads/pp/<?= htmlspecialchars($demande['photo_profil']) ?>" 
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
                                        <button>Acepter</button>
                                    </a>
                                    <a href="?action=refuser&id=<?= $demande['id'] ?>">
                                        <button>Refuser</button>
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

<?php include_once ROOT . "/modules/footer.php"; ?>

</body>
</html>