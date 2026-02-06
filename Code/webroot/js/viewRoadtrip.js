document.addEventListener('DOMContentLoaded', () => {
    initGlobalMap();
    calculerTousLesSegments();
});

const viewMapInstances = {};

const colorsPalette = [
    '#e6194b', '#3cb44b', '#ffe119', '#4363d8', '#f58231',
    '#911eb4', '#42d4f4', '#f032e6', '#bfef45', '#fabed4',
    '#469990', '#dcbeff', '#9A6324', '#fffac8', '#800000'
];

function getTrajetData(id) {
    if (typeof roadTripData === 'undefined' || !roadTripData) return null;
    return roadTripData.find(t => t.id == id);
}

async function initGlobalMap() {
    const mapDiv = document.getElementById('map-global');
    if (!mapDiv) return;

    mapDiv.style.display = 'block';

    const hasData = (typeof roadTripData !== 'undefined' && roadTripData && roadTripData.length > 0);
    const map = L.map('map-global');

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    setTimeout(() => { map.invalidateSize(); }, 200);

    if (!hasData) {
        map.setView([46.6, 2.2], 5);
        return;
    }

    const markersCluster = L.markerClusterGroup({
        maxClusterRadius: 40,
        showCoverageOnHover: false
    });
    map.addLayer(markersCluster);

    const bounds = [];
    let colorIndex = 0;

    for (const data of roadTripData) {
        if (!data.hasCoords) continue;

        data.color = colorsPalette[colorIndex % colorsPalette.length];

        try {
            await drawRoute(map, data, data.color, false, markersCluster);

            bounds.push([data.depart.lat, data.depart.lon]);
            bounds.push([data.arrivee.lat, data.arrivee.lon]);
            if(data.sousEtapes) {
                data.sousEtapes.forEach(s => { if(s.lat) bounds.push([s.lat, s.lon]); });
            }
        } catch (e) { console.error(e); }
        colorIndex++;
    }

    if (bounds.length > 0) {
        map.fitBounds(bounds, { padding: [50, 50] });
    } else {
        map.setView([46.6, 2.2], 6);
    }
}

async function initStepMap(id) {
    const data = getTrajetData(id);
    const divId = 'map-trajet-' + id;
    const divElement = document.getElementById(divId);

    if (!divElement) return;

    if (!data || !data.hasCoords) {
        divElement.innerHTML = '<div style="display:flex; align-items:center; justify-content:center; height:100%; color:#888;">Coordonnées en attente...</div>';
        return;
    }

    if (viewMapInstances[id]) {
        setTimeout(() => {
            viewMapInstances[id].invalidateSize();
            if(data.layerBounds) viewMapInstances[id].fitBounds(data.layerBounds, { padding: [20, 20] });
        }, 200);
        return;
    }

    const map = L.map(divId);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OSM' }).addTo(map);

    viewMapInstances[id] = map;

    setTimeout(async () => {
        map.invalidateSize();
        await drawRoute(map, data, data.color || '#3388ff', true, null);
    }, 100);
}

async function drawRoute(map, data, color, fitBounds, clusterGroup) {
    let stepCounter = 1;

    createNumberedMarker(map, data.depart, stepCounter++, color, `<b>Départ:</b> ${data.depart.nom}`, 'left', clusterGroup);

    if(data.sousEtapes) {
        data.sousEtapes.forEach(s => {
            let popup = `<b>📍 ${s.nom}</b>`;
            if(s.heure) popup += `<br>⏱️ Pause prévue: ${s.heure}`;
            createNumberedMarker(map, s, stepCounter++, color, popup, null, clusterGroup);
        });
    }

    createNumberedMarker(map, data.arrivee, stepCounter, color, `<b>Arrivée:</b> ${data.arrivee.nom}`, 'right', clusterGroup);

    const servers = {
        'voiture': 'https://routing.openstreetmap.de/routed-car',
        'velo': 'https://routing.openstreetmap.de/routed-bike',
        'vélo': 'https://routing.openstreetmap.de/routed-bike',
        'marche': 'https://routing.openstreetmap.de/routed-foot',
        'à pied': 'https://routing.openstreetmap.de/routed-foot'
    };

    const mode = data.mode || 'voiture';
    const baseUrl = servers[mode] || servers['voiture'];

    let coords = `${data.depart.lon},${data.depart.lat}`;
    if(data.sousEtapes) {
        data.sousEtapes.forEach(s => {
            if(s.lon && s.lat) coords += `;${s.lon},${s.lat}`;
        });
    }
    coords += `;${data.arrivee.lon},${data.arrivee.lat}`;

    const url = `${baseUrl}/route/v1/driving/${coords}?overview=full&geometries=geojson`;

    try {
        const resp = await fetch(url);
        const json = await resp.json();

        if (json.code === 'Ok') {
            const style = {
                color: color,
                weight: 5,
                opacity: 0.8,
                dashArray: (mode !== 'voiture') ? '10, 10' : null
            };

            const layer = L.geoJSON(json.routes[0].geometry, style).addTo(map);

            if(fitBounds) map.fitBounds(layer.getBounds(), { padding:[30,30] });
            data.layerBounds = layer.getBounds();
        }
    } catch(e) {
        console.warn("API Routage échouée, tracé ligne droite.");
        const points = [[data.depart.lat, data.depart.lon]];
        if(data.sousEtapes) data.sousEtapes.forEach(s => points.push([s.lat, s.lon]));
        points.push([data.arrivee.lat, data.arrivee.lon]);

        L.polyline(points, { color: color, dashArray: '5,10' }).addTo(map);
    }
}

