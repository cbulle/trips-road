<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\History> $historique
 */

$this->assign('title', '🕓 Mon Historique');
$this->assign('mainClass', 'historique-page'); // Classe spécifique si besoin
?>

<div>
    
    <div class="header-tools" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h1>🕓 Mon Historique</h1>
        
        <?php if (!$historique->isEmpty()): ?>
            <?= $this->Form->postLink(
                '<i class="material-icons" style="vertical-align: middle; font-size: 18px;">delete_sweep</i> Tout effacer',
                ['action' => 'deleteHistorique'],
                [
                    'escape' => false,
                    'class' => 'btn-clear-history', // Tu pourras styliser ce bouton spécifique
                    'style' => 'background: #e74c3c; color: white; padding: 5px 10px; border-radius: 4px; text-decoration: none; display: inline-flex; align-items: center; gap: 5px;',
                    'confirm' => 'Voulez-vous vraiment effacer tout votre historique ?'
                ]
            ) ?>
        <?php endif; ?>
    </div>

    <?= $this->Flash->render() ?>

    <?php if ($historique->isEmpty()): ?>
        <p style="text-align: center; margin-top: 50px; color: #666;">
            Vous n'avez consulté aucun road trip récemment.
        </p>
        <div style="text-align: center;">
            <?= $this->Html->link(
                'Explorer les road trips',
                ['controller' => 'Roadtrips', 'action' => 'publicRoadtrips'],
                ['class' => 'btn-view', 'style' => 'padding: 10px 20px; text-decoration: none;']
            ) ?>
        </div>
    <?php else: ?>

        <div class="roadtrip-grid">
            <?php foreach ($historique as $item): ?>
                <?php 
                    // On récupère le roadtrip associé à l'entrée d'historique
                    $rt = $item->roadtrip;
                    
                    // Si le roadtrip a été supprimé entre temps, on évite le crash
                    if (!$rt) continue; 
                ?>

                <div class="roadtrip-card">

                    <?php
                    // --- TA LOGIQUE D'IMAGE (Adaptée) ---
                    $urlImage = '/img/imgBase.png'; // Image par défaut
                    
                    // On vérifie photo_url (comme dans ton fichier public)
                    // On garde aussi une compatibilité si tu as 'photo' ou 'photo_cover' dans ta BDD
                    $photoName = $rt->photo_url ?? $rt->photo ?? $rt->photo_cover ?? null;

                    if (!empty($photoName)) {
                        $cheminPhysique = WWW_ROOT . 'uploads' . DS . 'roadtrips' . DS . $photoName;
                        if (file_exists($cheminPhysique)) {
                            $urlImage = '/uploads/roadtrips/' . $photoName;
                        }
                    }
                    ?>

                    <?= $this->Html->image($urlImage, [
                        'alt' => 'Photo du road trip',
                        'class' => 'roadtrip-photo',
                        'url' => ['action' => 'view', $rt->id] // Rend l'image cliquable
                    ]) ?>

                    <h3><?= h($rt->title) ?></h3>

                    <span class="status-badge" style="background-color: #34495e; color: #fff;">
                        👁️ Vu le <?= $item->created->format('d/m/Y') ?>
                    </span>

                    <p><?= h($this->Text->truncate($rt->description, 100)) ?></p>

                    <p class="creator-info">
                        Proposé par :
                        <strong>
                            <?= h($rt->user->username ?? 'Utilisateur inconnu') ?>
                        </strong>
                    </p>

                    <div class="roadtrip-buttons">
                        <a class="btn-view" href="<?= $this->Url->build(['controller' => 'Roadtrips', 'action' => 'view', $rt->id]) ?>" title="Revoir ce roadtrip">
                            <i class="material-icons">visibility</i>
                        </a>

                        <a class="btn-favori"
                           href="<?= $this->Url->build(['controller' => 'Favorites', 'action' => 'add', $rt->id, '?' => ['redirect' => 'historique']]) ?>" title="Ajouter aux favoris">
                            <i class="material-icons">favorite_border</i>
                        </a>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>
</div>