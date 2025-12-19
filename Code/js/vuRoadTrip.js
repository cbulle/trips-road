document.addEventListener('DOMContentLoaded', () => {
    initGlobalMap();
    calculerTousLesSegments();
});

const mapInstances = {};

const colorsPalette = [
    '#e6194b', '#3cb44b', '#ffe119', '#4363d8', '#f58231', 
    '#911eb4', '#42d4f4', '#f032e6', '#bfef45', '#fabed4', 
    '#469990', '#dcbeff', '#9A6324', '#fffac8', '#800000', 
    '#aaffc3', '#808000', '#ffd8b1', '#000075', '#808080'
];

/**
 * 1. GESTION DE LA CARTE GLOBALE (AVEC CLUSTERS)
 */
async function initGlobalMap() {
    if (!document.getElementById('map-global')) return;

    const map = L.map('map-global');
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    // --- NOUVEAU : Création du groupe de clusters ---
    // On désactive le zoom au clic sur un cluster si on veut (spiderfyOnMaxZoom: false)
    // Ici on laisse par défaut.
    const markersCluster = L.markerClusterGroup({
        maxClusterRadius: 50, // Rayon en pixels pour regrouper (plus petit = moins de regroupement)
        spiderfyOnMaxZoom: true,
        showCoverageOnHover: false,
        zoomToBoundsOnClick: true
    });

    // On ajoute le groupe vide à la carte
    map.addLayer(markersCluster);

    const bounds = [];
    let colorIndex = 0;

    for (const id in roadTripData) {
        const data = roadTripData[id];
        data.color = colorsPalette[colorIndex % colorsPalette.length];

        // On passe le groupe de cluster à la fonction drawRoute
        await drawRoute(map, data, data.color, false, true, markersCluster);

        bounds.push([data.depart.lat, data.depart.lon]);
        bounds.push([data.arrivee.lat, data.arrivee.lon]);
        if (data.sousEtapes && data.sousEtapes.length > 0) {
            data.sousEtapes.forEach(se => bounds.push([se.lat, se.lon]));
        }
        colorIndex++;
    }

    if (bounds.length > 0) {
        map.fitBounds(bounds, { padding: [50, 50] });
    } else {
        map.setView([46.603354, 1.888334], 6);
    }
}

/**
 * 2. CARTE INDIVIDUELLE (SANS CLUSTERS)
 */
async function initStepMap(id) {
    if (mapInstances[id]) {
        setTimeout(() => mapInstances[id].invalidateSize(), 100);
        return;
    }

    const divId = 'map-trajet-' + id;
    if (!document.getElementById(divId)) return;

    const data = roadTripData[id];
    
    const map = L.map(divId);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    mapInstances[id] = map;
    const color = data.color || '#0B667D';

    // Ici on passe null pour le clusterGroup car on veut voir tous les points
    await drawRoute(map, data, color, true, false, null);
}

/**
 * 3. DESSINER UNE ROUTE & MARQUEURS
 * @param {Object} clusterGroup (Optionnel) Si fourni, on ajoute les marqueurs dedans au lieu de la carte
 */
async function drawRoute(map, data, color, fitBounds, useOffset, clusterGroup) {
    const latDep = data.depart.lat;
    const lonDep = data.depart.lon;
    const latArr = data.arrivee.lat;
    const lonArr = data.arrivee.lon;

    let profile = 'driving';
    if (data.mode === 'velo' || data.mode === 'vélo') profile = 'cycling';
    if (data.mode === 'marche' || data.mode === 'à pied') profile = 'walking';

    let coordinates = `${lonDep},${latDep}`;
    if (data.sousEtapes && data.sousEtapes.length > 0) {
        data.sousEtapes.forEach(se => {
            coordinates += `;${se.lon},${se.lat}`;
        });
    }
    coordinates += `;${lonArr},${latArr}`;

    let stepCounter = 1;

    // --- CRÉATION DES MARQUEURS ---
    // Note: On passe 'clusterGroup' à la fonction helper

    // A. DÉPART
    createNumberedMarker(
        map, latDep, lonDep, stepCounter++, color, 
        `<b>Départ:</b> ${data.depart.nom}`, 
        useOffset ? 'left' : null,
        clusterGroup
    );

    // B. SOUS-ÉTAPES
    if (data.sousEtapes && data.sousEtapes.length > 0) {
        data.sousEtapes.forEach((se) => {
            let popupContent = `<b>📍 ${se.nom}</b>`;
            if (se.heure) popupContent += `<br>🕐 ${se.heure}`;
            if (se.remarque) {
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = se.remarque;
                const txt = tempDiv.textContent || "";
                popupContent += `<br><em>${txt.substring(0,80)}...</em>`;
            }

            createNumberedMarker(map, se.lat, se.lon, stepCounter++, color, popupContent, null, clusterGroup);
        });
    }

    // C. ARRIVÉE
    createNumberedMarker(
        map, latArr, lonArr, stepCounter, color, 
        `<b>Arrivée:</b> ${data.arrivee.nom}`, 
        useOffset ? 'right' : null,
        clusterGroup
    );

    // --- TRACÉ LIGNE (La ligne ne va JAMAIS dans le cluster, toujours sur la map) ---
    const url = `https://router.project-osrm.org/route/v1/${profile}/${coordinates}?overview=full&geometries=geojson`;

    try {
        const response = await fetch(url);
        const json = await response.json();
        let geoData;

        if (json.code === 'Ok') {
            geoData = json.routes[0].geometry;
        } else {
            geoData = {
                "type": "LineString",
                "coordinates": [[lonDep, latDep], [lonArr, latArr]]
            };
        }

        const layer = L.geoJSON(geoData, {
            style: { color: color, weight: 6, opacity: 0.8 }
        }).addTo(map);

        if (fitBounds) {
            map.fitBounds(layer.getBounds(), { padding: [30, 30] });
        }

    } catch (e) {
        console.error("Erreur API OSRM", e);
    }
}

