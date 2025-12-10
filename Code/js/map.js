document.addEventListener("DOMContentLoaded", function () {
    const defaultCoords = [45.75, 4.85]; // Coordonnées par défaut (Lyon)
    let userCoords = defaultCoords;

    const userCity = document.getElementById('userCity').value;
    console.log("Ville de l'utilisateur : ", userCity); // Log de la ville

    if (userCity) {
        fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(userCity)}`)
        .then(response => response.json())
        .then(data => {
            console.log("Réponse de l'API Nominatim : ", data); // Log des données
            if (data.length > 0) {
                const lat = parseFloat(data[0].lat);
                const lon = parseFloat(data[0].lon);
                userCoords = [lat, lon];
            }
            console.log("Coordonnées de l'utilisateur : ", userCoords); // Log des coordonnées
            updateMap(userCoords);
        })
        .catch(() => {
            console.log("Erreur dans la récupération des données, utilisation des coordonnées par défaut");
            updateMap(userCoords);
        });
    } else {
        updateMap(userCoords);
    }

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            const lat = position.coords.latitude;
            const lon = position.coords.longitude;
            userCoords = [lat, lon];
            console.log("Coordonnées de géolocalisation : ", userCoords); // Log des coordonnées géolocalisées
            updateMap(userCoords);
        }, function() {
            console.log("Erreur de géolocalisation, utilisation des coordonnées par défaut");
            updateMap(userCoords);
        });
    } else {
        console.log("Géolocalisation non supportée, utilisation des coordonnées par défaut");
        updateMap(userCoords);
    }

    // Fonction pour mettre à jour la carte
    function updateMap(coords) {
        console.log("Coordonnées utilisées pour afficher la carte : ", coords); // Log des coordonnées à afficher

        // Vérifiez que l'élément #userMap existe
        const mapContainer = document.getElementById('userMap');
        if (!mapContainer) {
            console.error("Le conteneur de la carte (#userMap) n'existe pas !");
            return;
        }

        // Créer la carte avec les coordonnées récupérées
        const map = L.map(mapContainer).setView(coords, 10);

        // Ajouter la couche OpenStreetMap
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        // Ajouter un marqueur sur la carte
        L.marker(coords).addTo(map)
        .bindPopup(`Ville : ${userCity || 'Non définie'}`)
        .openPopup();

        // Fonction pour ajouter les marqueurs de lieux
        function addMarkers(type) {
            const overpassUrl = `https://overpass-api.de/api/interpreter?data=[out:json];(node["amenity"="${type}"](around:5000,${coords[0]},${coords[1]}););out;`;
            fetch(overpassUrl)
                .then(response => response.json())
                .then(data => {
                    data.elements.forEach(element => {
                        const lat = element.lat;
                        const lon = element.lon;
                        const name = element.tags ? element.tags.name : "Inconnu";

                        // Ajouter un marqueur pour chaque lieu trouvé
                        L.marker([lat, lon]).addTo(map)
                        .bindPopup(`${name} (${type})`)
                        .openPopup();
                    });
                })
                .catch(err => console.log("Erreur de récupération des données Overpass:", err));
        }

        // Événements sur les boutons de filtre
        document.getElementById('restaurantsBtn').addEventListener('click', () => {
            console.log("Affichage des restaurants...");
            addMarkers('restaurant');
        });

        document.getElementById('hotelsBtn').addEventListener('click', () => {
            console.log("Affichage des hôtels...");
            addMarkers('hotel');
        });

        document.getElementById('poiBtn').addEventListener('click', () => {
            console.log("Affichage des points d'intérêt...");
            addMarkers('tourism');
        });

        document.getElementById('churchesBtn').addEventListener('click', () => {
            console.log("Affichage des églises...");
            addMarkers('place_of_worship');
        });

        document.getElementById('attractionsBtn').addEventListener('click', () => {
            console.log("Affichage des attractions...");
            addMarkers('attraction');
        });

        document.getElementById('shopsBtn').addEventListener('click', () => {
            console.log("Affichage des magasins...");
            addMarkers('shop');
        });
    }
});
