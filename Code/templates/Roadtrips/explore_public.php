<h1>Road Trips Publics</h1>

<?php if (isset($_SESSION['message'])): ?>
    <p style="text-align: center; color: green; font-weight: bold;">
        <?= htmlspecialchars($_SESSION['message']) ?>
    </p>
    <?php unset($_SESSION['message']); ?>
<?php endif; ?>

<?php if (empty($roadtrips)) : ?>
    <p>Aucun road trip public pour le moment.</p>
<?php else : ?>
    <div class="roadtrip-grid">
        <?php foreach ($roadtrips as $rt): ?>
            <div class="roadtrip-card">

                <?php if (!empty($rt['photo'])): ?>
                    <img src="/uploads/roadtrips/<?= htmlspecialchars($rt['photo']) ?>"
                         alt="Photo du road trip" class="roadtrip-photo">
                <?php else : ?>
                    <img src="/img/imgBase.png" alt="Photo par défaut du road trip" class="roadtrip-photo">
                <?php endif; ?>

                <h3><?= htmlspecialchars($rt['titre']) ?></h3>

                <?php
                $isTermine = ($rt['statut'] === 'termine');
                $classeStatus = $isTermine ? 'status-termine' : 'status-brouillon';
                $labelStatus = $isTermine ? '✅ Terminé' : '🚧 En cours';
                ?>
                <span class="status-badge <?= $classeStatus ?>">
                <?= $labelStatus ?>
            </span>
                <p><?= htmlspecialchars($rt['description']) ?></p>

                <p class="creator-info">
                    Proposé par : <strong><?= htmlspecialchars($rt['pseudo'] ?? 'Utilisateur inconnu') ?></strong>
                </p>

                <div class="roadtrip-buttons">
                    <a class="btn-view" href="/public_road?id=<?= $rt['id'] ?>">
                        <i class="material-icons">visibility</i>
                    </a>

                    <?php /*if ($id_utilisateur): ?>
                        <?php $isFavori = in_array($rt['id'], $favorisIds); ?>
                        <a class="btn-favori <?= $isFavori ? 'active' : '' ?>"
                           href="/favo?id=<?= $rt['id'] ?>&redirect=Roadtrip">
                            <i class="material-icons">favorite</i>
                        </a>
                    <?php endif;*/ ?>
                </div>

            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
