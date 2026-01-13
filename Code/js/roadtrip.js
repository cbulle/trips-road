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
        if (!data.hasCoords) {
            console.warn(`Trajet ${data.id} ignoré : coordonnées manquantes.`);
            continue;
        }

        data.color = colorsPalette[colorIndex % colorsPalette.length];
        
        try {
            await drawRoute(map, data, data.color, false, true, markersCluster);

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

    if (!data || !data.hasCoords) {
        divElement.innerHTML = `
            <div style="display:flex; align-items:center; justify-content:center; height:100%; background:#f8f9fa; color:#888; text-align:center; padding:10px;">
                <div>
                    <span style="font-size:24px;">🌍</span><br>
                    <strong>Coordonnées en attente</strong><br>
                    <small>Les lieux n'ont pas encore été géolocalisés.</small>
                </div>
            </div>`;
        divElement.style.display = 'block';
        return;
    }

    if (mapInstances[id]) {
        setTimeout(() => {
            mapInstances[id].invalidateSize();
            if(data.layerGroup) {
                 mapInstances[id].fitBounds(data.layerGroup.getBounds(), { padding: [30, 30] });
            }
        }, 100);
        return;
    }

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
 * 3. FONCTION DE DESSIN
 */
async function drawRoute(map, data, color, fitBounds, useOffset, clusterGroup) {
    const latDep = parseFloat(data.depart.lat);
    const lonDep = parseFloat(data.depart.lon);
    const latArr = parseFloat(data.arrivee.lat);
    const lonArr = parseFloat(data.arrivee.lon);

    if (isNaN(latDep) || isNaN(lonDep) || isNaN(latArr) || isNaN(lonArr)) return;

    let stepCounter = 1;
    createNumberedMarker(map, latDep, lonDep, stepCounter++, color, `<b>Départ:</b> ${data.depart.nom}`, useOffset ? 'left' : null, clusterGroup);

    if (data.sousEtapes && data.sousEtapes.length > 0) {
        data.sousEtapes.forEach((se) => {
            const sLat = parseFloat(se.lat); const sLon = parseFloat(se.lon);
            if (!isNaN(sLat) && !isNaN(sLon)) {
                let popupContent = `<b>📍 ${se.nom}</b>`;
                if (se.heure) popupContent += `<br>⏳ Pause : ${se.heure}`;
                createNumberedMarker(map, sLat, sLon, stepCounter++, color, popupContent, null, clusterGroup);
            }
        });
    }
    createNumberedMarker(map, latArr, lonArr, stepCounter, color, `<b>Arrivée:</b> ${data.arrivee.nom}`, useOffset ? 'right' : null, clusterGroup);

    const servers = {
        'voiture': 'https://routing.openstreetmap.de/routed-car',
        'velo': 'https://routing.openstreetmap.de/routed-bike',
        'vélo': 'https://routing.openstreetmap.de/routed-bike',
        'marche': 'https://routing.openstreetmap.de/routed-foot',
        'à pied': 'https://routing.openstreetmap.de/routed-foot'
    };
    
    const baseUrl = servers[data.mode] || servers['voiture'];
    let coordinates = `${lonDep},${latDep}`;
    if (data.sousEtapes) {
        data.sousEtapes.forEach(se => {
            if(!isNaN(parseFloat(se.lat))) coordinates += `;${se.lon},${se.lat}`;
        });
    }
    coordinates += `;${lonArr},${latArr}`;

    const url = `${baseUrl}/route/v1/driving/${coordinates}?overview=full&geometries=geojson`;

    try {
        const response = await fetch(url);
        const json = await response.json();
        
        if (json.code === 'Ok') {
            const routeLayer = L.geoJSON(json.routes[0].geometry, {
                style: { 
                    color: color, 
                    weight: 5, 
                    opacity: 0.8,
                    dashArray: (data.mode !== 'voiture') ? '10, 10' : null 
                }
            }).addTo(map);

            data.layerGroup = routeLayer;
            if (fitBounds) map.fitBounds(routeLayer.getBounds(), { padding: [30, 30] });
        }
    } catch (e) {
        console.error("Erreur trace route:", e);
        L.polyline([[latDep, lonDep], [latArr, lonArr]], {color: color, weight: 2, dashArray: '5,5'}).addTo(map);
    }
}

function createNumberedMarker(map, lat, lon, number, color, popupText, offsetDirection, clusterGroup) {
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
        setTimeout(() => { initStepMap(id); }, 300);
    }
};

function checkAndToggleGlobalMap() {
    const mapGlobal = document.getElementById('map-global');
    if (!mapGlobal) return;
    const activeCards = document.querySelectorAll('.card-vu.active');
    mapGlobal.style.display = (activeCards.length === 0) ? 'block' : 'none';
}

function calculerTousLesSegments() {
    const cards = document.querySelectorAll('.card-vu');
    cards.forEach((card, cardIndex) => {
        setTimeout(() => { calculerHorairesTrajet(card); }, cardIndex * 500);
    });
}