/**
 * Helper modifié pour gérer le Cluster
 */
function createNumberedMarker(map, lat, lon, number, color, popupText, offsetDirection, clusterGroup) {
    
    let offsetClass = '';
    if (offsetDirection === 'left') offsetClass = 'marker-offset-left';
    if (offsetDirection === 'right') offsetClass = 'marker-offset-right';

    const icon = L.divIcon({
        className: `custom-marker-number ${offsetClass}`, 
        html: `<div class="marker-pin" style="background-color: ${color};">${number}</div>`,
        iconSize: [30, 30],
        iconAnchor: [15, 15],
        popupAnchor: [0, -15]
    });

    const marker = L.marker([lat, lon], { icon: icon }).bindPopup(popupText);

    // LOGIQUE CLÉ : Si un groupe de cluster existe (Carte globale), on ajoute dedans.
    // Sinon (Carte étape), on ajoute direct à la map.
    if (clusterGroup) {
        clusterGroup.addLayer(marker);
    } else {
        marker.addTo(map);
    }
}

// ... Le reste du code (toggleTrajet, calculs...) reste identique ...
// Copie-colle la fin de ton fichier précédent à partir de "window.toggleTrajet" ici.
// Ou si tu veux je te remets le bloc complet ci-dessous :

window.toggleTrajet = function(id) {
    const container = document.getElementById('sous-etapes-' + id);
    const card = document.getElementById('card-' + id);
    const mapGlobal = document.getElementById('map-global');
    
    if (!container || !card) return;

    const isActive = container.classList.contains('active');

    if (isActive) {
        container.classList.remove('active');
        card.classList.remove('active');
        checkAndToggleGlobalMap();
    } else {
        container.classList.add('active');
        card.classList.add('active');
        if (mapGlobal) mapGlobal.style.display = 'none';
        setTimeout(() => { initStepMap(id); }, 200);
    }
};

function checkAndToggleGlobalMap() {
    const mapGlobal = document.getElementById('map-global');
    if (!mapGlobal) return;
    const activeCards = document.querySelectorAll('.card-vu.active');
    if (activeCards.length === 0) {
        mapGlobal.style.display = 'block';
    } else {
        mapGlobal.style.display = 'none';
    }
}

function calculerTousLesSegments() {
    const segments = document.querySelectorAll('.segment-info');
    segments.forEach((segment, index) => {
        setTimeout(() => { calculerSegment(segment); }, index * 500);
    });
}

async function calculerSegment(segment) {
    const latDep = parseFloat(segment.dataset.latDep);
    const lonDep = parseFloat(segment.dataset.lonDep);
    const latArr = parseFloat(segment.dataset.latArr);
    const lonArr = parseFloat(segment.dataset.lonArr);
    const mode = segment.dataset.mode || 'voiture';
    
    const distEl = segment.querySelector('.segment-distance');
    const timeEl = segment.querySelector('.segment-time');
    
    if (!latDep || !lonDep || !latArr || !lonArr) {
        distEl.textContent = 'N/A'; timeEl.textContent = 'N/A'; return;
    }
    
    const profiles = { 'voiture': 'driving', 'velo': 'cycling', 'vélo': 'cycling', 'marche': 'walking', 'à pied': 'walking' };
    const profile = profiles[mode.toLowerCase()] || 'driving';
    const url = `https://router.project-osrm.org/route/v1/${profile}/${lonDep},${latDep};${lonArr},${latArr}?overview=false`;
    
    try {
        const response = await fetch(url);
        const data = await response.json();
        
        if (data.code === 'Ok' && data.routes && data.routes.length > 0) {
            const route = data.routes[0];
            const distanceKm = (route.distance / 1000).toFixed(1);
            distEl.textContent = `${distanceKm} km`;
            const durationSec = route.duration;
            const heures = Math.floor(durationSec / 3600);
            const minutes = Math.floor((durationSec % 3600) / 60);
            let tempsTexte = (heures > 0) ? `${heures}h${minutes > 0 ? minutes.toString().padStart(2, '0') : ''}` : `${minutes}min`;
            timeEl.textContent = tempsTexte;
            segment.classList.add('segment-calculated');
        } else {
            distEl.textContent = '—'; timeEl.textContent = '—';
        }
    } catch (error) {
        console.error('Erreur calcul segment:', error);
        distEl.textContent = 'Err'; timeEl.textContent = 'Err';
    }
}
    /*==================================
    Mes road 
    ================================*/

function closeShareModal() {
    document.getElementById('shareModal').classList.remove('active');
    // Retirer le paramètre de l'URL
    window.history.replaceState({}, document.title, window.location.pathname);
}

function copyShareUrl() {
    const input = document.getElementById('shareUrl');
    navigator.clipboard.writeText(input.value).then(() => {
        const success = document.getElementById('copySuccess');
        success.style.display = 'block';
        
        setTimeout(() => {
            success.style.display = 'none';
        }, 3000);
    }).catch(err => {
        console.error('Erreur lors de la copie du texte : ', err);
    });
}

// Fermer le modal en cliquant à l'extérieur
document.addEventListener('click', function(event) {
    const modal = document.getElementById('shareModal');
    if (modal && !modal.contains(event.target)) {
        closeShareModal();
    }
});
