/**
 * @file Interactive map with Point of Interest (POI) search
 * @description
 * This script initializes a Leaflet map providing:
 * - User geolocation
 * - Location search via Nominatim API
 * - POI display via Overpass API (OpenStreetMap)
 * - Filtering by category and search radius adjustment
 * @requires Leaflet.js
 * @requires OverpassAPI
 * @requires NominatimAPI
 */

/* ======================================================
    CONFIGURATION AND GLOBAL VARIABLES
====================================================== */

/** * Global application configuration
 * @type {Object}
 */
const appConfig = window.appConfig || {};

/** * Default coordinates (e.g., Lyon, FR)
 * @type {number[]}
 */
const defaultCoords = [appConfig.defaultLat || 45.767518, appConfig.defaultLon || 4.833534];

/** * Leaflet map instance
 * @type {L.Map}
 */
let map;

/** * Layer groups for markers
 * @type {L.LayerGroup}
 */
let searchLayer, poiLayer;

/** * Current search position coordinates
 * @type {number[]}
 */
let currentCoords = defaultCoords;

/** * Circle object visualizing the search radius
 * @type {L.Circle|null}
 */
let currentCircle = null;

/** * Search radius in meters
 * @type {number}
 */
let searchRadius = 2000;

/**
 * Overpass filter configuration definition
 * @typedef {Object} POIFilter
 * @property {string} query - The Overpass QL query string
 * @property {string} icon - Emoji character for the marker
 * @property {string} color - Hex color for the marker background
 */

/** * Dictionary of available POI filters
 * @type {Object.<string, POIFilter>}
 */
const poiFilters = {
    restaurant: { query: 'node["amenity"="restaurant"](around:{radius},{lat},{lon});', icon: '🍽️', color: '#e74c3c' },
    fast_food: { query: 'node["amenity"="fast_food"](around:{radius},{lat},{lon});', icon: '🍔', color: '#e67e22' },
    cafe: { query: 'node["amenity"="cafe"](around:{radius},{lat},{lon});', icon: '☕', color: '#d35400' },
    bar: { query: 'node["amenity"="bar"](around:{radius},{lat},{lon});node["amenity"="pub"](around:{radius},{lat},{lon});', icon: '🍺', color: '#9b59b6' },
    hotel: { query: 'node["tourism"="hotel"](around:{radius},{lat},{lon});', icon: '🏨', color: '#3498db' },
    camping: { query: 'node["tourism"="camp_site"](around:{radius},{lat},{lon});', icon: '🏕️', color: '#27ae60' },
    fuel: { query: 'node["amenity"="fuel"](around:{radius},{lat},{lon});', icon: '⛽', color: '#f39c12' },
    parking: { query: 'node["amenity"="parking"](around:{radius},{lat},{lon});', icon: '🅿️', color: '#34495e' },
    attraction: { query: 'node["tourism"="attraction"](around:{radius},{lat},{lon});', icon: '🎭', color: '#1abc9c' },
    museum: { query: 'node["tourism"="museum"](around:{radius},{lat},{lon});', icon: '🏛️', color: '#8e44ad' },
    park: { query: 'node["leisure"="park"](around:{radius},{lat},{lon});', icon: '🌳', color: '#27ae60' },
    hospital: { query: 'node["amenity"="hospital"](around:{radius},{lat},{lon});', icon: '🏥', color: '#c0392b' }
};

/* ======================================================
   GLOBAL FUNCTIONS
   ====================================================== */

/**
 * Handles the opening and closing of the filter sidebar.
 * Updates map size after transition.
 * @function toggleSidebar
 * @returns {void}
 */
function toggleSidebar() {
    const sidebar = document.getElementById('mapSidebar');
    const icon = document.getElementById('toggleIcon');
    if (sidebar) {
        sidebar.classList.toggle('closed');
        if (icon) {
            icon.innerHTML = sidebar.classList.contains('closed') ? "▶" : "◀";
        }
        if (map) {
            setTimeout(() => { map.invalidateSize(); }, 300);
        }
    }
}

/**
 * Creates a custom Leaflet DivIcon with an emoji and colored background.
 * @function createCustomIcon
 * @param {string} emoji - The emoji to display
 * @param {string} color - The background color (hex/css)
 * @returns {L.DivIcon} The created Leaflet icon
 */
function createCustomIcon(emoji, color) {
    return L.divIcon({
        html: `<div style="background-color: ${color}; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3); font-size: 16px;">${emoji}</div>`,
        className: 'custom-poi-icon',
        iconSize: [30, 30],
        iconAnchor: [15, 30],
        popupAnchor: [0, -30]
    });
}

/**
 * Initializes the map and layers.
 * Sets up geolocation and click listeners.
 * @inner
 * @function initMap
 */
