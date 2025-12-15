document.addEventListener("DOMContentLoaded", function () {
    const defaultCoords = [45.75, 4.85]; // Lyon par défaut
    let userCoords = defaultCoords;
    let map;
    let currentMarkers = [];
    let userMarker = null;
    let activeFilter = null;
    const searchInput = document.getElementById('poiSearch');
    const searchResults = document.getElementById('searchResults');
    const categorySelect = document.getElementById('categorySelect');
    const clearFilterBtn = document.getElementById('clearFilterBtn');

    // Configuration étendue des catégories POI
    const poiFilters = {
        restaurant: {
            query: 'node["amenity"="restaurant"](around:2000,{lat},{lon});',
            icon: '🍽️',
            color: '#e74c3c'
        },
        fast_food: {
            query: 'node["amenity"="fast_food"](around:2000,{lat},{lon});',
            icon: '🍔',
            color: '#e67e22'
        },
        cafe: {
            query: 'node["amenity"="cafe"](around:2000,{lat},{lon});',
            icon: '☕',
            color: '#d35400'
        },
        bar: {
            query: 'node["amenity"="bar"](around:2000,{lat},{lon});node["amenity"="pub"](around:2000,{lat},{lon});',
            icon: '🍺',
            color: '#9b59b6'
        },
        hotel: {
            query: 'node["tourism"="hotel"](around:2000,{lat},{lon});',
            icon: '🏨',
            color: '#3498db'
        },
        camping: {
            query: 'node["tourism"="camp_site"](around:2000,{lat},{lon});',
            icon: '🏕️',
            color: '#27ae60'
        },
        hostel: {
            query: 'node["tourism"="hostel"](around:2000,{lat},{lon});node["tourism"="guest_house"](around:2000,{lat},{lon});',
            icon: '🛏️',
            color: '#16a085'
        },
        fuel: {
            query: 'node["amenity"="fuel"](around:2000,{lat},{lon});',
            icon: '⛽',
            color: '#f39c12'
        },
        parking: {
            query: 'node["amenity"="parking"](around:2000,{lat},{lon});',
            icon: '🅿️',
            color: '#34495e'
        },
        rest_area: {
            query: 'node["highway"="rest_area"](around:2000,{lat},{lon});node["highway"="services"](around:2000,{lat},{lon});',
            icon: '🛣️',
            color: '#7f8c8d'
        },
        atm: {
            query: 'node["amenity"="atm"](around:2000,{lat},{lon});node["amenity"="bank"](around:2000,{lat},{lon});',
            icon: '🏧',
            color: '#2ecc71'
        },
        pharmacy: {
            query: 'node["amenity"="pharmacy"](around:2000,{lat},{lon});',
            icon: '💊',
            color: '#e74c3c'
        },
        attraction: {
            query: 'node["tourism"="attraction"](around:2000,{lat},{lon});',
            icon: '🎭',
            color: '#1abc9c'
        },
        museum: {
            query: 'node["tourism"="museum"](around:2000,{lat},{lon});node["tourism"="gallery"](around:2000,{lat},{lon});',
            icon: '🏛️',
            color: '#8e44ad'
        },
        monument: {
            query: 'node["historic"="monument"](around:2000,{lat},{lon});node["historic"="memorial"](around:2000,{lat},{lon});',
            icon: '🗿',
            color: '#95a5a6'
        },
        viewpoint: {
            query: 'node["tourism"="viewpoint"](around:2000,{lat},{lon});',
            icon: '🌄',
            color: '#f39c12'
        },
        park: {
            query: 'node["leisure"="park"](around:2000,{lat},{lon});node["leisure"="garden"](around:2000,{lat},{lon});',
            icon: '🌳',
            color: '#27ae60'
        },
        beach: {
            query: 'node["natural"="beach"](around:2000,{lat},{lon});',
            icon: '🏖️',
            color: '#3498db'
        },
        supermarket: {
            query: 'node["shop"="supermarket"](around:2000,{lat},{lon});',
            icon: '🛒',
            color: '#e67e22'
        },
        mall: {
            query: 'node["shop"="mall"](around:2000,{lat},{lon});',
            icon: '🏬',
            color: '#9b59b6'
        },
        convenience: {
            query: 'node["shop"="convenience"](around:2000,{lat},{lon});',
            icon: '🏪',
            color: '#16a085'
        },
        hospital: {
            query: 'node["amenity"="hospital"](around:2000,{lat},{lon});node["amenity"="clinic"](around:2000,{lat},{lon});',
            icon: '🏥',
            color: '#c0392b'
        },
        police: {
            query: 'node["amenity"="police"](around:2000,{lat},{lon});',
            icon: '👮',
            color: '#2c3e50'
        }
    };

    // Créer une icône personnalisée
    function createCustomIcon(emoji, color) {
        return L.divIcon({
            html: `<div style="background-color: ${color}; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3); font-size: 18px;">${emoji}</div>`,
            className: 'custom-icon',
            iconSize: [32, 32],
            iconAnchor: [16, 32],
            popupAnchor: [0, -32]
        });
    }

    // Initialisation de la carte
    function initializeMap(coords) {
        map = L.map('userMap').setView(coords, 13);

        // Ajouter la couche OpenStreetMap
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19
        }).addTo(map);

        // Ajouter un marqueur pour la position actuelle
        addUserMarker(coords);

        // Gestionnaire de clic sur la carte
        map.on('click', function(e) {
            const clickedCoords = [e.latlng.lat, e.latlng.lng];
            updateUserPosition(clickedCoords);
        });
    }

    // Mettre à jour la position de l'utilisateur
    function updateUserPosition(coords) {
        userCoords = coords;
        
        // Mettre à jour le marqueur
        addUserMarker(coords);
        
        // Centrer la carte sur la nouvelle position
        map.setView(coords, map.getZoom());
        
        // Recharger les POI si un filtre est actif
        if (activeFilter) {
            loadPOI(activeFilter);
        }
    }

    // Ajouter un marqueur pour la position de l'utilisateur
    function addUserMarker(coords) {
        if (userMarker) {
            map.removeLayer(userMarker);
        }

        const userIcon = L.divIcon({
            html: '<div style="background-color: #2ecc71; width: 20px; height: 20px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3);"></div>',
            className: 'user-location-icon',
            iconSize: [20, 20],
            iconAnchor: [10, 10]
        });

        userMarker = L.marker(coords, { icon: userIcon }).addTo(map)
            .bindPopup('<b>📍 Votre position</b><br><small>Cliquez sur la carte pour changer de position</small>');
    }

    // Tenter d'obtenir la géolocalisation
    function tryGeolocation() {
        if ("geolocation" in navigator) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    userCoords = [position.coords.latitude, position.coords.longitude];
                    initializeMap(userCoords);
                },
                function(error) {
                    console.log("Géolocalisation refusée ou impossible:", error.message);
                    initMapWithFallback();
                }
            );
        } else {
            initMapWithFallback();
        }
    }

    // Initialiser avec fallback (ville utilisateur ou Lyon)
    function initMapWithFallback() {
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
    }

    // Démarrer avec tentative de géolocalisation
    tryGeolocation();

    // Fonction pour charger les POI via Overpass API
    async function loadPOI(filterType) {
        // Supprimer les anciens marqueurs POI
        clearPOIMarkers();

        // Afficher un indicateur de chargement
        showLoadingIndicator();

        const filter = poiFilters[filterType];
        const query = filter.query.replace(/{lat}/g, userCoords[0]).replace(/{lon}/g, userCoords[1]);
        
        const overpassUrl = 'https://overpass-api.de/api/interpreter';
        const overpassQuery = `[out:json][timeout:25];(${query});out body;`;

        try {
            const response = await fetch(overpassUrl, {
                method: 'POST',
                body: overpassQuery
            });

            const data = await response.json();

            // Ajouter les marqueurs pour chaque POI
            data.elements.forEach(element => {
                if (element.lat && element.lon) {
                    const marker = L.marker([element.lat, element.lon], {
                        icon: createCustomIcon(filter.icon, filter.color)
                    }).addTo(map);

                    const name = element.tags.name || 'Sans nom';
                    const address = element.tags['addr:street'] 
                        ? `${element.tags['addr:street']} ${element.tags['addr:housenumber'] || ''}`
                        : 'Adresse non disponible';

                    marker.bindPopup(`
                        <div style="min-width: 150px;">
                            <h4 style="margin: 0 0 5px 0;">${filter.icon} ${name}</h4>
                            <p style="margin: 0; font-size: 12px; color: #666;">${address}</p>
                        </div>
                    `);

                    currentMarkers.push(marker);
                }
            });

            hideLoadingIndicator();

            // Afficher le bouton pour effacer
            clearFilterBtn.style.display = 'block';

            // Afficher un message si aucun résultat
            if (data.elements.length === 0) {
                alert(`Aucun résultat trouvé dans un rayon de 2km.`);
                clearFilterBtn.style.display = 'none';
            }

        } catch (error) {
            console.error('Erreur lors du chargement des POI:', error);
            hideLoadingIndicator();
            alert('Erreur lors du chargement des données. Veuillez réessayer.');
        }
    }

    // Supprimer tous les marqueurs POI
    function clearPOIMarkers() {
        currentMarkers.forEach(marker => map.removeLayer(marker));
        currentMarkers = [];
    }

    // Indicateurs de chargement
    function showLoadingIndicator() {
        const loader = document.createElement('div');
        loader.id = 'mapLoader';
        loader.style.cssText = `
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            z-index: 1000;
            text-align: center;
        `;
        loader.innerHTML = '<div style="font-size: 24px;">⏳</div><div>Chargement...</div>';
        document.getElementById('userMap').appendChild(loader);
    }

    function hideLoadingIndicator() {
        const loader = document.getElementById('mapLoader');
        if (loader) loader.remove();
    }

    // Gestionnaire pour le menu déroulant
    categorySelect.addEventListener('change', function() {
        const selectedCategory = this.value;
        
        if (selectedCategory) {
            activeFilter = selectedCategory;
            loadPOI(selectedCategory);
        } else {
            // Si option vide sélectionnée, effacer les marqueurs
            clearPOIMarkers();
            activeFilter = null;
            clearFilterBtn.style.display = 'none';
        }
    });

    // Bouton pour effacer les marqueurs
    clearFilterBtn.addEventListener('click', function() {
        clearPOIMarkers();
        activeFilter = null;
        categorySelect.value = '';
        this.style.display = 'none';
    });

    // Recherche de lieu en temps réel
    searchInput.addEventListener('input', function () {
        const query = searchInput.value.trim();
        if (query.length > 2) {
            fetch(`https://nominatim.openstreetmap.org/search?format=json&limit=5&q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    searchResults.innerHTML = '';

                    data.forEach(item => {
                        const li = document.createElement('li');
                        li.textContent = item.display_name;
                        li.addEventListener('click', () => {
                            const lat = parseFloat(item.lat);
                            const lon = parseFloat(item.lon);
                            
                            // Mettre à jour la position avec la fonction qui recharge les filtres
                            updateUserPosition([lat, lon]);

                            searchResults.innerHTML = '';
                            searchInput.value = '';
                        });
                        searchResults.appendChild(li);
                    });
                })
                .catch(error => console.log('Erreur de recherche : ', error));
        } else {
            searchResults.innerHTML = '';
        }
    });

    // Bouton de recherche
    document.getElementById('searchBtn').addEventListener('click', function() {
        if (searchInput.value.trim().length > 2) {
            searchInput.dispatchEvent(new Event('input'));
        }
    });
});