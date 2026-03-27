<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Roadtrip> $randomRoadtrips
 */
?>
<section class="hero-index">
    <div class="hero-content">
        <h1>Prêt pour l'aventure ?</h1>
        <p>Créez votre propre itinéraire ou découvrez ceux de la communauté.</p>
        <div class="hero-buttons">
            <?= $this->Html->link('<span>➕</span> Créer un Road Trip', ['controller' => 'Roadtrips', 'action' => 'add'], ['escape' => false, 'class' => 'btn-action primary']) ?>
            <?= $this->Html->link('<span>🌍</span> Voir les Road Trips Publics', ['controller' => 'Roadtrips', 'action' => 'publicRoadtrips'], ['escape' => false, 'class' => 'btn-action secondary']) ?>
        </div>
    </div>
</section>

<section class="full-map-container">
    <div class="floating-search">
        <div class="search-input-group">
            <?= $this->Form->text('poi_search', ['id' => 'poiSearchIndex', 'placeholder' => 'Rechercher un lieu...']) ?>
            <button type="button" id="searchBtnIndex">🔍</button>
        </div>
        <ul id="searchResultsIndex" class="searching-results"></ul>
    </div>

    <div class="map-sidebar open" id="mapSidebar">
        <div class="sidebar-content">
            <div class="category-header">
                <h4>Filtres</h4>
            </div>

            <div class="category-select-wrapper">
                <label for="categorySelect">Catégories :</label>
                <?= $this->Form->select('category', [
                    'Restauration' => ['restaurant' => '🍽️ Restaurants', 'fast_food' => '🍔 Fast-food', 'cafe' => '☕ Cafés', 'bar' => '🍺 Bars & Pubs'],
                    'Hébergement' => ['hotel' => '🏨 Hôtels', 'camping' => '🏕️ Campings'],
                    'Services' => ['fuel' => '⛽ Stations essence', 'parking' => '🅿️ Parkings'],
                    'Loisirs' => ['attraction' => '🎭 Attractions', 'museum' => '🏛️ Musées', 'park' => '🌳 Parcs'],
                    'Urgences' => ['hospital' => '🏥 Hôpitaux']
                ], ['id' => 'categorySelect', 'class' => 'category-select', 'empty' => '-- Tout afficher --']) ?>
            </div>

            <button type="button" id="clearFilterBtn" class="clear-filter-btn d-none">
                ❌ Effacer
            </button>

            <div class="category-info">
                <p class="info-text">💡 <strong>Astuce :</strong> Cliquez sur la carte pour recentrer la recherche.</p>
            </div>

            <div class="category-header">
                <h4>Rayon du filtrage</h4>
            </div>

            <div class="range-container mt-10">
                <label for="radiusSlider" class="flex-between-bold">
                    Rayon : <span id="radiusValue">2</span> km
                </label>
                <?= $this->Form->control('radius', [
                    'type' => 'range', 'min' => 1, 'max' => 20, 'step' => 1, 'value' => 2,
                    'label' => false, 'id' => 'radiusSlider', 'class' => 'form-range w-100-pointer'
                ]) ?>
            </div>
        </div>
        <button type="button" class="sidebar-toggle" id="btnToggleSidebar">
            <span id="toggleIcon">◀</span>
        </button>
    </div>

    <div id="userMapIndex"></div>
</section>

<section class="featured-section">
    <h2>🌟 À la une</h2>
    <div class="roadtrips-grid">
        <?php if (isset($randomRoadtrips) && !$randomRoadtrips->isEmpty()): ?>
            <?php foreach ($randomRoadtrips as $rt): ?>
                <?= $this->Html->link(
                    '<article class="mini-card">' .
                    '<div class="card-img" style="background-image: url(\'' . $this->Url->build($rt->cover_image) . '\');"></div>' .
                    '<div class="card-info">' .
                    '<h3>' . h($rt->title) . '</h3>' .
                    '<span class="badge">Terminé</span>' .
                    '</div>' .
                    '</article>',
                    ['controller' => 'Roadtrips', 'action' => 'view', $rt->id],
                    ['escape' => false, 'class' => 'link-no-decor']
                ) ?>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="text-center-padded">
                <p class="text-muted-large">Aucun road trip à la une pour le moment.</p>
                <p>Soyez le premier à en publier un !</p>
            </div>
        <?php endif; ?>
    </div>
</section>
