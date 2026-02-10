<section class="hero-actions">
    <div class="hero-content">
        <h1>Prêt pour l'aventure ?</h1>
        <p>Créez votre propre itinéraire ou découvrez ceux de la communauté.</p>
        <div class="hero-buttons">
            <a href="/creationRoadTrip" class="btn-action primary">
                <span>➕</span> Créer un Road Trip
            </a>
            <a href="/roadtrips/public" class="btn-action secondary">
                <span>🌍</span> Voir les Road Trips Publics
            </a>
        </div>
    </div>
</section>

<section class="featured-section">
    <h2>🌟 À la une</h2>
    <div class="roadtrips-grid">
        <article class="mini-card">
            <div class="card-img" style="background-image: url('img/exemple1.jpg');"></div>
            <div class="card-info">
                <h3>Tour de Corse</h3>
                <span class="badge">Terminé</span>
            </div>
        </article>

        <article class="mini-card">
            <div class="card-img" style="background-image: url('img/exemple2.jpg');"></div>
            <div class="card-info">
                <h3>Alpes Suisses</h3>
                <span class="badge">Brouillon</span>
            </div>
        </article>

        <article class="mini-card">
            <div class="card-img" style="background-image: url('img/exemple3.jpg');"></div>
            <div class="card-info">
                <h3>Route 66</h3>
                <span class="badge">Terminé</span>
            </div>
        </article>
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

    <div id="userMap"></div>
</section>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('mapSidebar');
        const icon = document.getElementById('toggleIcon');

        sidebar.classList.toggle('closed');

        // Change la flèche de sens
        if (sidebar.classList.contains('closed')) {
            icon.innerHTML = "▶"; // Flèche vers la droite pour ouvrir
        } else {
            icon.innerHTML = "◀"; // Flèche vers la gauche pour fermer
        }
    }
</script>