function createNumberedMarker(map, point, number, color, popupText, offset, cluster) {
    if(!point || !point.lat || !point.lon) return;

    const offsetClass = offset ? `marker-offset-${offset}` : '';

    const icon = L.divIcon({
        className: `custom-marker-number ${offsetClass}`,
        html: `<div class="marker-pin" style="background-color: ${color}; color: white; display:flex; justify-content:center; align-items:center; border-radius:50%; width:100%; height:100%; border:2px solid white; box-shadow:0 2px 5px rgba(0,0,0,0.3); font-weight:bold;">${number}</div>`,
        iconSize: [30, 30],
        iconAnchor: [15, 15],
        popupAnchor: [0, -15]
    });

    const marker = L.marker([point.lat, point.lon], { icon: icon }).bindPopup(popupText);

    if(cluster) cluster.addLayer(marker);
    else marker.addTo(map);
}

window.toggleTrajet = function(id) {
    const content = document.getElementById('sous-etapes-' + id);
    const card = document.getElementById('card-' + id);
    const mapGlobal = document.getElementById('map-global');

    if (!content || !card) return;

    const isOpening = (content.style.display === 'none' || content.style.display === '');

    if (!isOpening) {
        content.style.display = 'none';
        card.classList.remove('active');
        checkToggleGlobalMap();
    } else {
        content.style.display = 'block';
        card.classList.add('active');

        if(mapGlobal) mapGlobal.style.display = 'none';

        setTimeout(() => { initStepMap(id); }, 100);
    }
};

function checkToggleGlobalMap() {
    const active = document.querySelectorAll('.card-vu.active');
    const mapGlobal = document.getElementById('map-global');
    if(mapGlobal) mapGlobal.style.display = (active.length === 0) ? 'block' : 'none';
}

function calculerTousLesSegments() {
    const cards = document.querySelectorAll('.card-vu');
    cards.forEach((card, i) => {
        setTimeout(() => processCardTimes(card), i * 600);
    });
}

async function processCardTimes(card) {
    const id = card.id.replace('card-', '');
    const data = getTrajetData(id);

    if(!data || !data.hasCoords) return;

    const servers = {
        'voiture': 'https://routing.openstreetmap.de/routed-car',
        'velo': 'https://routing.openstreetmap.de/routed-bike',
        'vélo': 'https://routing.openstreetmap.de/routed-bike',
        'marche': 'https://routing.openstreetmap.de/routed-foot',
        'à pied': 'https://routing.openstreetmap.de/routed-foot'
    };
    const baseUrl = servers[data.mode] || servers['voiture'];

    let coords = `${data.depart.lon},${data.depart.lat}`;
    if(data.sousEtapes) data.sousEtapes.forEach(s => { coords += `;${s.lon},${s.lat}`; });
    coords += `;${data.arrivee.lon},${data.arrivee.lat}`;

    const url = `${baseUrl}/route/v1/driving/${coords}?overview=false&steps=false`;

    try {
        const resp = await fetch(url);
        const json = await resp.json();

        if (json.code === 'Ok' && json.routes && json.routes[0]) {
            const legs = json.routes[0].legs;

            let currentClock = data.heure_depart || '08:00';

            const etapeElements = card.querySelectorAll('.sous-etape-card');
            const segmentInfos = card.querySelectorAll('.segment-info');

            legs.forEach((leg, index) => {
                const durationSec = leg.duration;
                const distanceKm = (leg.distance / 1000).toFixed(1);

                if(segmentInfos[index]) {
                    const segDiv = segmentInfos[index];
                    segDiv.querySelector('.segment-distance').textContent = `${distanceKm} km`;
                    segDiv.querySelector('.segment-time').textContent = formatDuration(durationSec);
                    segDiv.classList.add('segment-calculated');
                }

                const arrivalClock = addTime(currentClock, durationSec);

                const targetCard = etapeElements[index + 1];

                if (targetCard) {
                    const loader = targetCard.querySelector('.horaire-calcule');
                    const pauseStr = targetCard.dataset.pause;
                    const isLast = !targetCard.nextElementSibling;

                    if(loader) {
                        if (isLast) {
                            loader.innerHTML = `🏁 Arrivée : <strong>${arrivalClock}</strong>`;
                        } else {
                            let html = `⏰ Arrivée : <strong>${arrivalClock}</strong>`;

                            if (pauseStr && pauseStr !== '00:00' && pauseStr !== '00:00:00') {
                                const pauseSeconds = durationToSeconds(pauseStr);
                                const departureClock = addTime(arrivalClock, pauseSeconds);
                                html += `<br><span class="horaire-depart-etape" style="color:var(--vert); display:block; margin-top:5px;">🚀 Repart : <strong>${departureClock}</strong></span>`;
                                currentClock = departureClock;
                            } else {
                                currentClock = arrivalClock;
                            }
                            loader.innerHTML = html;
                        }
                    }
                }
            });
        }
    } catch (e) {
        console.error("Erreur calcul temps:", e);
    }
}

function addTime(startTime, secondsToAdd) {
    if(!startTime) return "--:--";
    const parts = startTime.split(':').map(Number);
    const date = new Date();
    date.setHours(parts[0], parts[1], 0, 0);
    date.setSeconds(date.getSeconds() + Math.round(secondsToAdd));
    return date.getHours().toString().padStart(2, '0') + ":" +
        date.getMinutes().toString().padStart(2, '0');
}

function durationToSeconds(timeStr) {
    if(!timeStr) return 0;
    const parts = timeStr.split(':').map(Number);
    let seconds = (parts[0] * 3600) + (parts[1] * 60);
    if(parts.length === 3) {
        seconds += parts[2];
    }
    return seconds;
}

function formatDuration(seconds) {
    const h = Math.floor(seconds / 3600);
    const m = Math.floor((seconds % 3600) / 60);
    if (h > 0) return `${h}h ${m}min`;
    return `${m} min`;
}