async function calculerHorairesTrajet(card) {
    const trajetId = card.id.replace('card-', '');
    const dataTrajet = getTrajetData(trajetId);
    
    if (!dataTrajet || !dataTrajet.hasCoords) {
        // console.warn(`Trajet ${trajetId} : coordonnées manquantes`);
        return;
    }

    const etapeCards = card.querySelectorAll('.sous-etape-card');
    
    // Construction des coordonnées
    let coordsPath = `${dataTrajet.depart.lon},${dataTrajet.depart.lat}`;
    if (dataTrajet.sousEtapes && dataTrajet.sousEtapes.length > 0) {
        dataTrajet.sousEtapes.forEach(se => {
            coordsPath += `;${se.lon},${se.lat}`;
        });
    }
    coordsPath += `;${dataTrajet.arrivee.lon},${dataTrajet.arrivee.lat}`;

    // --- CORRECTION ICI : Utilisation des bons serveurs ---
    const servers = {
        'voiture': 'https://routing.openstreetmap.de/routed-car',
        'velo': 'https://routing.openstreetmap.de/routed-bike',
        'vélo': 'https://routing.openstreetmap.de/routed-bike',
        'marche': 'https://routing.openstreetmap.de/routed-foot',
        'à pied': 'https://routing.openstreetmap.de/routed-foot'
    };

    // On récupère le bon serveur, sinon voiture par défaut
    const baseUrl = servers[dataTrajet.mode] || servers['voiture'];

    // Note : Sur ces serveurs spécifiques (FOSSGIS), on laisse souvent "/driving/" dans l'URL
    // car c'est le sous-domaine (routed-foot) qui détermine la vitesse.
    const url = `${baseUrl}/route/v1/driving/${coordsPath}?overview=false&steps=false`;
    // -------------------------------------------------------

    try {
        const response = await fetch(url);
        const data = await response.json();

        if (data.code === 'Ok' && data.routes && data.routes[0]) {
            const route = data.routes[0];
            let currentClock = dataTrajet.heure_depart || '08:00';
            
            route.legs.forEach((leg, legIndex) => {
                const durationSeconds = leg.duration;
                const distanceKm = (leg.distance / 1000).toFixed(1);
                
                currentClock = addTime(currentClock, durationSeconds);
                
                const targetCard = etapeCards[legIndex + 1];
                
                if (targetCard) {
                    const horaireSpan = targetCard.querySelector('.horaire-calcule');
                    const isArrival = targetCard.dataset.isArrival === '1';
                    // Correction potentielle si 'isDeparture' n'est pas défini dans ton HTML, on vérifie juste arrival
                    
                    if (horaireSpan) {
                        if (isArrival) {
                            horaireSpan.innerHTML = `🏁 Arrivée : <strong>${currentClock}</strong>`;
                        } else {
                            horaireSpan.innerHTML = `⏰ Arrivée : <strong>${currentClock}</strong>`;
                            
                            const pauseDuration = targetCard.dataset.pause || '00:00';
                            if (pauseDuration !== '00:00') {
                                const departTime = addTime(currentClock, durationToSeconds(pauseDuration));
                                horaireSpan.innerHTML += `<br><span class="horaire-depart-etape">🚀 Départ : <strong>${departTime}</strong></span>`;
                                currentClock = departTime;
                            }
                        }
                    }
                }
                
                const segments = card.querySelectorAll('.segment-info');
                if (segments[legIndex]) {
                    const segmentInfo = segments[legIndex];
                    segmentInfo.querySelector('.segment-distance').textContent = distanceKm + " km";
                    
                    const hours = Math.floor(durationSeconds / 3600);
                    const minutes = Math.floor((durationSeconds % 3600) / 60);
                    let timeText = '';
                    if (hours > 0) timeText += hours + 'h ';
                    timeText += minutes + 'min';
                    
                    segmentInfo.querySelector('.segment-time').textContent = timeText;
                    // Ajout d'une classe pour indiquer que le calcul est fait (optionnel)
                    segmentInfo.classList.add('segment-calculated');
                }
            });
        }
    } catch (error) {
        console.error("Erreur calcul horaires:", error);
    }
}

function addTime(startTime, secondsToAdd) {
    if(!startTime) return "--:--";
    const [h, m] = startTime.split(':').map(Number);
    const date = new Date();
    date.setHours(h, m, 0, 0);
    date.setSeconds(date.getSeconds() + Math.round(secondsToAdd));
    return date.getHours().toString().padStart(2, '0') + ":" + 
           date.getMinutes().toString().padStart(2, '0');
}

function durationToSeconds(timeStr) {
    if(!timeStr) return 0;
    const parts = timeStr.split(':').map(Number);
    if(parts.length === 3) return (parts[0] * 3600) + (parts[1] * 60) + parts[2];
    return (parts[0] * 3600) + (parts[1] * 60);
}