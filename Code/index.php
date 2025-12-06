<?php
require_once __DIR__ . '/modules/init.php';

?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Road Trip Planner</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.7.1/dist/leaflet.css" />
<script src="https://cdn.jsdelivr.net/npm/leaflet@1.7.1/dist/leaflet.js"></script>

   
    <link rel="stylesheet" href="css/style.css"> 
</head>
<body>
   
<?php     
include_once __DIR__ . "/modules/header.php"
?>
        <div class="index_container">
            <h2>Bienvenue sur Trips & Roads !</h2>
            <p>Planifiez et partagez vos road trips facilement.</p>
            <a href="creationRoadTrip.php"><button type="submit">Créer un nouveau Road Trip</button></a>
        </div>
</main>

 <?php     
include_once __DIR__ . "/modules/aside.php"
?>       
   
   <div id="mapContainer" style="margin: 20px 0;">
    <h3>Carte des environs</h3>
    <div id="userMap" ></div>
</div>



   
<?php     
include_once __DIR__ . "/modules/footer.php"
?>
<script>
document.addEventListener("DOMContentLoaded", function () {
    // Coordonnée par défaut (Lyon) si l'utilisateur n'est ni connecté, ni géolocalisé
    const defaultCoords = [45.75, 4.85]; // Lyon par défaut
    let userCoords = defaultCoords;  // Coordonnée initiale par défaut

    // Vérifier si l'utilisateur est connecté et a une ville
    const userCity = "<?php echo isset($_SESSION['utilisateur']['ville']) ? $_SESSION['utilisateur']['ville'] : ''; ?>";
    
    if (userCity) {
        

        // Vérifier si la ville de l'utilisateur est dans notre base de données de villes
        userCoords = cityCoords[userCity] || defaultCoords; // Sinon, on garde Lyon par défaut
    }

    // Si la géolocalisation est possible, utiliser la position actuelle de l'utilisateur
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            const lat = position.coords.latitude;
            const lon = position.coords.longitude;
            userCoords = [lat, lon]; // Mettre à jour la position de la carte avec la géolocalisation
            updateMap(userCoords);
        }, function() {
            // Si l'utilisateur refuse la géolocalisation, on utilise les coordonnées par défaut
            updateMap(userCoords);
        });
    } else {
        // Si la géolocalisation n'est pas supportée, utiliser les coordonnées par défaut
        updateMap(userCoords);
    }

    // Fonction pour initialiser la carte avec les coordonnées données
    function updateMap(coords) {
        // Création de la carte
        const map = L.map('userMap').setView(coords, 10);

        // Ajouter le fond OpenStreetMap
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        // Ajouter un marqueur pour la position de l'utilisateur
         L.marker(defaultCoords).addTo(map)
        .bindPopup(`Ville : ${userCity}`)
        .openPopup();
    }
});
</script>


</body>
</html>
