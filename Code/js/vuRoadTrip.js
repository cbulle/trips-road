document.addEventListener('DOMContentLoaded', () => {
    // On lance la carte globale direct
    initGlobalMap();
    
    // Lancer le calcul des distances et temps
    calculerTousLesSegments();
});

// Stockage des instances de cartes pour ne pas les recharger 2 fois
const mapInstances = {};

/**
 * 1. GESTION DE LA CARTE GLOBALE (Tout le roadtrip)
 */
async function initGlobalMap() {
    // Vérif si la div existe
    if (!document.getElementById('map-global')) return;

    // Création de la carte
    const map = L.map('map-global');
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    const bounds = [];
    const colors = ['#39B2A5', '#E0C58B', '#FF7F50', '#2E8B57', '#BF092F']; // Tes couleurs CSS
    let colorIndex = 0;

    // roadTripData est défini dans le PHP (voir plus bas)
    for (const id in roadTripData) {
        const data = roadTripData[id];
        const color = colors[colorIndex % colors.length];

        // Tracer le segment
        await drawRoute(map, data, color, false);

        // Ajouter les points pour centrer la vue à la fin
        bounds.push([data.depart.lat, data.depart.lon]);
        bounds.push([data.arrivee.lat, data.arrivee.lon]);
        
        // AJOUT : Ajouter les sous-étapes aux bounds
        if (data.sousEtapes && data.sousEtapes.length > 0) {
            data.sousEtapes.forEach(se => {
                bounds.push([se.lat, se.lon]);
            });
        }

        colorIndex++;
    }

    // Centrer la carte sur tout le roadtrip
    if (bounds.length > 0) {
        map.fitBounds(bounds, { padding: [50, 50] });
    } else {
        map.setView([46.603354, 1.888334], 6); // Centre France par défaut
    }
}

/**
 * 2. GESTION DU CLIC (Toggle détails + Carte étape)
 * Cette fonction est appelée par le onclick="" dans le PHP
 */
window.toggleTrajet = function(id) {
    const container = document.getElementById('sous-etapes-' + id);
    const card = document.getElementById('card-' + id);
    const mapGlobal = document.getElementById('map-global');
    
    if (!container || !card) return;

    // Toggle de la classe CSS
    const isActive = container.classList.contains('active');

    if (isActive) {
        // On ferme
        container.classList.remove('active');
        card.classList.remove('active');
        
        // Vérifier s'il reste des cartes actives
        checkAndToggleGlobalMap();
    } else {
        // On ouvre
        container.classList.add('active');
        card.classList.add('active');
        
        // Masquer la carte globale
        if (mapGlobal) {
            mapGlobal.style.display = 'none';
        }

        // IMPORTANT : On attend un tout petit peu que la div s'affiche (display:block)
        // avant de charger la carte, sinon Leaflet bug et affiche du gris.
        setTimeout(() => {
            initStepMap(id);
        }, 200);
    }
};

/**
 * Fonction pour vérifier s'il y a des cartes actives et afficher/masquer la carte globale
 */
function checkAndToggleGlobalMap() {
    const mapGlobal = document.getElementById('map-global');
    if (!mapGlobal) return;
    
    // Chercher s'il y a au moins une carte active
    const activeCards = document.querySelectorAll('.card-vu.active');
    
    if (activeCards.length === 0) {
        // Aucune carte active, afficher la carte globale
        mapGlobal.style.display = 'block';
    } else {
        // Il y a au moins une carte active, masquer la carte globale
        mapGlobal.style.display = 'none';
    }
}

/**
 * 3. CARTE INDIVIDUELLE PAR ÉTAPE
 */
async function initStepMap(id) {
    // Si la carte existe déjà, on redimensionne juste pour être sûr
    if (mapInstances[id]) {
        mapInstances[id].invalidateSize();
        return;
    }

    const divId = 'map-trajet-' + id;
    if (!document.getElementById(divId)) return;

    const data = roadTripData[id];
    
    const map = L.map(divId);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    // Sauvegarde l'instance
    mapInstances[id] = map;

    // Dessiner la route en bleu (ou autre couleur fixe pour les étapes)
    await drawRoute(map, data, '#0B667D', true);
}

/**
 * 4. FONCTION UTILITAIRE : DESSINER UNE ROUTE (OSRM API)
 */
