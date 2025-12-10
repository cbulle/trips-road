document.addEventListener("DOMContentLoaded", function () {
    const defaultCoords = [45.75, 4.85]; // Coordonnées par défaut (Lyon)
    let userCoords = defaultCoords;
    let map;
    let currentMarkers = [];
    const searchInput = document.getElementById('poiSearch');
    const searchResults = document.getElementById('searchResults');

    // Initialisation de la carte
    function initializeMap(coords) {
        map = L.map('map').setView(coords, 12); // Zoom par défaut à 12

        // Ajouter la couche OpenStreetMap
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        // Ajouter un marqueur pour la ville de l'utilisateur
        L.marker(coords).addTo(map)
            .bindPopup('Ville : ' + userCity)
            .openPopup();
    }

    // Initialisation de la carte en fonction de la ville
    const userCity = document.getElementById('userCity').value;
    if (userCity) {
        fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(userCity)}`)
            .then(response => response.json())
            .then(data => {
                if (data.length > 0) {
                    const lat = parseFloat(data[0].lat);
                    const lon = parseFloat(data[0].lon);
                    userCoords = [lat, lon];
                }
                initializeMap(userCoords);
            })
            .catch(() => initializeMap(userCoords));
    } else {
        initializeMap(userCoords);
    }

    // Fonction pour récupérer les résultats de la recherche en temps réel
    searchInput.addEventListener('input', function () {
        const query = searchInput.value.trim();
        if (query.length > 2) {  // Ne déclencher la recherche que si le texte est assez long
            fetch(`https://nominatim.openstreetmap.org/search?format=json&limit=5&q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    // Effacer les anciens résultats
                    searchResults.innerHTML = '';

                    // Ajouter les nouveaux résultats à la liste
                    data.forEach(item => {
                        const li = document.createElement('li');
                        li.textContent = item.display_name;
                        li.addEventListener('click', () => {
                            const lat = parseFloat(item.lat);
                            const lon = parseFloat(item.lon);
                            userCoords = [lat, lon];

                            // Centrer la carte sur le lieu et ajouter un marqueur rouge
                            map.setView(userCoords, 14);
                            L.marker(userCoords, {
                                icon: L.icon({
                                    iconUrl: 'https://upload.wikimedia.org/wikipedia/commons/a/a5/Red_dot.svg',
                                    iconSize: [25, 25],
                                    iconAnchor: [12, 12],
                                })
                            }).addTo(map)
                                .bindPopup(`Lieu trouvé : ${item.display_name}`)
                                .openPopup();

                            // Effacer les résultats de recherche
                            searchResults.innerHTML = '';
                        });
                        searchResults.appendChild(li);
                    });
                })
                .catch(error => console.log('Erreur de recherche : ', error));
        } else {
            // Si la recherche est vide ou trop courte, vider les résultats
            searchResults.innerHTML = '';
        }
    });
});
