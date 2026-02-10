<?php
/**
 * @var \App\View\AppView $this
 * @var array $friends
 * @var int $userId
 */
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau Message - Messagerie</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="/css/messagerie.css">
</head>
<body>
<div class="messages-wrapper">
    <!-- Sidebar -->
    <aside class="messages-sidebar">
        <div class="sidebar-header">
            <a href="<?= $this->Url->build(['action' => 'index']) ?>" class="back-button" title="Retour">
                <i class="material-icons">arrow_back</i>
            </a>
            <h1>Messages</h1>
        </div>
    </aside>

    <!-- Zone principale -->
    <main class="messages-main">
        <div class="select-friend-container">
            <div class="select-friend-header">
                <h2>Sélectionnez un ami</h2>
                <p>Choisissez la personne à qui vous voulez écrire</p>
            </div>

            <div class="search-box">
                <i class="material-icons">search</i>
                <input type="text" id="searchInput" placeholder="Rechercher...">
            </div>

            <?php if (empty($friends)): ?>
                <div class="empty-state">
                    <i class="material-icons">people_outline</i>
                    <p>Vous n'avez pas d'amis pour le moment</p>
                    <a href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'index']) ?>" class="btn-primary">
                        Ajouter des amis
                    </a>
                </div>
            <?php else: ?>
                <div class="friends-list" id="friendsList">
                    <?php foreach ($friends as $friend): ?>
                        <a href="<?= $this->Url->build(['action' => 'start', $friend->id]) ?>"
                           class="friend-item"
                           data-name="<?= h(strtolower($friend->prenom . ' ' . $friend->nom)) ?>">
                            <div class="friend-avatar">
                                <?php if (!empty($friend->avatar)): ?>
                                    <img src="<?= h($friend->avatar) ?>" alt="<?= h($friend->prenom) ?>">
                                <?php else: ?>
                                    <div class="avatar-placeholder">
                                        <?= strtoupper(substr($friend->prenom, 0, 1)) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="friend-info">
                                <h3><?= h($friend->prenom . ' ' . $friend->nom) ?></h3>
                                <p class="friend-status">
                                    <?= !empty($friend->bio) ? h($friend->bio) : 'Pas de description' ?>
                                </p>
                            </div>
                            <i class="material-icons">chevron_right</i>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<script>
    const searchInput = document.getElementById('searchInput');
    const friendsList = document.getElementById('friendsList');

    if (searchInput && friendsList) {
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            const friends = friendsList.querySelectorAll('.friend-item');

            friends.forEach(friend => {
                const name = friend.getAttribute('data-name');
                if (name.includes(query) || query === '') {
                    friend.style.display = '';
                } else {
                    friend.style.display = 'none';
                }
            });

            // Afficher un message si aucun résultat
            const visible = Array.from(friends).some(f => f.style.display !== 'none');
            if (!visible && query) {
                if (!friendsList.querySelector('.no-results')) {
                    const noResults = document.createElement('div');
                    noResults.className = 'no-results';
                    noResults.textContent = 'Aucun ami trouvé';
                    friendsList.appendChild(noResults);
                }
            } else {
                const noResults = friendsList.querySelector('.no-results');
                if (noResults) noResults.remove();
            }
        });
    }
</script>
</body>
</html>