async function drawRoute(map, data, color, fitBounds) {
    const latDep = data.depart.lat;
    const lonDep = data.depart.lon;
    const latArr = data.arrivee.lat;
    const lonArr = data.arrivee.lon;

    // Choix du profil OSRM
    let profile = 'driving';
    if (data.mode === 'velo' || data.mode === 'vélo') profile = 'cycling';
    if (data.mode === 'marche' || data.mode === 'à pied') profile = 'walking';

    // Construction de la liste des coordonnées : départ + sous-étapes + arrivée
    let coordinates = `${lonDep},${latDep}`;
    
    // AJOUT : Ajouter les sous-étapes dans l'itinéraire
    if (data.sousEtapes && data.sousEtapes.length > 0) {
        data.sousEtapes.forEach(se => {
            coordinates += `;${se.lon},${se.lat}`;
        });
    }
    
    coordinates += `;${lonArr},${latArr}`;

    // URL API (Attention: OSRM public peut être lent ou limité, c'est pour la démo)
    let url = `https://router.project-osrm.org/route/v1/${profile}/${coordinates}?overview=full&geometries=geojson`;

    try {
        const response = await fetch(url);
        const json = await response.json();
        let geoData;

        if (json.code === 'Ok') {
            geoData = json.routes[0].geometry;
        } else {
            // Fallback : ligne droite si l'API échoue
            geoData = {
                "type": "LineString",
                "coordinates": [[lonDep, latDep], [lonArr, latArr]]
            };
        }

        const layer = L.geoJSON(geoData, {
            style: { color: color, weight: 6, opacity: 0.8 }
        }).addTo(map);

        // Marqueurs Départ
        L.marker([latDep, lonDep], {
            icon: L.divIcon({
                className: 'custom-marker-depart',
                html: '<div style="background-color: #2E8B57; color: white; border-radius: 50%; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: bold; border: 2px solid white;">🚀</div>',
                iconSize: [30, 30]
            })
        }).addTo(map).bindPopup(`<b>🚀 Départ:</b> ${data.depart.nom}`);

        // AJOUT : Marqueurs pour les sous-étapes
        if (data.sousEtapes && data.sousEtapes.length > 0) {
            data.sousEtapes.forEach((se, index) => {
                let popupContent = `<b>📍 ${se.nom}</b>`;
                if (se.heure) {
                    popupContent += `<br>🕐 ${se.heure}`;
                }
                if (se.remarque) {
                    // Extraire juste le texte sans HTML pour le popup
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = se.remarque;
                    const textContent = tempDiv.textContent || tempDiv.innerText || '';
                    const preview = textContent.substring(0, 100) + (textContent.length > 100 ? '...' : '');
                    popupContent += `<br><em>${preview}</em>`;
                }
                
                L.marker([se.lat, se.lon], {
                    icon: L.divIcon({
                        className: 'custom-marker-etape',
                        html: `<div style="background-color: ${color}; color: white; border-radius: 50%; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: bold; border: 2px solid white;">${index + 1}</div>`,
                        iconSize: [28, 28]
                    })
                }).addTo(map).bindPopup(popupContent);
            });
        }

        // Marqueur Arrivée
        L.marker([latArr, lonArr], {
            icon: L.divIcon({
                className: 'custom-marker-arrivee',
                html: '<div style="background-color: #BF092F; color: white; border-radius: 50%; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: bold; border: 2px solid white;">🏁</div>',
                iconSize: [30, 30]
            })
        }).addTo(map).bindPopup(`<b>🏁 Arrivée:</b> ${data.arrivee.nom}`);

        if (fitBounds) {
            map.fitBounds(layer.getBounds(), { padding: [20, 20] });
        }

    } catch (e) {
        console.error("Erreur itinéraire", e);
    }
}

/**
 * 5. CALCUL DES DISTANCES ET TEMPS ENTRE CHAQUE SEGMENT
 */
function calculerTousLesSegments() {
    const segments = document.querySelectorAll('.segment-info');
    
    segments.forEach((segment, index) => {
        // Délai pour éviter de surcharger l'API OSRM
        setTimeout(() => {
            calculerSegment(segment);
        }, index * 500); // 500ms entre chaque calcul
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
        distEl.textContent = 'N/A';
        timeEl.textContent = 'N/A';
        return;
    }
    
    // Conversion mode → profil OSRM
    const profiles = {
        'voiture': 'driving',
        'velo': 'cycling',
        'vélo': 'cycling',
        'marche': 'walking',
        'à pied': 'walking'
    };
    
    const profile = profiles[mode.toLowerCase()] || 'driving';
    
    // URL OSRM
    const url = `https://router.project-osrm.org/route/v1/${profile}/${lonDep},${latDep};${lonArr},${latArr}?overview=false`;
    
    try {
        const response = await fetch(url);
        const data = await response.json();
        
        if (data.code === 'Ok' && data.routes && data.routes.length > 0) {
            const route = data.routes[0];
            
            // Distance en km
            const distanceKm = (route.distance / 1000).toFixed(1);
            distEl.textContent = `${distanceKm} km`;
            
            // Temps
            const durationSec = route.duration;
            const heures = Math.floor(durationSec / 3600);
            const minutes = Math.floor((durationSec % 3600) / 60);
            
            let tempsTexte = '';
            if (heures > 0) {
                tempsTexte = `${heures}h${minutes > 0 ? minutes.toString().padStart(2, '0') : ''}`;
            } else {
                tempsTexte = `${minutes}min`;
            }
            
            timeEl.textContent = tempsTexte;
            
            // Ajouter une classe success pour le style
            segment.classList.add('segment-calculated');
        } else {
            distEl.textContent = '—';
            timeEl.textContent = '—';
        }
    } catch (error) {
        console.error('Erreur calcul segment:', error);
        distEl.textContent = 'Err';
        timeEl.textContent = 'Err';
    }
}