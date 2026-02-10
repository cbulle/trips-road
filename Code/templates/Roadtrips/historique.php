<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Roadtrip[]|\Cake\Collection\CollectionInterface $historique
 */

// Configuration de la page
$this->assign('title', '🕓 Mon Historique');
$this->assign('mainClass', 'historique-view-page'); // Classe pour le CSS global si besoin
?>

<div class="index_container">
    
    <div class="header-tools">
        <h1>🕓 Mon Historique</h1>
        <?php if (!$historique->isEmpty()): ?>
            <?= $this->Form->postLink(
    '<i class="fas fa-trash-alt"></i> Tout effacer',
    ['action' => 'deleteHistorique'], // <--- C'est ici qu'on change le nom
    [
        'escape' => false,
        'class' => 'btn-clear-history',
        'confirm' => 'Voulez-vous vraiment effacer tout votre historique ?'
    ]
) ?>
        <?php endif; ?>
    </div>

    <?php if ($historique->isEmpty()): ?>
        <div style="text-align: center; padding: 50px;">
            <p>Vous n'avez consulté aucun road trip récemment.</p>
            <?= $this->Html->link(
                'Explorer les road trips',
                ['controller' => 'Roadtrips', 'action' => 'publicRoadtrips'],
                ['class' => 'btn-explore'] // Ajout d'une classe si nécessaire
            ) ?>
        </div>
    <?php else: ?>
        <div class="roadtrip-grid">
            <?php foreach ($historique as $item): ?>
                <?php 
                    // On suppose que $item est l'entité Roadtrip jointe à l'historique
                    // ou que $item est l'entité Historique contenant le Roadtrip.
                    // Adapté ici pour un objet $roadtrip direct.
                    $roadtrip = $item->roadtrip ?? $item; // Fallback selon votre structure
                    
                    // Gestion Image (Logique CakePHP avec Url Helper)
                    $imageName = 'default_trip.jpg';
                    if (!empty($roadtrip->photo)) {
                        $imageName = $roadtrip->photo;
                    } elseif (!empty($roadtrip->photo_cover)) {
                        $imageName = $roadtrip->photo_cover;
                    }
                    
                    $imageUrl = $this->Url->webroot('uploads/roadtrips/' . $imageName);
                ?>

                <div class="roadtrip-card">
                    <img src="<?= $imageUrl ?>" 
                         alt="Photo du road trip <?= h($roadtrip->title) ?>" 
                         class="roadtrip-photo" loading="lazy">

                    <h3><?= h($roadtrip->title) ?></h3>
                    
                    <span class="date-visite">
    <?php 
        // On essaie de trouver la bonne colonne de date
        $dateObj = $item->created ?? $item->date_visite ?? null;

        if ($dateObj) {
            echo 'Vu le ' . $dateObj->format('d/m/Y à H:i');
        }
    ?>
</span>

                    <p>
                        <?= $this->Text->truncate(
                            $roadtrip->description,
                            80,
                            ['ellipsis' => '...', 'exact' => false]
                        ) ?>
                    </p>

                    <p>
                        <small>Par 
                            <?= h(($roadtrip->user->last_name ?? '') . ' ' . ($roadtrip->user->first_name ?? $roadtrip->user->username ?? 'Inconnu')) ?>
                        </small>
                    </p>

                    <div class="roadtrip-buttons">
                        <?= $this->Html->link(
                            '<i class="fas fa-eye"></i> Revoir',
                            ['controller' => 'Roadtrips', 'action' => 'view', $roadtrip->id],
                            ['class' => 'btn-view', 'escape' => false]
                        ) ?>

                        <?= $this->Html->link(
                            '<i class="far fa-star"></i> Favoris',
                            [
                                'controller' => 'Favorites', 
                                'action' => 'add', 
                                $roadtrip->id, 
                                '?' => ['redirect' => 'historique'] // Gestion de la redirection
                            ],
                            ['class' => 'btn-edit', 'escape' => false]
                        ) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>