document.addEventListener("DOMContentLoaded", function () {
    const appConfig = window.appConfig || {};
    const defaultCoords = [appConfig.defaultLat || 45.767518, appConfig.defaultLon || 4.833534];

    let map, searchLayer, poiLayer;
    let currentCoords = defaultCoords;
    let currentCircle = null;
    let searchRadius = 2000;

    const searchInput = document.getElementById('poiSearchIndex');
    const searchResults = document.getElementById('searchResultsIndex');
    const categorySelect = document.getElementById('categorySelect');
    const clearFilterBtn = document.getElementById('clearFilterBtn');
    const radiusSlider = document.getElementById('radiusSlider');
    const radiusValueSpan = document.getElementById('radiusValue');

    const poiFilters = {
        restaurant: { query: 'node["amenity"="restaurant"](around:{radius},{lat},{lon});', icon: '🍽️', color: '#e74c3c' },
        fast_food: { query: 'node["amenity"="fast_food"](around:{radius},{lat},{lon});', icon: '🍔', color: '#e67e22' },
        cafe: { query: 'node["amenity"="cafe"](around:{radius},{lat},{lon});', icon: '☕', color: '#d35400' },
        bar: { query: 'node["amenity"="bar"](around:{radius},{lat},{lon});node["amenity"="pub"](around:{radius},{lat},{lon});', icon: '🍺', color: '#9b59b6' },
        hotel: { query: 'node["tourism"="hotel"](around:{radius},{lat},{lon});', icon: '🏨', color: '#3498db' },
        camping: { query: 'node["tourism"="camp_site"](around:{radius},{lat},{lon});', icon: '🏕️', color: '#27ae60' },
        fuel: { query: 'node["amenity"="fuel"](around:{radius},{lat},{lon});', icon: '⛽', color: '#f39c12' },
        parking: { query: 'node["amenity"="parking"](around:{radius},{lat},{lon});', icon: '🅿️', color: '#34495e' },
        atm: { query: 'node["amenity"="atm"](around:{radius},{lat},{lon});', icon: '🏧', color: '#2ecc71' },
        pharmacy: { query: 'node["amenity"="pharmacy"](around:{radius},{lat},{lon});', icon: '💊', color: '#c0392b' },
        attraction: { query: 'node["tourism"="attraction"](around:{radius},{lat},{lon});', icon: '🎭', color: '#1abc9c' },
        museum: { query: 'node["tourism"="museum"](around:{radius},{lat},{lon});', icon: '🏛️', color: '#8e44ad' },
        park: { query: 'node["leisure"="park"](around:{radius},{lat},{lon});', icon: '🌳', color: '#27ae60' },
        supermarket: { query: 'node["shop"="supermarket"](around:{radius},{lat},{lon});', icon: '🛒', color: '#e67e22' },
        hospital: { query: 'node["amenity"="hospital"](around:{radius},{lat},{lon});', icon: '🏥', color: '#c0392b' }
    };

    function initMap() {
        if (!document.getElementById('userMap')) return;

        map = L.map('userMap').setView(currentCoords, 6);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap',
            maxZoom: 19
        }).addTo(map);

        searchLayer = L.layerGroup().addTo(map);
        poiLayer = L.layerGroup().addTo(map);

        if ("geolocation" in navigator) {
            navigator.geolocation.getCurrentPosition(
                pos => updateSearchPosition(pos.coords.latitude, pos.coords.longitude, 13),
                () => updateSearchPosition(currentCoords[0], currentCoords[1], 6)
            );
        } else {
            updateSearchPosition(currentCoords[0], currentCoords[1], 6);
        }

        map.on('click', e => updateSearchPosition(e.latlng.lat, e.latlng.lng));
    }

    function updateSearchPosition(lat, lng, zoom = null) {
        currentCoords = [lat, lng];
        searchLayer.clearLayers();

        const userIcon = L.divIcon({
            html: '<div style="font-size:30px; margin-top:-20px; text-align:center;">📍</div>',
            className: 'custom-pin',
            iconSize: [30, 42],
            iconAnchor: [15, 20]
        });

        L.marker([lat, lng], { icon: userIcon }).addTo(searchLayer)

        currentCircle = L.circle([lat, lng], {
            color: '#3498db',
            fillColor: '#3498db',
            fillOpacity: 0.15,
            radius: searchRadius
        }).addTo(searchLayer);

        if (zoom) map.setView([lat, lng], zoom);

        if (categorySelect.value) loadPOI(categorySelect.value);
    }

    async function loadPOI(filterType) {
        poiLayer.clearLayers();
        document.body.style.cursor = 'wait';

        const filter = poiFilters[filterType];
        if (!filter) return;

        const query = filter.query
            .replace(/{lat}/g, currentCoords[0])
            .replace(/{lon}/g, currentCoords[1])
            .replace(/{radius}/g, searchRadius);

        const overpassUrl = 'https://overpass-api.de/api/interpreter';
        const overpassQuery = `[out:json][timeout:25];(${query});out body;`;

        try {
            const response = await fetch(overpassUrl, { method: 'POST', body: overpassQuery });
            const data = await response.json();
            document.body.style.cursor = 'default';

            if (data.elements.length > 0) {
                if (clearFilterBtn) clearFilterBtn.style.display = 'block';
                data.elements.forEach(element => {
                    if (element.lat && element.lon) {
                        const icon = createCustomIcon(filter.icon, filter.color);
                        L.marker([element.lat, element.lon], { icon: icon })
                         .addTo(poiLayer)
                         .bindPopup(`<b>${filter.icon} ${element.tags.name || 'Sans nom'}</b>`);
                    }
                });
            }
        } catch (error) {
            console.error(error);
            document.body.style.cursor = 'default';
        }
    }

    function createCustomIcon(emoji, color) {
        return L.divIcon({
            html: `<div style="background-color: ${color}; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3); font-size: 16px;">${emoji}</div>`,
            className: 'custom-poi-icon',
            iconSize: [30, 30],
            iconAnchor: [15, 30],
            popupAnchor: [0, -30]
        });
    }


    if (radiusSlider) {
        radiusSlider.addEventListener('input', function() {
            const km = this.value;
            if(radiusValueSpan) radiusValueSpan.textContent = km;
            searchRadius = km * 1000;
            if (currentCircle) currentCircle.setRadius(searchRadius);
        });

        radiusSlider.addEventListener('change', function() {
            if (categorySelect.value) loadPOI(categorySelect.value);
        });
    }

    if (categorySelect) {
        categorySelect.addEventListener('change', function() {
            if (this.value) loadPOI(this.value);
            else {
                poiLayer.clearLayers();
                if (clearFilterBtn) clearFilterBtn.style.display = 'none';
            }
        });
    }

    if (clearFilterBtn) {
        clearFilterBtn.addEventListener('click', function() {
            poiLayer.clearLayers();
            categorySelect.value = "";
            this.style.display = 'none';
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.trim();
            if (query.length > 2) {
                fetch(`https://nominatim.openstreetmap.org/search?format=json&limit=5&q=${encodeURIComponent(query)}`)
                    .then(r => r.json())
                    .then(data => {
                        searchResults.innerHTML = '';
                        data.forEach(item => {
                            const li = document.createElement('li');
                            li.textContent = item.display_name;
                            li.style.cssText = 'cursor:pointer; padding:5px; border-bottom:1px solid #eee';
                            li.addEventListener('click', () => {
                                updateSearchPosition(parseFloat(item.lat), parseFloat(item.lon), 14);
                                searchResults.innerHTML = '';
                                searchInput.value = '';
                            });
                            searchResults.appendChild(li);
                        });
                    });
            } else { searchResults.innerHTML = ''; }
        });
    }

    initMap();
});
