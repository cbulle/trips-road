<?php
require_once __DIR__ . '/include/init.php';
$userId = $_SESSION['utilisateur']['id'] ?? null;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Road Trip Planner</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.7.1/dist/leaflet.css" />
    <script src="https://cdn.jsdelivr.net/npm/leaflet@1.7.1/dist/leaflet.js"></script>
    <?php
    if ($userId !== null) {
        echo "<script>const currentUserId = " . json_encode($userId) . ";</script>";
    } else {
        echo "<script>const currentUserId = null;</script>";
    }
    ?>
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/style.css"> 
</head>
<body>
   
<?php     
include_once __DIR__ . "/modules/header.php"
?>

<main class="main-index">
    <div class="index_container">
        <h2>Bienvenue sur Trips & Roads !</h2>
        <p>Planifiez et partagez vos road trips facilement.</p>
        <a href="/creationRoadTrip"><button type="submit">Créer un nouveau Road Trip</button></a>
    </div>

    <div class="search-container">
        <input type="text" id="poiSearch" placeholder="Rechercher un lieu..." />
        <button id="searchBtn">🔍 Rechercher</button>
        <ul id="searchResults" class="searching-results"></ul>
    </div>

    <div id="mapContainer">
        <h3>📍 Carte interactive</h3>
        
        <div class="map-wrapper">
            <div class="category-sidebar">
                <div class="category-header">
                    <span class="category-icon">🗺️</span>
                    <h4>Points d'intérêt</h4>
                </div>
                
                <div class="category-select-wrapper">
                    <label for="categorySelect">Sélectionnez une catégorie :</label>
                    <select id="categorySelect" class="category-select">
                        <option value="">-- Choisir une catégorie --</option>
                        <optgroup label="🍽️ Restauration">
                            <option value="restaurant">🍽️ Restaurants</option>
                            <option value="fast_food">🍔 Fast-food</option>
                            <option value="cafe">☕ Cafés</option>
                            <option value="bar">🍺 Bars & Pubs</option>
                        </optgroup>
                        <optgroup label="🏨 Hébergement">
                            <option value="hotel">🏨 Hôtels</option>
                            <option value="camping">🏕️ Campings</option>
                            <option value="hostel">🛏️ Auberges</option>
                        </optgroup>
                        <optgroup label="⛰️ Chemins">
                            <option value="ferrata">⛰️ Via ferrata</option>
                            <option value="rando"> 🥾 Randonnées</option>
                        </optgroup>
                        <optgroup label="⛽ Services">
                            <option value="fuel">⛽ Stations essence</option>
                            <option value="parking">🅿️ Parkings</option>
                            <option value="rest_area">🛣️ Aires de repos</option>
                            <option value="atm">🏧 Distributeurs (ATM)</option>
                            <option value="pharmacy">💊 Pharmacies</option>
                        </optgroup>
                        <optgroup label="🎭 Tourisme & Loisirs">
                            <option value="attraction">🎭 Attractions</option>
                            <option value="museum">🏛️ Musées</option>
                            <option value="monument">🗿 Monuments</option>
                            <option value="viewpoint">🌄 Points de vue</option>
                            <option value="park">🌳 Parcs & Jardins</option>
                            <option value="zoo">🐘 Zoo</option>
                            <option value="beach">🏖️ Plages</option>
                        </optgroup>
                        <optgroup label="🛒 Shopping">
                            <option value="supermarket">🛒 Supermarchés</option>
                            <option value="mall">🏬 Centres commerciaux</option>
                            <option value="convenience">🏪 Supérettes</option>
                            <option value="shop_food">🛒 Nourriture</option>

                        </optgroup>
                        <optgroup label="🏥 Urgences">
                            <option value="hospital">🏥 Hôpitaux</option>
                            <option value="police">👮 Postes de police</option>
                        </optgroup>
                    </select>
                </div>

                <div class="category-info">
                    <p class="info-text">💡 <strong>Astuce :</strong> Cliquez sur la carte pour changer votre position de recherche</p>
                    <p class="info-zone">📍 Zone de recherche : <strong>2 km</strong> autour de votre position</p>
                </div>

                <button id="clearFilterBtn" class="clear-filter-btn" style="display: none;">
                    ❌ Effacer les marqueurs
                </button>
            </div>

            <div id="userMap" style="height: 600px; flex: 1; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1);"></div>
        </div>
    </div>
</main>

      
     
<?php     
include_once __DIR__ . "/modules/footer.php"
?>
<input type="hidden" id="userCity" value="<?php echo isset($_SESSION['utilisateur']['ville']) ? $_SESSION['utilisateur']['ville'] : ''; ?>">
<script src="https://cdn.jsdelivr.net/npm/leaflet@1.7.1/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
<script src="https://unpkg.com/leaflet-markercluster/dist/leaflet.markercluster.js"></script>
<script src="js/index.js"></script>



</body>
</html>
