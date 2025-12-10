<?php
require_once __DIR__ . '/modules/init.php';
$userId = $_SESSION['utilisateur']['id'] ?? null;
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
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
<script>
document.addEventListener("DOMContentLoaded", function () {
    const defaultCoords = [45.75, 4.85]; 
    let userCoords = defaultCoords;  

    const userCity = "<?php echo isset($_SESSION['utilisateur']['ville']) ? $_SESSION['utilisateur']['ville'] : ''; ?>";
    
    if (userCity) {
        
        // pas de solution encore 
    }

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            const lat = position.coords.latitude;
            const lon = position.coords.longitude;
            userCoords = [lat, lon]; 
            updateMap(userCoords);
        }, function() {
            updateMap(userCoords);
        });
    } else {
        updateMap(userCoords);
    }

    function updateMap(coords) {
        const map = L.map('userMap').setView(coords, 10);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        L.marker(coords).addTo(map)
        .bindPopup(`Ville : ${userCity || 'Non définie'}`)
        .openPopup();
    }
});
</script>



</body>
</html>
