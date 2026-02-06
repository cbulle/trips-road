    <div class="index_container">
        
        <div class="header-tools">
            <h1>🕓 Mon Historique</h1>
            <?php if (!empty($historique)): ?>
                <a href="historique.php?action=clear" class="btn-clear-history" 
                   onclick="return confirm('Voulez-vous vraiment effacer tout votre historique de consultation ?');">
                    <i class="fas fa-trash-alt"></i> Tout effacer
                </a>
            <?php endif; ?>
        </div>

        <?php if (empty($historique)): ?>
            <div style="text-align: center; padding: 50px;">
                <p>Vous n'avez consulté aucun road trip récemment.</p>
                <?= $this->Html->link(
                    'Explorer les road trips',
                    ['controller' => 'Roadtrips', 'action' => 'publicRoadtrips']
                )
                ?>
            </div>
        <?php else: ?>
            <div class="roadtrip-grid">
                <?php foreach ($historique as $item): ?>
                    <div class="roadtrip-card">
                        <?php 
                        // Gestion Image
                        $imagePath = "default_trip.jpg";
                        if (!empty($item['photo'])) $imagePath = $item['photo'];
                        elseif (!empty($item['photo_cover'])) $imagePath = $item['photo_cover'];
                        ?>
                        
                        <img src="/uploads/roadtrips/<?= htmlspecialchars($imagePath) ?>" 
                             alt="Photo du road trip" class="roadtrip-photo">

                        <h3><?= htmlspecialchars($item['titre']) ?></h3>
                        
                        <span class="date-visite">
                            Vu le <?= date('d/m/Y à H:i', strtotime($item['date_visite'])) ?>
                        </span>

                        <p><?= htmlspecialchars(substr($item['description'], 0, 80)) ?>...</p>
                        <p><small>Par <?= htmlspecialchars($item['nom'] . ' ' . $item['prenom']) ?></small></p>

                        <div class="roadtrip-buttons">
                            <a class="btn-view" href="public_road.php?id=<?= $item['id'] ?>">
                                <i class="fas fa-eye"></i> Revoir
                            </a>
                            <a class="btn-edit" href="/formulaire/favo.php?id=<?= $item['id'] ?>&redirect=historique.php">
                                <i class="far fa-star"></i> Favoris
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>