<?php
require_once __DIR__ . '/modules/init.php';
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
    <link rel="stylesheet" href="css/style.css"> 
</head>
<body>
   
<?php     
include_once __DIR__ . "/modules/header.php"
?>

<main class = "main-index" >
    <div class="index_container">
        <h2>Bienvenue sur Trips & Roads !</h2>
        <p>Planifiez et partagez vos road trips facilement.</p>
        <a href="creationRoadTrip.php"><button type="submit">Créer un nouveau Road Trip</button></a>
    </div>
    <div class="search-container">
    <input type="text" id="poiSearch" placeholder="Rechercher un lieu..." />
    <button id="searchBtn">Rechercher</button>
</div>

<div class="filter-buttons">
    <button id="restaurantBtn">Restaurant</button>
    <button id="hotelBtn">Hôtel</button>
    <button id="poiBtn">Attractions</button>
    <button id="shopBtn">Magasin</button>
</div>

    <div id="mapContainer" >
        <h3>Carte des environs</h3>
        <div id="userMap" ></div>
    </div>
</main>

<?php     
include_once __DIR__ . "/modules/aside.php"
?>       
     
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
