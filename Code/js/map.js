document.addEventListener('DOMContentLoaded', async () => {

    let userFavorites = [];
    try {
        const respFav = await fetch('/fonctions/get_lieux_favoris.php');
        if(respFav.ok) {
            userFavorites = await respFav.json();
        }
    } catch (e) {
        console.log("Erreur chargement favoris", e);
    }

    // ============================================================
    // 0. FONCTION DE COMPRESSION D'IMAGE (CÔTÉ CLIENT)
    // ============================================================
    
    function compresserImageJS(file, quality = 0.7, maxWidth = 1920) {
        return new Promise((resolve, reject) => {
            const fileName = file.name;
            const reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onload = event => {
                const img = new Image();
                img.src = event.target.result;
                img.onload = () => {
                    let width = img.width;
                    let height = img.height;
                    if (width > maxWidth) {
                        height = Math.round(height * (maxWidth / width));
                        width = maxWidth;
                    }
                    const canvas = document.createElement('canvas');
                    canvas.width = width;
                    canvas.height = height;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);
                    ctx.canvas.toBlob((blob) => {
                        if (!blob) {
                            reject(new Error("Erreur lors de la création du blob via Canvas"));
                            return;
                        }
                        const compressedFile = new File([blob], fileName, {
                            type: 'image/jpeg',
                            lastModified: Date.now()
                        });
                        resolve(compressedFile);
                    }, 'image/jpeg', quality);
                };
                img.onerror = error => reject(error);
            };
            reader.onerror = error => reject(error);
        });
    }

    // ============================================================
    // 0b. UTILITAIRES DE TEMPS ET DISTANCE (AJOUTÉ)
    // ============================================================
    
    function formatDuration(seconds) {
        const h = Math.floor(seconds / 3600);
        const m = Math.floor((seconds % 3600) / 60);
        return h > 0 ? `${h}h${m.toString().padStart(2, '0')}` : `${m} min`;
    }

    function calculateETA(lastStepTime, travelSeconds) {
        if (!lastStepTime) return null;
        const [hours, minutes] = lastStepTime.split(':').map(Number);
        const date = new Date();
        date.setHours(hours, minutes, 0);
        date.setSeconds(date.getSeconds() + travelSeconds);
        return date.getHours().toString().padStart(2, '0') + ":" + 
               date.getMinutes().toString().padStart(2, '0');
    }

    // ============================================================
    // 1. INITIALISATION DE LA CARTE & VARIABLES
    // ============================================================

    let map = L.map('map').setView([46.5, 2.5], 6);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '@ OpenStreetMap'
    }).addTo(map);

    let segments = []; 
    const markers = {}; 
    let currentStartCity = (typeof USER_DEFAULT_CITY !== 'undefined') ? USER_DEFAULT_CITY : ""; 
    let currentStartCoords = null; 

    const strategies = {
        'Voiture': 'driving',
        'Velo': 'cycling',
        'Marche': 'walking'
    };
 
    const segmentColors = [
        '#0B667D', '#2E8B57', '#FF7F50', '#BF092F', '#8e44ad', '#d35400', '#2980b9', // Tes couleurs originales
        '#16A085', 
        '#E67E22', 
        '#8E44AD', 
        '#2C3E50', 
        '#C0392B', 
        '#27AE60', 
        '#F1C40F', 
        '#E74C3C', 
        '#34495E', 
        '#9B59B6', 
        '#1ABC9C', 
        '#7F8C8D', 
        '#D35400'  
    ];

    // ============================================================
    // 1b. CHARGEMENT DES FAVORIS SUR LA CARTE
    // ============================================================
    
    const favoriteIcon = L.divIcon({
        html: '<div style="font-size: 24px; color: #f1c40f; text-shadow: 0 0 3px black;">⭐</div>',
        className: 'fav-marker-icon',
        iconSize: [30, 30],
        iconAnchor: [15, 15],
        popupAnchor: [0, -15]
    });

    async function loadMapFavorites() {
        try {
            const resp = await fetch('/fonctions/get_lieux_favoris.php');
            const favoris = await resp.json();
            favoris.forEach(fav => {
                const lat = parseFloat(fav.latitude);
                const lon = parseFloat(fav.longitude);
                const marker = L.marker([lat, lon], { icon: favoriteIcon }).addTo(map);
                const container = document.createElement('div');
                container.style.textAlign = 'center';
                container.innerHTML = `
                    <strong style="color:#d35400">${fav.nom_lieu}</strong><br>
                    <small>${fav.categorie}</small><br>
                    <div style="margin-top:10px; display:flex; flex-direction:column; gap:5px;">
                        <button class="btn-fav-action btn-start" style="background:#27ae60; color:white; border:none; padding:5px; cursor:pointer; border-radius:3px;">🚩 Définir comme Départ</button>
                        <button class="btn-fav-action btn-end" style="background:#c0392b; color:white; border:none; padding:5px; cursor:pointer; border-radius:3px;">🏁 Définir comme Arrivée</button>
                        <button class="btn-fav-action btn-step" style="background:#2980b9; color:white; border:none; padding:5px; cursor:pointer; border-radius:3px;">📍 Ajouter comme Étape</button>
                    </div>`;

                container.querySelector('.btn-start').addEventListener('click', () => {
                    const btnAdd = document.getElementById('btnAddSegment');
                    if (btnAdd && btnAdd.style.display !== 'none') btnAdd.click();
                    setTimeout(() => {
                        const inputStart = document.getElementById('inputStartBlock');
                        if (inputStart) {
                            inputStart.value = fav.nom_lieu;
                            currentStartCity = fav.nom_lieu;
                            currentStartCoords = [lat, lon];
                            inputStart.style.backgroundColor = '#d5f5e3';
                        } else {
                            alert("Le départ est déjà fixé par l'arrivée du segment précédent.");
                        }
                    }, 100);
                    marker.closePopup();
                });

                container.querySelector('.btn-end').addEventListener('click', () => {
                    const btnAdd = document.getElementById('btnAddSegment');
                    if (btnAdd && btnAdd.style.display !== 'none') btnAdd.click();
                    setTimeout(() => {
                        const inputEnd = document.getElementById('inputEndBlock');
                        if (inputEnd) {
                            inputEnd.value = fav.nom_lieu;
                            inputEnd.style.backgroundColor = '#d5f5e3';
                        }
                    }, 100);
                    marker.closePopup();
                });

                container.querySelector('.btn-step').addEventListener('click', () => {
                    const formContainer = document.getElementById('segmentFormContainer');
                    if (formContainer.style.display === 'block') {
                        document.getElementById('addSubEtape').click();
                        setTimeout(() => {
                            const allInputs = document.querySelectorAll('.subEtapeNom');
                            const lastInput = allInputs[allInputs.length - 1];
                            if(lastInput) {
                                lastInput.value = fav.nom_lieu;
                                lastInput.style.backgroundColor = '#d6eaf8';
                            }
                        }, 50);
                        marker.closePopup();
                    } else {
                        alert("Veuillez d'abord ouvrir le mode modification d'un trajet.");
                    }
                });
                marker.bindPopup(container);
            });
        } catch (e) { console.error("Erreur chargement favoris", e); }
    }
    loadMapFavorites();
 
    // ============================================================
    // 2. GESTION DU STATUT & VISIBILITÉ
    // ============================================================
    const statusSelect = document.getElementById('roadtripStatut');
    const visibilitySelect = document.getElementById('roadtripVisibilite');

    if(visibilitySelect) {
        visibilitySelect.disabled = false;
    }
    
    if(statusSelect) {
        statusSelect.addEventListener('change', () => {
             if(visibilitySelect) visibilitySelect.disabled = false;
        });
    }

    // ============================================================
    // 3. FONCTIONS UTILITAIRES
    // ============================================================
    
    async function getCoordonnees(ville) {
        /* List of the countries avalaible for research of a city (the API will only search the name in those
        country) All europe is here*/
        const europeCodes = [
            "fr","be","ch","lu", // France, Belguim, Switzerland, Luxemburg
            "de","at","li", // Germany, Austria, Liechtenstein
            "it","sm","va", // Italy, San-Marino, Vatican city
            "es","pt","ad", // Spain, Portugal, Andorra
            "gb","ie", // Great Britain, Ireland
            "nl","dk","no","se","fi","is", // Netherlands, Danemark, Norway, Sweden, Finland, Iceland
            "pl","cz","sk","hu", // Poland, Czech Republic, Slovakia, Hungary
            "ee","lv","lt", // Estonia, latvia, lethuania
            "ro","bg","gr","cy","mt", // Romania, Bulgaria, Greece, Cyprus, Malta
            "si","hr","ba","rs","me","al","mk","xk", // Slovenia, Croatia, Bosnia, Serbia, Montenegro, Albania, North Macedonia, Kosovo
            "ua","md","by","ge","am","az" // Ukraine, Moldova, Belarus, Georgia, Armenia, Azerbaidjan
        ].join(',');

        const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(ville)}&limit=1&accept-language=fr&countrycodes=${europeCodes}`;
        
        try {
            const resp = await fetch(url);
            const data = await resp.json();
            if (data.length > 0) return [parseFloat(data[0].lat), parseFloat(data[0].lon)];
            return null;
        } catch (e) { 
            console.error("Erreur de géocodage:", e);
            return null; 
        }
    }
    
    function addMarker(lieu, coords, type, popupContent) {
        const marker = L.marker(coords).addTo(map).bindPopup(popupContent);
        if (!markers[lieu]) markers[lieu] = [];
        markers[lieu].push({ marker, type });
        return marker;
    }

    function getNomSimple(nom) { return nom ? nom.split(',')[0].trim() : ''; }

    function initAutocomplete(element) {
        if (!element) return;

        element.addEventListener('input', function() {
            this.removeAttribute('data-lat');
            this.removeAttribute('data-lon');
            this.removeAttribute('data-full-name');
            this.style.backgroundColor = ''; 
        });
        // -----------------------------------------------------------------------

        $(element).autocomplete({
            source: function(request, response) {
                // Appel API Photon
                const url = `https://photon.komoot.io/api/?q=${encodeURIComponent(request.term)}&lang=fr&limit=15`;
                
                $.ajax({
                    url: url,
                    method: "GET",
                    success: function(data) {
                        let suggestions = [];
                        let seen = new Set();

                        // Liste des codes pays européens pour le filtrage
                        const europeanCountryCodes = [
                            "FR","BE","CH","LU","DE","AT","LI","IT","SM","VA","ES","PT","AD",
                            "GB","IE","NL","DK","NO","SE","FI","IS","PL","CZ","SK","HU","EE",
                            "LV","LT","RO","BG","GR","CY","MT","SI","HR","BA","RS","ME","AL",
                            "MK","XK","UA","MD","BY","GE","AM","AZ"
                        ];

                        data.features.forEach(item => {
                            const p = item.properties;
                            const countryCode = p.countrycode; 

                            // FILTRE : On ne garde que si le pays est dans notre liste européenne
                            if (!europeanCountryCodes.includes(countryCode)) {
                                return; 
                            }

                            const name = p.name || "";
                            const city = p.city || p.town || "";
                            const postcode = p.postcode || "";
                            const country = p.country || "";
                            
                            const uniqueKey = `${name}-${city}-${postcode}-${country}`.toLowerCase();

                            if (!seen.has(uniqueKey) && suggestions.length < 8) {
                                seen.add(uniqueKey);
                                const fullLabel = [name, city, postcode, country].filter(Boolean).join(", ");
                                
                                suggestions.push({
                                    label: `<div class="ui-menu-item-content">
                                                <span class="ui-menu-item-name">${name}</span>
                                                <span class="ui-menu-item-details">${fullLabel}</span>
                                            </div>`,
                                    value: fullLabel, 
                                    full_name: fullLabel,
                                    lat: item.geometry.coordinates[1],
                                    lon: item.geometry.coordinates[0]
                                });
                            }
                        });
                        response(suggestions);
                    }
                });
            },
            minLength: 3,
            select: function(event, ui) {
                $(this).val(ui.item.full_name);
                $(this).attr('data-full-name', ui.item.full_name);
                $(this).attr('data-lat', ui.item.lat);
                $(this).attr('data-lon', ui.item.lon);
                
                $(this).css('backgroundColor', '#e8f8f5');
                
                return false;
            }
        }).data("ui-autocomplete")._renderItem = function(ul, item) {
            return $("<li>").append($("<div>").html(item.label)).appendTo(ul);
        };
    }

    function updateDateConstraints() {
        const dateInputs = document.querySelectorAll('.legend-date-input');
        for (let i = 0; i < dateInputs.length; i++) {
            const currentInput = dateInputs[i];
            if (i > 0) {
                const prevInput = dateInputs[i - 1];
                if (prevInput.value) {
                    currentInput.min = prevInput.value;
                    if (currentInput.value && currentInput.value < prevInput.value) {
                        currentInput.value = prevInput.value;
                        const idx = currentInput.closest('li').dataset.index;
                        if(segments[idx]) segments[idx].date = prevInput.value;
                    }
                }
            }
            currentInput.onchange = (e) => {
                const idx = e.target.closest('li').dataset.index;
                if(segments[idx]) segments[idx].date = e.target.value;
                updateDateConstraints();
            };
        }
    }

    // ============================================================
    // 4. CHARGEMENT MODE ÉDITION
    // ============================================================

    if (typeof MODE_EDITION !== 'undefined' && MODE_EDITION === true && typeof EXISTING_TRAJETS !== 'undefined') {
        await loadExistingRoadTrip();
    } else if (currentStartCity) {
        getCoordonnees(currentStartCity).then(coords => {
            if (coords) {
                currentStartCoords = coords;
                addMarker(currentStartCity, currentStartCoords, "ville", `Départ : ${currentStartCity}`);
                map.setView(currentStartCoords, 10);
            }
        });
    }

    async function loadExistingRoadTrip() {
        for (let i = 0; i < EXISTING_TRAJETS.length; i++) {
            const t = EXISTING_TRAJETS[i];
            const startCoords = await getCoordonnees(t.depart);
            const endCoords = await getCoordonnees(t.arrivee);
            let sousEtapesWithCoords = [];
            if (t.sousEtapes && t.sousEtapes.length > 0) {
                for (const se of t.sousEtapes) {
                    const c = await getCoordonnees(se.nom);
                    if(c) {
                        sousEtapesWithCoords.push({ ...se, coords: c });
                        addMarker(se.nom, c, "sous_etape", `<b>${se.nom}</b><br>${se.heure}`);
                    }
                }
            }
            if (startCoords && endCoords) {
                addMarker(t.depart, startCoords, "ville", t.depart);
                addMarker(t.arrivee, endCoords, "ville", t.arrivee);
                const dataForJs = { mode: t.mode, date_trajet: t.date_trajet || t.date, sousEtapes: sousEtapesWithCoords };
                await _ajouterSegmentEntre(t.depart, startCoords, t.arrivee, endCoords, segments.length, strategies['Voiture'], dataForJs);
                currentStartCity = t.arrivee;
                currentStartCoords = endCoords;
            }
        }
        if (segments.length > 0) {
            const group = new L.featureGroup(segments.map(s => s.line));
            map.fitBounds(group.getBounds());
        }
    }

    // ============================================================
    // 5. AJOUT DE SEGMENTS
    // ============================================================
    
    async function _ajouterSegmentEntre(startName, startCoords, endName, endCoords, index, strategy, existingData = null) {
        const modeTransport = existingData ? existingData.mode : 'Voiture';
        console.log(modeTransport);
        const currentProfile = strategies[modeTransport] ? strategies[modeTransport].profile : strategy.profile;

        let coordsList = [startCoords];
        if (existingData && existingData.sousEtapes) {
            existingData.sousEtapes.forEach(se => { if(se.coords) coordsList.push(se.coords); });
        }
        coordsList.push(endCoords);

        const coordString = coordsList.map(c => `${c[1]},${c[0]}`).join(';');
        const url = `https://router.project-osrm.org/route/v1/${currentProfile}/${coordString}?overview=full&geometries=geojson`;
 
        try {
            const resp = await fetch(url);
            const data = await resp.json();
            const route = data.routes[0];
            
            const color = segmentColors[index % segmentColors.length];
            const line = L.geoJSON(route.geometry, { color: color, weight: 5 }).addTo(map);
 
            const segData = {
                line, 
                startName, startCoord: startCoords, 
                endName, endCoord: endCoords,
                couleurSegment: color,
                sousEtapes: existingData && existingData.sousEtapes ? existingData.sousEtapes : [],
                startNameSimple: getNomSimple(startName),
                endNameSimple: getNomSimple(endName),
                modeTransport: modeTransport,
                options: {},
                date: existingData ? existingData.date_trajet : '',
                distance: route.distance, // Mètres
                duration: route.duration, // Secondes
                legs: route.legs
            };
            segments.push(segData);
 
            const template = document.getElementById('template-legend-item');
            const clone = template.content.cloneNode(true);
            const li = clone.querySelector('li');
            li.dataset.index = index;
            clone.querySelector('.legend-color-indicator').style.background = color;
            clone.querySelector('.toggleSousEtapes').innerHTML = `${getNomSimple(startName)} → ${getNomSimple(endName)}`;
            
            const dateInput = clone.querySelector('.legend-date-input');
            if(segData.date) dateInput.value = segData.date;
            
            const transportBtns = clone.querySelectorAll('.transport-btn');
            transportBtns.forEach(btn => {
                btn.addEventListener('click', async () => {
                    // Récupérer le mode depuis l'attribut data-mode du bouton cliqué
                    const nouveauMode = btn.dataset.mode; 
                    
                    // UI : changer le bouton actif
                    transportBtns.forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');

                    // IMPORTANT : Appeler la mise à jour avec le nouveau mode
                    console.log("Passage au mode :", nouveauMode); // Pour tes tests
                    await updateRouteSegment(index, nouveauMode, segments[index].options);
                });
            });

            clone.querySelector('.modifierSousEtapes').dataset.index = index;
            document.getElementById('legendList').appendChild(clone);
            updateDateConstraints(); 
            updateLegendHtml(index); 

        } catch (e) { console.error(e); }
    }

    // Fonction utilitaire pour créer le select des favoris
    // 1. Fonction pour le menu Départ / Arrivée
    function createFavSelectForInput(targetInputId) {
        if (!userFavorites || userFavorites.length === 0) return null;

        const select = document.createElement('select');
        // MODIFICATION ICI : padding ajusté et border-radius 15px pour correspondre aux inputs
        select.style.cssText = "width: 100%; margin-bottom: 10px; padding: 10px 14px; border: 1px solid #ddd; border-radius: 15px; background-color: #fff; color: #555; font-size: 1rem; cursor: pointer;";
        
        let optionsHtml = '<option value="">⭐ Choisir un favori...</option>';
        userFavorites.forEach(fav => {
            const icon = (fav.categorie === 'restaurant') ? '🍽️' : 
                         (fav.categorie === 'hotel') ? '🏨' : '📍';
            optionsHtml += `<option value="${fav.nom_lieu}">${icon} ${fav.nom_lieu}</option>`;
        });
        select.innerHTML = optionsHtml;

        select.addEventListener('change', function() {
            const input = document.getElementById(targetInputId);
            if(input && this.value) {
                input.value = this.value;
                input.style.backgroundColor = '#e8f8f5';
                setTimeout(() => { input.style.backgroundColor = ''; }, 500);
            }
        });

        return select;
    }

    const btnAddSegment = document.getElementById('btnAddSegment');
    const newBlockFormContainer = document.getElementById('newBlockForm');
 
    if (btnAddSegment) {
        btnAddSegment.addEventListener('click', () => {
            btnAddSegment.style.display = 'none'; 
            let html = '';
            
            // Construction du HTML (inchangé)
            if (!currentStartCoords) {
                html += `<div class="new-block-field"><label class="new-block-label">Départ :</label><input type="text" id="inputStartBlock" class="new-block-input"></div>`;
            } else {
                html += `<div class="new-block-static"><strong>Départ :</strong> ${currentStartCity}</div>`;
            }
 
            html += `<div class="new-block-field"><label class="new-block-label">Arrivée :</label><input type="text" id="inputEndBlock" class="new-block-input"></div>
                     <div class="new-block-actions">
                        <button id="btnCancelBlock" class="btn-block-action btn-block-cancel">Annuler</button>
                        <button id="btnValidateBlock" class="btn-block-action btn-block-validate">Valider</button>
                     </div>`;
 
            newBlockFormContainer.innerHTML = html;

            // --- MODIFICATION : AJOUT DES MENUS DÉROULANTS ---
            
            // 1. Menu pour le DÉPART (seulement si c'est le tout premier trajet)
            const inputStart = document.getElementById('inputStartBlock');
            if (inputStart) {
                const selectStart = createFavSelectForInput('inputStartBlock');
                if (selectStart) {
                    inputStart.parentNode.insertBefore(selectStart, inputStart);
                }
                initAutocomplete(inputStart);
            }
            
            // 2. Menu pour l'ARRIVÉE (toujours présent)
            const inputEnd = document.getElementById('inputEndBlock');
            if (inputEnd) {
                const selectEnd = createFavSelectForInput('inputEndBlock');
                if (selectEnd) {
                    inputEnd.parentNode.insertBefore(selectEnd, inputEnd);
                }
                initAutocomplete(inputEnd);
            }
            // ---------------------------------------------------
 
            // Gestion des boutons Annuler / Valider (Code existant conservé)
            document.getElementById('btnCancelBlock').addEventListener('click', () => {
                newBlockFormContainer.innerHTML = '';
                btnAddSegment.style.display = 'block';
            });
 
            document.getElementById('btnValidateBlock').addEventListener('click', async () => {
                const btn = document.getElementById('btnValidateBlock');
                btn.disabled = true; btn.textContent = "Calcul...";
                
                let startName, startCoords;
                const inputStartEl = document.getElementById('inputStartBlock');
                
                if (inputStartEl) {
                    const lat = inputStartEl.getAttribute('data-lat');
                    const lon = inputStartEl.getAttribute('data-lon');
                    
                    startName = inputStartEl.value.trim();
                    startCoords = (lat && lon) ? [parseFloat(lat), parseFloat(lon)] : await getCoordonnees(startName);
                    
                    if(!startCoords) { alert('Départ introuvable'); btn.disabled=false; return; }
                    addMarker(startName, startCoords, "ville", startName);
                } else {
                    startName = currentStartCity;
                    startCoords = currentStartCoords;
                }
                
                const inputEndEl = document.getElementById('inputEndBlock');
                const eLat = inputEndEl.getAttribute('data-lat');
                const eLon = inputEndEl.getAttribute('data-lon');
                const endName = inputEndEl.value.trim();
                
                const endCoords = (eLat && eLon) ? [parseFloat(eLat), parseFloat(eLon)] : await getCoordonnees(endName);
                
                if(!endCoords) { alert('Arrivée introuvable'); btn.disabled=false; return; }
                
                await _ajouterSegmentEntre(startName, startCoords, endName, endCoords, segments.length, strategies['Voiture']);
                addMarker(endName, endCoords, "ville", endName);
                
                currentStartCity = endName;
                currentStartCoords = endCoords; // On garde les coordonnées réelles pour le prochain trajet
                
                newBlockFormContainer.innerHTML = '';
                btnAddSegment.style.display = 'block';
            });
        });
    }

    // ============================================================
    // 6. CALCUL D'ITINÉRAIRE (MODIFIÉ)
    // ============================================================

    async function updateRouteSegment(index, mode, options = {}) {
        const seg = segments[index];
        if (!seg) return;

        // Mapping des serveurs spécifiques pour chaque mode (plus fiable que le profil seul)
        const servers = {
            'Voiture': 'https://routing.openstreetmap.de/routed-car',
            'Velo': 'https://routing.openstreetmap.de/routed-bike',
            'Marche': 'https://routing.openstreetmap.de/routed-foot'
        };
        
        const baseUrl = servers[mode] || servers['Voiture'];

        // Préparation des coordonnées (Long,Lat pour OSRM)
        let points = [seg.startCoord];
        if (seg.sousEtapes) {
            seg.sousEtapes.forEach(se => { if(se.coords) points.push(se.coords); });
        }
        points.push(seg.endCoord);
        const coordString = points.map(p => `${p[1]},${p[0]}`).join(';');

        const url = `${baseUrl}/route/v1/driving/${coordString}?overview=full&geometries=geojson&continue_straight=true`;

        try {
            const resp = await fetch(url);
            const data = await resp.json();
            
            if (data.code === 'Ok') {
                const route = data.routes[0];

                // 1. SUPPRESSION RADICALE : On retire l'ancienne ligne de la carte
                if (seg.line) {
                    map.removeLayer(seg.line);
                }

                // 2. MISE À JOUR DES DONNÉES
                seg.distance = route.distance;
                seg.duration = route.duration;
                seg.modeTransport = mode;

                // 3. CRÉATION DE LA NOUVELLE LIGNE
                // On change le style (pointillés pour vélo/marche) pour vérifier visuellement
                const lineStyle = {
                    color: seg.couleurSegment,
                    weight: 6,
                    opacity: 0.8,
                    dashArray: mode !== 'Voiture' ? '10, 10' : null // Pointillés si pas voiture
                };

                seg.line = L.geoJSON(route.geometry, lineStyle).addTo(map);

                // 4. MISE À JOUR DE LA LÉGENDE
                updateLegendHtml(index);
                
                console.log(`Succès ! Mode: ${mode}, Route mise à jour.`);
            }
        } catch (e) {
            console.error("Erreur de mise à jour de la route :", e);
        }
    }
    // ============================================================
    // 7. ÉDITEUR SOUS-ÉTAPES ET LÉGENDE (MODIFIÉ)
    // ============================================================

    let currentSegmentIndex = null;
    const subEtapesContainer = document.getElementById('subEtapesContainer');
    
    function updateLegendHtml(index) {
        const seg = segments[index];
        const li = document.querySelector(`li[data-index="${index}"]`);
        if(!li || !seg.heure_depart) return;

        const ul = li.querySelector('.sousEtapesList');
        let html = ``;
        
        // 1. Heure de départ initiale (ex: "08:00")
        let currentClock = seg.heure_depart; 

        // Fonction utilitaire pour ajouter du temps (secondes) à une heure (HH:mm)
        function addTime(startTime, secondsToAdd) {
            const [h, m] = startTime.split(':').map(Number);
            const date = new Date();
            date.setHours(h, m, 0);
            date.setSeconds(date.getSeconds() + secondsToAdd);
            return date.getHours().toString().padStart(2, '0') + ":" + 
                date.getMinutes().toString().padStart(2, '0');
        }

        // Fonction utilitaire pour convertir "HH:mm" en secondes
        function durationToSeconds(timeStr) {
            const [h, m] = timeStr.split(':').map(Number);
            return (h * 3600) + (m * 60);
        }

        if(seg.legs) {
            // Parcours des sous-étapes et des "legs" (tronçons entre 2 points)
            seg.legs.forEach((leg, i) => {
                // On ajoute le temps de trajet du tronçon
                currentClock = addTime(currentClock, leg.duration);
                
                if (i < seg.sousEtapes.length) {
                    const se = seg.sousEtapes[i];
                    html += `<li style="margin-top:5px; border-left:2px solid ${seg.couleurSegment}; padding-left:5px;">
                                <b>Arrivée à ${getNomSimple(se.nom)} : ${currentClock}</b><br>
                                <small>Pause de ${se.heure}h</small></li>`;
                    
                    // On ajoute le temps de pause passé sur place
                    currentClock = addTime(currentClock, durationToSeconds(se.heure));
                    html += `<li style="list-style:none; font-size:0.8em; color:gray;">(Départ prévu à ${currentClock})</li>`;
                }
            });
        }

        const distKm = (seg.distance / 1000).toFixed(1);
        const totalTimeStr = formatDuration(seg.duration);
        let statsHtml = `<div style="font-size:0.85em; color:#666; margin-bottom:5px;">📏 ${distKm} km | ⏱️ ${totalTimeStr} de route</div>`;
        
        html += `<li style="margin-top:5px; font-weight:bold; color:#2c3e50;">🏁 Arrivée finale : ${currentClock}</li>`;
        
        ul.innerHTML = statsHtml + html;
    }

    document.getElementById('legendList').addEventListener('click', (e) => {
        if (e.target.classList.contains('modifierSousEtapes')) {
            openSegmentEditor(e.target.closest('li').dataset.index);
        }
        if (e.target.classList.contains('toggleSousEtapes')) {
             const ul = e.target.closest('li').querySelector('.sousEtapesList');
             ul.style.display = (ul.style.display === 'none') ? 'block' : 'none';
        }
    });

    function openSegmentEditor(index) {
        currentSegmentIndex = index;
        const seg = segments[index];
        document.getElementById('segmentFormContainer').style.display = 'block';
        document.getElementById('segmentTitle').textContent = `${seg.startNameSimple} → ${seg.endNameSimple}`;
        subEtapesContainer.innerHTML = '';
        if (seg.sousEtapes && seg.sousEtapes.length > 0) seg.sousEtapes.forEach(se => addSousEtapeForm(se));
        else addSousEtapeForm();
    }
    
    // Ajout d'une ligne de formulaire sous-étape
    function addSousEtapeForm(data = {}) {
        const uniqueId = 'editor-' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        const clone = document.getElementById('template-sub-etape').content.cloneNode(true);
        const div = clone.querySelector('.subEtape');
        
        const inputNom = div.querySelector('.subEtapeNom'); 
        inputNom.value = data.nom || '';
        div.querySelector('.subEtapeHeure').value = data.heure || '';
        
        // --- CRÉATION DU SELECT FAVORIS ---
        if (userFavorites.length > 0) {
            const selectFav = document.createElement('select');
            // MODIFICATION ICI : même style harmonisé
            selectFav.style.cssText = "width: 100%; margin-bottom: 10px; padding: 10px 14px; border: 1px solid #ddd; border-radius: 15px; background-color: #fff; color: #555; font-size: 1rem; cursor: pointer;";
            
            let optionsHtml = '<option value="">⭐ Insérer un favori...</option>';
            userFavorites.forEach(fav => {
                const icon = (fav.categorie === 'restaurant') ? '🍽️' : 
                             (fav.categorie === 'hotel') ? '🏨' : '📍';
                optionsHtml += `<option value="${fav.nom_lieu}">${icon} ${fav.nom_lieu}</option>`;
            });
            
            selectFav.innerHTML = optionsHtml;
            
            selectFav.addEventListener('change', function() {
                if(this.value) {
                    inputNom.value = this.value;
                    inputNom.style.backgroundColor = '#e8f8f5';
                    setTimeout(() => { inputNom.style.backgroundColor = ''; }, 500);
                }
            });
            
            inputNom.parentNode.insertBefore(selectFav, inputNom);
        }
        // ---------------------------------------------

        const txt = div.querySelector('.subEtapeRemarque');
        txt.id = uniqueId; 
        txt.value = data.remarque || '';
        
        subEtapesContainer.appendChild(div);
        initAutocomplete(inputNom); 
        
        setTimeout(() => {
            tinymce.init({
                selector: '#' + uniqueId,
                
                base_url: '/js/tinymce',
                suffix: '.min',
                license_key: 'gpl',
                
                height: 300,
                menubar: false,
                statusbar: false,
                language: 'fr_FR', 

                plugins: 'image link lists table code help wordcount',
                toolbar: 'undo redo | bold italic | bullist numlist | link image | table | code',

                /* --- GESTION DES IMAGES --- */
                image_title: true,
                automatic_uploads: true,
                // L'URL de ton script PHP créé à l'étape 1
                images_upload_url: '/formulaire/traitementImageTiny.php', 
                file_picker_types: 'image',
                
                // Empêche TinyMCE de convertir les URLs en relatif "../uploads" qui casseraient sur d'autres pages
                relative_urls: false, 
                remove_script_host: false,
                convert_urls: true,

                // Callback pour la compression et l'upload
                file_picker_callback: (cb, value, meta) => {
                    const input = document.createElement('input');
                    input.setAttribute('type', 'file');
                    input.setAttribute('accept', 'image/*');

                    input.addEventListener('change', (e) => {
                        const file = e.target.files[0];
                        if (!file) return;

                        const reader = new FileReader();
                        reader.readAsDataURL(file);
                        reader.onload = (readerEvent) => {
                            const img = new Image();
                            img.src = readerEvent.target.result;

                            img.onload = () => {
                                // Paramètres de compression
                                const MAX_WIDTH = 1200;
                                const MAX_HEIGHT = 1200;
                                let width = img.width;
                                let height = img.height;

                                // Calcul du redimensionnement
                                if (width > height) {
                                    if (width > MAX_WIDTH) {
                                        height *= MAX_WIDTH / width;
                                        width = MAX_WIDTH;
                                    }
                                } else {
                                    if (height > MAX_HEIGHT) {
                                        width *= MAX_HEIGHT / height;
                                        height = MAX_HEIGHT;
                                    }
                                }

                                // Création du Canvas pour dessiner l'image redimensionnée
                                const canvas = document.createElement('canvas');
                                canvas.width = width;
                                canvas.height = height;
                                const ctx = canvas.getContext('2d');
                                ctx.drawImage(img, 0, 0, width, height);

                                // Conversion en Blob (Fichier compressé)
                                // Le 0.7 correspond à 70% de qualité JPEG
                                canvas.toBlob((blob) => {
                                    const newFile = new File([blob], file.name, { 
                                        type: 'image/jpeg', 
                                        lastModified: Date.now() 
                                    });

                                    // Ajout au cache de TinyMCE pour déclencher l'upload automatique
                                    const id = 'blobid' + (new Date()).getTime();
                                    const blobCache = tinymce.activeEditor.editorUpload.blobCache;
                                    const blobInfo = blobCache.create(id, newFile, blob);
                                    
                                    blobCache.add(blobInfo);

                                    // Appelle le callback pour afficher l'image (en base64) en attendant l'upload réel
                                    cb(blobInfo.blobUri(), { title: file.name });
                                    
                                }, 'image/jpeg', 0.7); 
                            };
                        };
                    });
                    input.click();
                }
            });
        }, 100);

        div.querySelector('.removeSubEtapeBtn').addEventListener('click', () => {
            tinymce.get(uniqueId)?.remove();
            div.remove();
        });
    }

    document.getElementById('addSubEtape').onclick = () => addSousEtapeForm();
    document.getElementById('closeSegmentForm').onclick = () => document.getElementById('segmentFormContainer').style.display = 'none';

    document.getElementById('saveSegment').onclick = async () => {
        const seg = segments[currentSegmentIndex];
        const newSubs = [];
        // On récupère toutes les divs subEtape
        for (const div of document.querySelectorAll('.subEtape')) {
            const inputNom = div.querySelector('.subEtapeNom'); // On cible l'élément input direct
            const nom = inputNom.value.trim();
            const heure = div.querySelector('.subEtapeHeure').value;
            // Récupération contenu TinyMCE
            const idEditor = div.querySelector('.subEtapeRemarque').id;
            const remarque = tinymce.get(idEditor) ? tinymce.get(idEditor).getContent() : "";
            
            if(!nom || !heure) continue;

            // --- CORRECTION ICI ---
            let coords = null;
            const latAttr = inputNom.getAttribute('data-lat');
            const lonAttr = inputNom.getAttribute('data-lon');

            // 1. Si Photon a déjà donné les coords (via le clic autocomplétion), on les utilise !
            if (latAttr && lonAttr) {
                coords = [parseFloat(latAttr), parseFloat(lonAttr)];
            } else {
                // 2. Sinon (saisie manuelle sans clic), on demande à Nominatim
                coords = await getCoordonnees(nom);
            }
            // ----------------------

            if(coords) {
                newSubs.push({ nom, heure, remarque, coords, lat: coords[0], lon: coords[1] });
                addMarker(nom, coords, "sous_etape", `<b>${nom}</b><br>${heure}`);
            } else {
                alert(`Impossible de localiser : ${nom}. Veuillez sélectionner une suggestion dans la liste.`);
            }
        }
        
        seg.sousEtapes = newSubs;
        await updateRouteSegment(currentSegmentIndex, seg.modeTransport || 'Voiture');
        document.getElementById('segmentFormContainer').style.display = 'none';
    };

    // ============================================================
    // 8. SAUVEGARDE FINALE
    // ============================================================

    document.getElementById('saveRoadtrip').onclick = async (e) => {
        if(segments.length === 0) return alert('Aucun trajet !');
        const btn = document.getElementById('saveRoadtrip');
        const oldTxt = btn.textContent; btn.textContent = "Compression & Sauvegarde..."; btn.disabled = true;

        try {
            const formData = new FormData();
            if (e.target.dataset.id) formData.append('id_roadtrip', e.target.dataset.id);
            formData.append('titre', document.getElementById('roadtripTitle').value);
            formData.append('description', document.getElementById('roadtripDescription').value);
            formData.append('visibilite', document.getElementById('roadtripVisibilite').value);
            formData.append('statut', document.getElementById('roadtripStatut').value);
            
            const fileInput = document.getElementById('roadtripPhoto');
            if(fileInput.files.length > 0) {
                const originalFile = fileInput.files[0];
                if(originalFile.type.startsWith('image/')) {
                    const compressedFile = await compresserImageJS(originalFile, 0.7, 1920);
                    formData.append('photo_cover', compressedFile);
                } else formData.append('photo_cover', originalFile);
            }

            const trajetsData = segments.map(s => ({
                depart: s.startName, departLat: s.startCoord[0], departLon: s.startCoord[1],
                arrivee: s.endName, arriveeLat: s.endCoord[0], arriveeLon: s.endCoord[1],
                mode: s.modeTransport, date: s.date, sousEtapes: s.sousEtapes,
                heure_depart: document.querySelector(`li[data-index="${segments.indexOf(s)}"] .legend-time-input`).value
            }));
            formData.append('trajets', JSON.stringify(trajetsData));

            const resp = await fetch('/formulaire/saveRoadtrip.php', { method: 'POST', body: formData });
            const json = await resp.json();
            if(json.success) { alert("Sauvegardé ! 📸"); window.location.href = "/mesRoadTrips.php"; }
            else alert("Erreur serveur : " + json.message);
        } catch(e) { console.error(e); }
        finally { btn.textContent = oldTxt; btn.disabled = false; }
    };
});