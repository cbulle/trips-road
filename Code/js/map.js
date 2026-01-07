document.addEventListener('DOMContentLoaded', async () => {

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
        Voiture: { profile: 'driving' },
        Velo: { profile: 'cycling' },
        Marche: { profile: 'walking' }
    };
 
    const segmentColors = [
        '#0B667D', '#2E8B57', '#FF7F50', '#BF092F', '#8e44ad', '#d35400', '#2980b9'
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

    function updateVisibilityState() {
        if(statusSelect && visibilitySelect) {
            if (statusSelect.value === 'brouillon') {
                visibilitySelect.value = 'prive';
                visibilitySelect.disabled = true;
            } else {
                visibilitySelect.disabled = false;
            }
        }
    }
    if(statusSelect) {
        statusSelect.addEventListener('change', updateVisibilityState);
        updateVisibilityState(); 
    }

    // ============================================================
    // 3. FONCTIONS UTILITAIRES
    // ============================================================
    
    async function getCoordonnees(ville) {
        const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(ville)}&limit=1&accept-language=fr`;
        try {
            const resp = await fetch(url);
            const data = await resp.json();
            if (data.length > 0) return [parseFloat(data[0].lat), parseFloat(data[0].lon)];
            return null;
        } catch (e) { return null; }
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
        $(element).autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: './fonctions/recherche_villes.php',
                    dataType: "json",
                    method: "GET",
                    data: { q: request.term },
                    success: function(data) {
                        response($.map(data, function(item) { return { label: item.nom_ville, value: item.nom_ville } }));
                    },
                    error: function() { response([]); }
                });
            },
            minLength: 3
        });
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
                btn.classList.remove('active');
                if(btn.dataset.mode === segData.modeTransport) btn.classList.add('active');
                btn.addEventListener('click', async () => {
                    transportBtns.forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    segments[index].modeTransport = btn.dataset.mode;
                    await updateRouteSegment(index, btn.dataset.mode, segments[index].options);
                });
            });

            clone.querySelector('.modifierSousEtapes').dataset.index = index;
            document.getElementById('legendList').appendChild(clone);
            updateDateConstraints(); 
            updateLegendHtml(index); 

        } catch (e) { console.error(e); }
    }

    const btnAddSegment = document.getElementById('btnAddSegment');
    const newBlockFormContainer = document.getElementById('newBlockForm');
 
    if (btnAddSegment) {
        btnAddSegment.addEventListener('click', () => {
            btnAddSegment.style.display = 'none'; 
            let html = !currentStartCoords ? `<div class="new-block-field"><label class="new-block-label">Départ :</label><input type="text" id="inputStartBlock" class="new-block-input"></div>` : `<div class="new-block-static"><strong>Départ :</strong> ${currentStartCity}</div>`;
            html += `<div class="new-block-field"><label class="new-block-label">Arrivée :</label><input type="text" id="inputEndBlock" class="new-block-input"></div><div class="new-block-actions"><button id="btnCancelBlock" class="btn-block-action btn-block-cancel">Annuler</button><button id="btnValidateBlock" class="btn-block-action btn-block-validate">Valider</button></div>`;
            newBlockFormContainer.innerHTML = html;
            if (!currentStartCoords) initAutocomplete(document.getElementById('inputStartBlock'));
            initAutocomplete(document.getElementById('inputEndBlock'));
            document.getElementById('btnCancelBlock').onclick = () => { newBlockFormContainer.innerHTML = ''; btnAddSegment.style.display = 'block'; };
            document.getElementById('btnValidateBlock').onclick = async () => {
                const btn = document.getElementById('btnValidateBlock'); btn.disabled = true; btn.textContent = "Calcul...";
                let startName = currentStartCity, startCoords = currentStartCoords;
                if (document.getElementById('inputStartBlock')) {
                    startName = document.getElementById('inputStartBlock').value.trim();
                    startCoords = await getCoordonnees(startName);
                    if(!startCoords) { alert('Départ introuvable'); btn.disabled=false; return; }
                    addMarker(startName, startCoords, "ville", startName);
                }
                const endName = document.getElementById('inputEndBlock').value.trim();
                const endCoords = await getCoordonnees(endName);
                if(!endCoords) { alert('Arrivée introuvable'); btn.disabled=false; return; }
                await _ajouterSegmentEntre(startName, startCoords, endName, endCoords, segments.length, strategies['Voiture']);
                addMarker(endName, endCoords, "ville", endName);
                currentStartCity = endName; currentStartCoords = endCoords;
                newBlockFormContainer.innerHTML = ''; btnAddSegment.style.display = 'block';
            };
        });
    }

    // ============================================================
    // 6. CALCUL D'ITINÉRAIRE (MODIFIÉ)
    // ============================================================

    async function updateRouteSegment(index, mode, options = {}) {
        const seg = segments[index];
        if (!seg) return;

        if (seg.line) map.removeLayer(seg.line);

        let profile = 'driving'; 
        if (mode === 'Velo') profile = 'cycling';
        if (mode === 'Marche') profile = 'walking';

        let coordsList = [seg.startCoord];
        if (seg.sousEtapes) {
            seg.sousEtapes.forEach(se => { if(se.coords) coordsList.push(se.coords); });
        }
        coordsList.push(seg.endCoord);
        const coordString = coordsList.map(c => `${c[1]},${c[0]}`).join(';');
        const url = `https://router.project-osrm.org/route/v1/${profile}/${coordString}?overview=full&geometries=geojson`;

        try {
            const resp = await fetch(url);
            const data = await resp.json();
            if (data.code === 'Ok') {
                const route = data.routes[0];
                seg.distance = route.distance;
                seg.duration = route.duration;
                seg.legs = route.legs;
                seg.line = L.geoJSON(route.geometry, { color: seg.couleurSegment, weight: 5 }).addTo(map);
                updateLegendHtml(index);
            }
        } catch (e) { console.error("Erreur d'itinéraire", e); }
    }

    // ============================================================
    // 7. ÉDITEUR SOUS-ÉTAPES ET LÉGENDE (MODIFIÉ)
    // ============================================================

    let currentSegmentIndex = null;
    const subEtapesContainer = document.getElementById('subEtapesContainer');
    
    function updateLegendHtml(index) {
        const seg = segments[index];
        const li = document.querySelector(`li[data-index="${index}"]`);
        if(!li) return;
        
        const distKm = (seg.distance / 1000).toFixed(1);
        const timeStr = formatDuration(seg.duration);
        let statsHtml = `<div style="font-size:0.85em; color:#666; margin-bottom:5px;">📏 ${distKm} km | ⏱️ ${timeStr}</div>`;

        const ul = li.querySelector('.sousEtapesList');
        let html = ``;
        let lastTime = null;

        if(seg.sousEtapes && seg.sousEtapes.length > 0) {
            seg.sousEtapes.forEach(se => {
                html += `<li style="margin-top:5px; border-left:2px solid ${seg.couleurSegment}; padding-left:5px;">
                            📍 ${getNomSimple(se.nom)} <small>(${se.heure})</small></li>`;
                lastTime = se.heure;
            });
        }

        if (lastTime && seg.legs) {
            const lastLegDuration = seg.legs[seg.legs.length - 1].duration;
            const eta = calculateETA(lastTime, lastLegDuration);
            html += `<li style="margin-top:5px; font-weight:bold; color:#2c3e50;">🏁 Arrivée : ${eta}</li>`;
        }
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
    
    function addSousEtapeForm(data = {}) {
        const uniqueId = 'editor-' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        const clone = document.getElementById('template-sub-etape').content.cloneNode(true);
        const div = clone.querySelector('.subEtape');
        div.querySelector('.subEtapeNom').value = data.nom || '';
        div.querySelector('.subEtapeHeure').value = data.heure || '';
        const txt = div.querySelector('.subEtapeRemarque');
        txt.id = uniqueId; 
        div.querySelector('.removeSubEtapeBtn').onclick = () => { if(tinymce.get(uniqueId)) tinymce.get(uniqueId).remove(); div.remove(); };
        subEtapesContainer.appendChild(div);
        initAutocomplete(div.querySelector('.subEtapeNom'));
        setTimeout(() => {
            tinymce.init({ selector: '#' + uniqueId, base_url: '/js/tinymce', suffix: '.min', license_key: 'gpl', promotion: false, branding: false, menubar: false, statusbar: false, min_height: 150, plugins: 'image link lists', toolbar: 'undo redo | bold italic | bullist | link image', })
            .then(editors => { if (data.remarque && editors.length > 0) editors[0].setContent(data.remarque); });
        }, 100);
    }

    document.getElementById('addSubEtape').onclick = () => addSousEtapeForm();
    document.getElementById('closeSegmentForm').onclick = () => document.getElementById('segmentFormContainer').style.display = 'none';

    document.getElementById('saveSegment').onclick = async () => {
        const seg = segments[currentSegmentIndex];
        const newSubs = [];
        for (const div of document.querySelectorAll('.subEtape')) {
            const nom = div.querySelector('.subEtapeNom').value.trim();
            const heure = div.querySelector('.subEtapeHeure').value;
            const remarque = tinymce.get(div.querySelector('.subEtapeRemarque').id).getContent();
            if(!nom || !heure) continue;
            const coords = await getCoordonnees(nom);
            if(coords) {
                newSubs.push({ nom, heure, remarque, coords, lat: coords[0], lon: coords[1] });
                addMarker(nom, coords, "sous_etape", `<b>${nom}</b><br>${heure}`);
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
                mode: s.modeTransport, date: s.date, sousEtapes: s.sousEtapes
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