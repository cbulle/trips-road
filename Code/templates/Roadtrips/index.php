<section class="hero-actions">
    <div class="hero-content">
        <h1>Prêt pour l'aventure ?</h1>
        <p>Créez votre propre itinéraire ou découvrez ceux de la communauté.</p>
        <div class="hero-buttons">
            <a href="/add_r_t" class="btn-action primary">
                <span>➕</span> Créer un Road Trip
            </a>
            <a href="/public_r_t" class="btn-action secondary">
                <span>🌍</span> Voir les Road Trips Publics
            </a>
        </div>
    </div>
</section>


    <section class="featured-section">
    <h2>🌟 À la une</h2>
    
    <div class="roadtrips-grid">
        <?php if (isset($randomRoadtrips) && !$randomRoadtrips->isEmpty()): ?>
            
            <?php foreach ($randomRoadtrips as $rt): ?>
                <?php
                $urlImage = '/img/imgBase.png'; 
                
                if (!empty($rt->photo_url)) { 
                    $cheminPhysique = WWW_ROOT . 'uploads' . DS . 'roadtrips' . DS . $rt->photo_url;
                    if (file_exists($cheminPhysique)) {
                        $urlImage = '/uploads/roadtrips/' . $rt->photo_url;
                    }
                }
                ?>

                <a href="<?= $this->Url->build(['controller' => 'Roadtrips', 'action' => 'view', $rt->id]) ?>" class="mini-card-link" style="text-decoration:none; color:inherit; display:block;">
                    <article class="mini-card">
                        <div class="card-img" style="background-image: url('<?= $this->Url->build($urlImage) ?>');"></div>
                        
                        <div class="card-info">
                            <h3><?= h($rt->title) ?></h3>
                            
                            <span class="badge">Terminé</span>
                        </div>
                    </article>
                </a>
            <?php endforeach; ?>
            
        <?php else: ?>
            <div style="text-align:center; grid-column: 1 / -1; padding: 20px; background:white; border-radius:10px;">
                <p style="color:#666; font-size: 1.1rem;">Aucun road trip à la une pour le moment.</p>
                <p>Soyez le premier à en publier un !</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="full-map-container">

    <div class="floating-search">
        <div class="search-input-group">
            <input type="text" id="poiSearch" placeholder="Rechercher un lieu..."/>
            <button id="searchBtn">🔍</button>
        </div>
        <ul id="searchResults" class="searching-results"></ul>
    </div>

    <div class="map-sidebar open" id="mapSidebar">
        <button class="sidebar-toggle" onclick="toggleSidebar()">
            <span id="toggleIcon">◀</span>
        </button>

        <div class="sidebar-content">
            <div class="category-header">
                <span class="category-icon">🗺️</span>
                <h4>Filtres</h4>
            </div>

            <div class="category-select-wrapper">
                <label for="categorySelect">Catégories :</label>
                <select id="categorySelect" class="category-select">
                    <option value="">-- Tout afficher --</option>
                    <optgroup label="🍽️ Restauration">
                        <option value="restaurant">🍽️ Restaurants</option>
                        <option value="fast_food">🍔 Fast-food</option>
                        <option value="cafe">☕ Cafés</option>
                        <option value="bar">🍺 Bars & Pubs</option>
                    </optgroup>
                    <optgroup label="🏨 Hébergement">
                        <option value="hotel">🏨 Hôtels</option>
                        <option value="camping">🏕️ Campings</option>
                    </optgroup>
                    <optgroup label="⛽ Services">
                        <option value="fuel">⛽ Stations essence</option>
                        <option value="parking">🅿️ Parkings</option>
                    </optgroup>
                    <optgroup label="🎭 Loisirs">
                        <option value="attraction">🎭 Attractions</option>
                        <option value="museum">🏛️ Musées</option>
                        <option value="park">🌳 Parcs</option>
                    </optgroup>
                    <optgroup label="🏥 Urgences">
                        <option value="hospital">🏥 Hôpitaux</option>
                    </optgroup>
                </select>
            </div>

            <div class="category-info">
                <p class="info-text">💡 <strong>Astuce :</strong> Cliquez sur la carte pour recentrer la recherche.</p>
                <p class="info-zone">📍 Rayon : <strong>2 km</strong></p>
            </div>

            <button id="clearFilterBtn" class="clear-filter-btn" style="display: none;">
                ❌ Effacer
            </button>
        </div>
    </div>

    <div id="map"></div>
</section>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('mapSidebar');
        const icon = document.getElementById('toggleIcon');

        sidebar.classList.toggle('closed');

        if (sidebar.classList.contains('closed')) {
            icon.innerHTML = "▶"; 
        } else {
            icon.innerHTML = "◀";
        }
    }
</script>