function initMap() {
    const mapContainer = document.getElementById('userMapIndex');
    if (!mapContainer) return;

    map = L.map('userMapIndex').setView(currentCoords, 6);

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

    setTimeout(() => { map.invalidateSize(); }, 200);
}

/**
 * Updates the central marker position and search circle radius.
 * @inner
 * @function updateSearchPosition
 * @param {number} lat - Latitude
 * @param {number} lng - Longitude
 * @param {number|null} [zoom=null] - Optional zoom level
 */
function updateSearchPosition(lat, lng, zoom = null) {
    currentCoords = [lat, lng];
    if(searchLayer) searchLayer.clearLayers();

    const userIcon = L.divIcon({
        html: '<div style="font-size:30px; margin-top:-20px; text-align:center;">📍</div>',
        className: 'custom-pin',
        iconSize: [30, 42],
        iconAnchor: [15, 20]
    });

    L.marker([lat, lng], { icon: userIcon }).addTo(searchLayer);

    currentCircle = L.circle([lat, lng], {
        color: '#3498db',
        fillColor: '#3498db',
        fillOpacity: 0.15,
        radius: searchRadius
    }).addTo(searchLayer);

    if (zoom && map) map.setView([lat, lng], zoom);

    const categorySelect = document.getElementById('categorySelect');
    if (categorySelect && categorySelect.value) {
        loadPOI(categorySelect.value);
    }
}

/**
 * Calls the Overpass API to load POIs based on the selected category.
 * @async
 * @inner
 * @function loadPOI
 * @param {string} filterType - The category key from poiFilters
 */
async function loadPOI(filterType) {
    if(!poiLayer) return;
    poiLayer.clearLayers();
    document.body.style.cursor = 'wait';

    const filter = poiFilters[filterType];
    const clearFilterBtn = document.getElementById('clearFilterBtn');

    if (!filter) {
        document.body.style.cursor = 'default';
        return;
    }

    const query = filter.query
        .replace(/{lat}/g, currentCoords[0])
        .replace(/{lon}/g, currentCoords[1])
        .replace(/{radius}/g, searchRadius);

    const overpassUrl = 'https://overpass-api.de/api/interpreter';
    const overpassQuery = `[out:json][timeout:25];(${query});out body;`;

    try {
        const response = await fetch(overpassUrl, { method: 'POST', body: overpassQuery });
        const data = await response.json();

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
        console.error("Erreur API Overpass :", error);
    } finally {
        document.body.style.cursor = 'default';
    }
}

async function searchLocation(query, searchResultsContainer, searchInputElement) {
    if (query.length <= 2) {
        searchResultsContainer.innerHTML = '';
        return;
    }

    try {
        const response = await fetch(`https://nominatim.openstreetmap.org/search?format=json&limit=5&q=${encodeURIComponent(query)}`);
        const data = await response.json();

        searchResultsContainer.innerHTML = '';
        data.forEach(item => {
            const li = document.createElement('li');
            li.textContent = item.display_name;
            li.addEventListener('click', () => {
                updateSearchPosition(parseFloat(item.lat), parseFloat(item.lon), 14);
                searchResultsContainer.innerHTML = '';
                searchInputElement.value = '';
            });
            searchResultsContainer.appendChild(li);
        });
    } catch (error) {
        console.error("Erreur API Nominatim :", error);
    }
}

/* ======================================================
   INITIALIZATION AND DOM LOGIC
   ====================================================== */

document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById('poiSearchIndex');
    const searchResults = document.getElementById('searchResultsIndex');
    const categorySelect = document.getElementById('categorySelect');
    const clearFilterBtn = document.getElementById('clearFilterBtn');
    const radiusSlider = document.getElementById('radiusSlider');
    const radiusValueSpan = document.getElementById('radiusValue');

    initMap();

    if (radiusSlider) {
        radiusSlider.addEventListener('input', function() {
            const km = this.value;
            if(radiusValueSpan) radiusValueSpan.textContent = km;
            searchRadius = km * 1000;
            if (currentCircle) currentCircle.setRadius(searchRadius);
        });

        radiusSlider.addEventListener('change', function() {
            if (categorySelect && categorySelect.value) {
                loadPOI(categorySelect.value);
            }
        });
    }

    if (categorySelect) {
        categorySelect.addEventListener('change', function() {
            if (this.value) {
                loadPOI(this.value);
            } else {
                poiLayer.clearLayers();
                if (clearFilterBtn) clearFilterBtn.style.display = 'none';
            }
        });
    }

    if (clearFilterBtn) {
        clearFilterBtn.addEventListener('click', function() {
            poiLayer.clearLayers();
            if (categorySelect) categorySelect.value = "";
            this.style.display = 'none';
        });
    }

    if (searchInput && searchResults) {
        searchInput.addEventListener('input', function() {
            const query = this.value.trim();
            searchLocation(query, searchResults, searchInput);
        });
    }
});
