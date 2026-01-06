document.addEventListener('DOMContentLoaded', () => {
    // On lance la carte globale
    initGlobalMap();
    // On lance le calcul des distances/temps pour l'affichage texte
    calculerTousLesSegments();
});

const mapInstances = {};
const colorsPalette = [
    '#e6194b', '#3cb44b', '#ffe119', '#4363d8', '#f58231', 
    '#911eb4', '#42d4f4', '#f032e6', '#bfef45', '#fabed4', 
    '#469990', '#dcbeff', '#9A6324', '#fffac8', '#800000'
];

/**
 * Récupère les données proprement (Array ou Object)
 */
function getTrajetData(id) {
    if (!roadTripData) return null;
    if (Array.isArray(roadTripData)) {
        // Le double égal (==) est important pour matcher string "12" et int 12
        return roadTripData.find(t => t.id == id);
    } else {
        return roadTripData[id];
    }
}

/**
 * 1. CARTE GLOBALE (D'ENSEMBLE)
 */
async function initGlobalMap() {
    if (!document.getElementById('map-global')) return;
    if (typeof roadTripData === 'undefined' || !roadTripData) return;

    const map = L.map('map-global');
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    const markersCluster = L.markerClusterGroup({
        maxClusterRadius: 40,
        spiderfyOnMaxZoom: true,
        showCoverageOnHover: false,
        zoomToBoundsOnClick: true
    });
    map.addLayer(markersCluster);

    const bounds = [];
    let colorIndex = 0;
    const allTrajets = Object.values(roadTripData);

    for (const data of allTrajets) {
        // Sécurité : si pas de coordonnées, on passe au suivant sans planter
        if (!data.hasCoords) {
            console.warn(`Trajet ${data.id} ignoré : coordonnées manquantes.`);
            continue;
        }

        data.color = colorsPalette[colorIndex % colorsPalette.length];
        
        try {
            // On dessine le trajet
            await drawRoute(map, data, data.color, false, true, markersCluster);

            // On ajoute les points aux limites pour centrer la carte
            bounds.push([parseFloat(data.depart.lat), parseFloat(data.depart.lon)]);
            bounds.push([parseFloat(data.arrivee.lat), parseFloat(data.arrivee.lon)]);
            
            if (data.sousEtapes && data.sousEtapes.length > 0) {
                data.sousEtapes.forEach(se => {
                    bounds.push([parseFloat(se.lat), parseFloat(se.lon)]);
                });
            }
        } catch (err) {
            console.error("Erreur affichage trajet global:", err);
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
 * 2. CARTE INDIVIDUELLE (VISUALISATION SEULE)
 */
async function initStepMap(id) {
    const data = getTrajetData(id);
    const divId = 'map-trajet-' + id;
    const divElement = document.getElementById(divId);
    
    if (!divElement) return;

    // CAS 1 : Pas de données JS du tout (ne devrait plus arriver avec le fix PHP)
    if (!data) {
        divElement.innerHTML = '<div style="padding:20px; text-align:center; color:#666;">Données introuvables.</div>';
        return;
    }

    // CAS 2 : Données présentes, mais pas de coordonnées (lat/lon null)
    if (!data.hasCoords) {
        divElement.innerHTML = `
            <div style="display:flex; align-items:center; justify-content:center; height:100%; background:#f8f9fa; color:#888; text-align:center; padding:10px;">
                <div>
                    <span style="font-size:24px;">🌍</span><br>
                    <strong>Coordonnées en attente</strong><br>
                    <small>Les lieux "${data.depart.nom}" ou "${data.arrivee.nom}" n'ont pas encore été géolocalisés.</small>
                </div>
            </div>`;
        // On s'assure que le conteneur est visible
        divElement.style.display = 'block';
        return;
    }

    // Si la carte existe déjà, on la redimensionne juste
    if (mapInstances[id]) {
        setTimeout(() => {
            mapInstances[id].invalidateSize();
            if(data.layerGroup) {
                 mapInstances[id].fitBounds(data.layerGroup.getBounds(), { padding: [30, 30] });
            }
        }, 100);
        return;
    }

    // Initialisation normale de la carte
    const map = L.map(divId);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    mapInstances[id] = map;
    
    const color = data.color || '#0B667D';

    setTimeout(async () => {
        map.invalidateSize();
        await drawRoute(map, data, color, true, false, null);
    }, 50);
}

/**
 * 3. FONCTION DE DESSIN (CŒUR DU SYSTÈME)
 */
async function drawRoute(map, data, color, fitBounds, useOffset, clusterGroup) {
    // 1. Conversion sécurisée
    const latDep = parseFloat(data.depart.lat);
    const lonDep = parseFloat(data.depart.lon);
    const latArr = parseFloat(data.arrivee.lat);
    const lonArr = parseFloat(data.arrivee.lon);

    // Sécurité anti-crash : Si une coordonnée est invalide (NaN), on arrête
    if (isNaN(latDep) || isNaN(lonDep) || isNaN(latArr) || isNaN(lonArr)) {
        console.error("Coordonnées invalides pour le trajet:", data.titre);
        return;
    }

    let stepCounter = 1;

    // Création des marqueurs
    createNumberedMarker(map, latDep, lonDep, stepCounter++, color, `<b>Départ:</b> ${data.depart.nom}`, useOffset ? 'left' : null, clusterGroup);

    if (data.sousEtapes && data.sousEtapes.length > 0) {
        data.sousEtapes.forEach((se) => {
            const sLat = parseFloat(se.lat);
            const sLon = parseFloat(se.lon);
            if (!isNaN(sLat) && !isNaN(sLon)) {
                let popupContent = `<b>📍 ${se.nom}</b>`;
                if (se.heure) popupContent += `<br>🕐 ${se.heure}`;
                createNumberedMarker(map, sLat, sLon, stepCounter++, color, popupContent, null, clusterGroup);
            }
        });
    }

    createNumberedMarker(map, latArr, lonArr, stepCounter, color, `<b>Arrivée:</b> ${data.arrivee.nom}`, useOffset ? 'right' : null, clusterGroup);

    // 2. Calcul itinéraire OSRM
    let profile = 'driving';
    if (data.mode === 'velo' || data.mode === 'vélo') profile = 'cycling';
    if (data.mode === 'marche' || data.mode === 'à pied') profile = 'walking';

    let coordinates = `${lonDep},${latDep}`;
    if (data.sousEtapes && data.sousEtapes.length > 0) {
        data.sousEtapes.forEach(se => {
             // On s'assure de ne pas mettre de NaN dans l'URL
             const sLat = parseFloat(se.lat);
             const sLon = parseFloat(se.lon);
             if(!isNaN(sLat) && !isNaN(sLon)) {
                 coordinates += `;${sLon},${sLat}`;
             }
        });
    }
    coordinates += `;${lonArr},${latArr}`;

    const url = `https://router.project-osrm.org/route/v1/${profile}/${coordinates}?overview=full&geometries=geojson`;

    try {
        const response = await fetch(url);
        const json = await response.json();
        let geoData;

        if (json.code === 'Ok') {
            geoData = json.routes[0].geometry;
        } else {
            console.warn("OSRM fallback (ligne droite) pour:", data.titre);
            geoData = {
                "type": "LineString",
                "coordinates": [[lonDep, latDep], [lonArr, latArr]]
            };
        }

        const routeLayer = L.geoJSON(geoData, {
            style: { color: color, weight: 5, opacity: 0.8 }
        }).addTo(map);

        data.layerGroup = routeLayer;

        if (fitBounds) {
            map.fitBounds(routeLayer.getBounds(), { padding: [30, 30] });
        }

    } catch (e) {
        console.error("Erreur trace route:", e);
        // Fallback ligne droite visuelle
        const line = L.polyline([[latDep, lonDep], [latArr, lonArr]], {color: color}).addTo(map);
        if (fitBounds) map.fitBounds(line.getBounds());
    }
}

function createNumberedMarker(map, lat, lon, number, color, popupText, offsetDirection, clusterGroup) {
    // Sécurité ultime
    if (isNaN(lat) || isNaN(lon)) return;

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

    if (clusterGroup) {
        clusterGroup.addLayer(marker);
    } else {
        marker.addTo(map);
    }
}

/* ============================================================
   UI & INTERACTION
   ============================================================ */

window.toggleTrajet = function(id) {
    const container = document.getElementById('sous-etapes-' + id);
    const card = document.getElementById('card-' + id);
    const mapGlobal = document.getElementById('map-global');
    
    if (!container || !card) return;

    const isActive = container.classList.contains('active');

    if (isActive) {
        // Fermeture
        container.classList.remove('active');
        card.classList.remove('active');
        checkAndToggleGlobalMap();
    } else {
        // Ouverture
        container.classList.add('active');
        card.classList.add('active');
        
        if (mapGlobal) mapGlobal.style.display = 'none';

        // IMPORTANT : On attend que l'animation CSS (slide down) commence
        setTimeout(() => { 
            initStepMap(id); 
        }, 300);
    }
};

function checkAndToggleGlobalMap() {
    const mapGlobal = document.getElementById('map-global');
    if (!mapGlobal) return;
    const activeCards = document.querySelectorAll('.card-vu.active');
    
    if (activeCards.length === 0) {
        mapGlobal.style.display = 'block';
        // Petit fix si la carte globale a besoin de se redessiner
        const map = L.DomUtil.get('map-global');
        if(map && map._leaflet_id) {
             // Optionnel : map.invalidateSize() si le conteneur a changé
        }
    } else {
        mapGlobal.style.display = 'none';
    }
}

/* ============================================================
   CALCULS TEXTUELS
   ============================================================ */
function calculerTousLesSegments() {
    const segments = document.querySelectorAll('.segment-info');
    segments.forEach((segment, index) => {
        // Petit délai pour ne pas spammer l'API
        setTimeout(() => { calculerSegment(segment); }, index * 300);
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
    
    if (isNaN(latDep) || isNaN(lonDep) || isNaN(latArr) || isNaN(lonArr)) {
        distEl.textContent = '-'; timeEl.textContent = '-'; return;
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
            
            const durationSec = route.duration;
            const heures = Math.floor(durationSec / 3600);
            const minutes = Math.floor((durationSec % 3600) / 60);
            let tempsTexte = "";
            
            if (heures > 0) {
                tempsTexte = `${heures}h${minutes.toString().padStart(2, '0')}`;
            } else {
                tempsTexte = `${minutes} min`;
            }
            
            distEl.textContent = `${distanceKm} km`;
            timeEl.textContent = tempsTexte;
            segment.classList.add('segment-calculated');
        } else {
            distEl.textContent = '?'; timeEl.textContent = '?';
        }
    } catch (error) {
        distEl.textContent = ''; timeEl.textContent = '';
    }
}