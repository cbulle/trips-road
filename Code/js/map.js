document.addEventListener('DOMContentLoaded', async () => {

    // ============================================================
    // 0. FONCTION DE COMPRESSION D'IMAGE (CÔTÉ CLIENT)
    // ============================================================
    
    /**
     * Compresse une image via un Canvas HTML5
     * @param {File} file - Le fichier original
     * @param {number} quality - Qualité JPEG (0 à 1, ex: 0.7)
     * @param {number} maxWidth - Largeur max en pixels (ex: 1920)
     * @returns {Promise<File>} - Le fichier compressé
     */
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
                    
                    // Redimensionnement proportionnel
                    if (width > maxWidth) {
                        height = Math.round(height * (maxWidth / width));
                        width = maxWidth;
                    }

                    const canvas = document.createElement('canvas');
                    canvas.width = width;
                    canvas.height = height;
                    
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);
                    
                    // Transformation en Blob JPEG
                    ctx.canvas.toBlob((blob) => {
                        if (!blob) {
                            reject(new Error("Erreur lors de la création du blob via Canvas"));
                            return;
                        }
                        // Création d'un nouveau fichier JS
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
    
    // Icône spécifique pour les favoris (Étoile dorée)
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
                
                // Création de la popup avec des boutons d'action
                const container = document.createElement('div');
                container.style.textAlign = 'center';
                
                container.innerHTML = `
                    <strong style="color:#d35400">${fav.nom_lieu}</strong><br>
                    <small>${fav.categorie}</small><br>
                    <div style="margin-top:10px; display:flex; flex-direction:column; gap:5px;">
                        <button class="btn-fav-action btn-start" style="background:#27ae60; color:white; border:none; padding:5px; cursor:pointer; border-radius:3px;">🚩 Définir comme Départ</button>
                        <button class="btn-fav-action btn-end" style="background:#c0392b; color:white; border:none; padding:5px; cursor:pointer; border-radius:3px;">🏁 Définir comme Arrivée</button>
                        <button class="btn-fav-action btn-step" style="background:#2980b9; color:white; border:none; padding:5px; cursor:pointer; border-radius:3px;">📍 Ajouter comme Étape</button>
                    </div>
                `;

                // --- ACTION : DÉFINIR COMME DÉPART ---
                container.querySelector('.btn-start').addEventListener('click', () => {
                    // Si le formulaire d'ajout de segment n'est pas ouvert, on l'ouvre
                    const btnAdd = document.getElementById('btnAddSegment');
                    if (btnAdd && btnAdd.style.display !== 'none') {
                        btnAdd.click();
                    }
                    
                    // Remplir le champ "Départ"
                    setTimeout(() => {
                        const inputStart = document.getElementById('inputStartBlock');
                        if (inputStart) {
                            inputStart.value = fav.nom_lieu;
                            // On stocke les coordonnées manuellement pour éviter d'avoir à refaire une recherche API
                            // Astuce : on simule le comportement de l'autocomplétion ou on met à jour la variable globale
                            currentStartCity = fav.nom_lieu;
                            currentStartCoords = [lat, lon];
                            // Flash visuel pour confirmer
                            inputStart.style.backgroundColor = '#d5f5e3';
                        } else {
                            // Cas où le départ est déjà fixé (2ème segment), on ne peut pas le changer ici facilement
                            alert("Le départ est déjà fixé par l'arrivée du segment précédent.");
                        }
                    }, 100);
                    marker.closePopup();
                });

                // --- ACTION : DÉFINIR COMME ARRIVÉE ---
                container.querySelector('.btn-end').addEventListener('click', () => {
                     // Si le formulaire n'est pas ouvert, on l'ouvre
                    const btnAdd = document.getElementById('btnAddSegment');
                    if (btnAdd && btnAdd.style.display !== 'none') {
                        btnAdd.click();
                    }

                    setTimeout(() => {
                        const inputEnd = document.getElementById('inputEndBlock');
                        if (inputEnd) {
                            inputEnd.value = fav.nom_lieu;
                            inputEnd.style.backgroundColor = '#d5f5e3';
                            // Note: Le clic sur "Valider" fera la recherche de coordonnées via l'API, 
                            // mais comme le nom est exact, ça marchera. 
                        }
                    }, 100);
                    marker.closePopup();
                });

                // --- ACTION : AJOUTER COMME SOUS-ÉTAPE ---
                container.querySelector('.btn-step').addEventListener('click', () => {
                    const formContainer = document.getElementById('segmentFormContainer');
                    
                    // Vérifier si l'éditeur de sous-étapes est ouvert
                    if (formContainer.style.display === 'block') {
                        // Ajouter une ligne dans le formulaire
                        // On appelle la fonction addSubEtapeForm existante (définie plus bas dans ton code)
                        // On doit s'assurer que addSubEtapeForm est accessible ou déplacée dans le scope
                        
                        // Petite astuce : on cherche le bouton "Ajouter une étape" du DOM et on simule un clic, 
                        // puis on remplit le dernier champ créé.
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
                        alert("Veuillez d'abord ouvrir le mode modification d'un trajet (cliquez sur 'Modifier les étapes' dans la légende) pour ajouter ce favori.");
                    }
                });

                marker.bindPopup(container);
            });
        } catch (e) {
            console.error("Erreur chargement favoris", e);
        }
    }

    // Lancer le chargement
    loadMapFavorites();
 
    // ============================================================
    // 2. GESTION DU STATUT & VISIBILITÉ (UI Logic)
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
        console.log("Mode édition activé. Chargement...");
        await loadExistingRoadTrip();
    } else {
        if (currentStartCity) {
            getCoordonnees(currentStartCity).then(coords => {
                if (coords) {
                    currentStartCoords = coords;
                    addMarker(currentStartCity, currentStartCoords, "ville", `Départ : ${currentStartCity}`);
                    map.setView(currentStartCoords, 10);
                }
            });
        }
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
                
                const dataForJs = {
                    mode: t.mode,
                    date_trajet: t.date_trajet || t.date,
                    sousEtapes: sousEtapesWithCoords 
                };

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
            existingData.sousEtapes.forEach(se => {
                if(se.coords) coordsList.push(se.coords);
            });
        }
        coordsList.push(endCoords);

        const coordString = coordsList.map(c => `${c[1]},${c[0]}`).join(';');
        const url = `https://router.project-osrm.org/route/v1/${currentProfile}/${coordString}?overview=full&geometries=geojson`;
 
        try {
            const resp = await fetch(url);
            const data = await resp.json();
            let geoData = (data.code === 'Ok') ? data.routes[0].geometry : { "type": "LineString", "coordinates": [[startCoords[1], startCoords[0]], [endCoords[1], endCoords[0]]] };
            
            const color = segmentColors[index % segmentColors.length];
            const line = L.geoJSON(geoData, { color: color, weight: 5 }).addTo(map);
 
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
                date: existingData ? existingData.date_trajet : ''
            };
            segments.push(segData);
 
            // AJOUT DOM LÉGENDE
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
            
            const settingsBtn = clone.querySelector('.settings-btn');
            const routePref = clone.querySelector('.route-preferences');
            settingsBtn.addEventListener('click', () => {
                routePref.style.display = routePref.style.display === 'none' ? 'block' : 'none';
            });
            
            clone.querySelectorAll('.pref-checkbox').forEach(cb => {
                cb.addEventListener('change', async () => {
                    segments[index].options[cb.dataset.pref] = cb.checked;
                    await updateRouteSegment(index, segments[index].modeTransport, segments[index].options);
                });
            });

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
            let html = '';
            
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
            if (!currentStartCoords) initAutocomplete(document.getElementById('inputStartBlock'));
            initAutocomplete(document.getElementById('inputEndBlock'));
 
            document.getElementById('btnCancelBlock').addEventListener('click', () => {
                newBlockFormContainer.innerHTML = '';
                btnAddSegment.style.display = 'block';
            });
 
            document.getElementById('btnValidateBlock').addEventListener('click', async () => {
                const btn = document.getElementById('btnValidateBlock');
                btn.disabled = true; btn.textContent = "Calcul...";
                
                let startName = currentStartCity, startCoords = currentStartCoords;
                const inputStart = document.getElementById('inputStartBlock');
                
                if (inputStart) {
                    startName = inputStart.value.trim();
                    startCoords = await getCoordonnees(startName);
                    if(!startCoords) { alert('Départ introuvable'); btn.disabled=false; return; }
                    addMarker(startName, startCoords, "ville", startName);
                }
                
                const endName = document.getElementById('inputEndBlock').value.trim();
                const endCoords = await getCoordonnees(endName);
                if(!endCoords) { alert('Arrivée introuvable'); btn.disabled=false; return; }
                
                await _ajouterSegmentEntre(startName, startCoords, endName, endCoords, segments.length, strategies['Voiture']);
                addMarker(endName, endCoords, "ville", endName);
                
                currentStartCity = endName;
                currentStartCoords = endCoords;
                
                newBlockFormContainer.innerHTML = '';
                btnAddSegment.style.display = 'block';
            });
        });
    }

    async function updateRouteSegment(index, mode, options = {}) {
        const seg = segments[index];
        if (!seg) return;

        seg.modeTransport = mode; 
        seg.options = options;

        let profile = 'driving';
        if (mode === 'Velo') profile = 'cycling';
        else if (mode === 'Marche') profile = 'walking';

        let coordsList = [seg.startCoord];
        if (seg.sousEtapes && seg.sousEtapes.length > 0) {
            for (const sub of seg.sousEtapes) {
                if (!sub.coords) {
                    sub.coords = await getCoordonnees(sub.nom);
                }
                if (sub.coords) coordsList.push(sub.coords);
            }
        }
        coordsList.push(seg.endCoord);

        const coordString = coordsList.map(c => `${c[1]},${c[0]}`).join(';');
        let url = `https://router.project-osrm.org/route/v1/${profile}/${coordString}?overview=full&geometries=geojson`;

        const excludes = [];
        if (options['exclude-tolls']) excludes.push('toll');
        if (options['exclude-motorways']) excludes.push('motorway');
        if (excludes.length > 0) url += `&exclude=${excludes.join(',')}`;

        try {
            const resp = await fetch(url);
            const data = await resp.json();

            if (data.code !== 'Ok') return;

            if (seg.line) map.removeLayer(seg.line);
            seg.line = L.geoJSON(data.routes[0].geometry, { 
                color: seg.couleurSegment, 
                weight: 5, 
                opacity: 0.8 
            }).addTo(map);

        } catch (e) { console.error("Erreur updateRouteSegment :", e); }
    }


    // ============================================================
    // 7. ÉDITEUR SOUS-ÉTAPES
    // ============================================================

    let currentSegmentIndex = null;
    const subEtapesContainer = document.getElementById('subEtapesContainer');
    
    function updateLegendHtml(index) {
        const seg = segments[index];
        const li = document.querySelector(`li[data-index="${index}"]`);
        if(!li) return;
        
        const ul = li.querySelector('.sousEtapesList');
        let html = ``;
        
        if(seg.sousEtapes && seg.sousEtapes.length > 0) {
            seg.sousEtapes.forEach(se => {
                html += `<li style="margin-top:5px; border-left:2px solid ${seg.couleurSegment}; padding-left:5px;">
                            📍 ${getNomSimple(se.nom)} 
                            <small>(${se.heure})</small>
                         </li>`;
            });
        }
        ul.innerHTML = html;
    }

    document.getElementById('legendList').addEventListener('click', (e) => {
        if (e.target.classList.contains('modifierSousEtapes')) {
            const idx = e.target.closest('li').dataset.index;
            openSegmentEditor(idx);
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
        
        if (seg.sousEtapes && seg.sousEtapes.length > 0) {
            seg.sousEtapes.forEach(se => addSousEtapeForm(se));
        } else {
            addSousEtapeForm();
        }
    }
    
    function addSousEtapeForm(data = {}) {
        const uniqueId = 'editor-' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        const clone = document.getElementById('template-sub-etape').content.cloneNode(true);
        const div = clone.querySelector('.subEtape');
        
        div.querySelector('.subEtapeNom').value = data.nom || '';
        div.querySelector('.subEtapeHeure').value = data.heure || '';
        
        const txt = div.querySelector('.subEtapeRemarque');
        txt.id = uniqueId; 
        
        div.querySelector('.removeSubEtapeBtn').addEventListener('click', () => {
            if(tinymce.get(uniqueId)) tinymce.get(uniqueId).remove();
            div.remove();
        });

        subEtapesContainer.appendChild(div);
        initAutocomplete(div.querySelector('.subEtapeNom'));
        
        setTimeout(() => {
            tinymce.init({
                selector: '#' + uniqueId,
                base_url: '/js/tinymce',
                suffix: '.min',
                license_key: 'gpl',
                promotion: false,
                branding: false,
                menubar: false,
                statusbar: false,
                min_height: 150,
                plugins: 'image link lists',
                toolbar: 'undo redo | bold italic | bullist | link image',
            }).then(editors => {
                if (data.remarque && editors.length > 0) {
                    editors[0].setContent(data.remarque);
                }
            });
        }, 100);
    }

    document.getElementById('addSubEtape').addEventListener('click', () => addSousEtapeForm());
    
    document.getElementById('closeSegmentForm').addEventListener('click', () => {
        document.getElementById('segmentFormContainer').style.display = 'none';
    });

    document.getElementById('saveSegment').addEventListener('click', async () => {
        const seg = segments[currentSegmentIndex];
        const newSubs = [];
        const divs = document.querySelectorAll('.subEtape');
        let error = false;

        for (const div of divs) {
            const nom = div.querySelector('.subEtapeNom').value.trim();
            const heure = div.querySelector('.subEtapeHeure').value;
            const idInfo = div.querySelector('.subEtapeRemarque').id;
            const remarque = tinymce.get(idInfo) ? tinymce.get(idInfo).getContent() : '';
            
            if(!nom) continue;
            if(!heure) {
                alert("L'heure est obligatoire.");
                error = true;
                break;
            }

            const coords = await getCoordonnees(nom);
            if(coords) {
                newSubs.push({ 
                    nom, 
                    heure, 
                    remarque, 
                    coords, // Important pour la carte
                    lat: coords[0], // Important pour la BD
                    lon: coords[1]  // Important pour la BD
                });
                addMarker(nom, coords, "sous_etape", `<b>${nom}</b><br>${heure}`);
            }
        }
        
        if(error) return;

        seg.sousEtapes = newSubs;
        await updateRouteSegment(currentSegmentIndex, seg.modeTransport || 'Voiture', seg.options || {});
        
        updateLegendHtml(currentSegmentIndex);
        document.getElementById('segmentFormContainer').style.display = 'none';
    });


    // ============================================================
    // 8. SAUVEGARDE FINALE (AVEC COMPRESSION JS)
    // ============================================================

    document.getElementById('saveRoadtrip').addEventListener('click', async (e) => {
        if(segments.length === 0) { alert('Aucun trajet !'); return; }
        
        const titre = document.getElementById('roadtripTitle').value;
        const statut = document.getElementById('roadtripStatut').value;
        
        // Validation dates
        for (let i = 0; i < segments.length; i++) {
            const li = document.querySelector(`li[data-index="${i}"]`);
            const dateInput = li.querySelector('.legend-date-input');
            if (!dateInput.value) {
                alert(`Date manquante pour le trajet ${i+1}`);
                return;
            }
            segments[i].date = dateInput.value;
        }

        const btn = document.getElementById('saveRoadtrip');
        const oldTxt = btn.textContent;
        btn.textContent = "Compression & Sauvegarde...";
        btn.disabled = true;

        try {
            const formData = new FormData();
            const editId = e.target.dataset.id;
            if (editId) formData.append('id_roadtrip', editId);

            formData.append('titre', titre);
            formData.append('description', document.getElementById('roadtripDescription').value);
            formData.append('visibilite', document.getElementById('roadtripVisibilite').value);
            formData.append('statut', statut);
            
            // --- COMPRESSION IMAGE ---
            const fileInput = document.getElementById('roadtripPhoto');
            if(fileInput.files.length > 0) {
                const originalFile = fileInput.files[0];
                
                // On ne compresse que si c'est une image
                if(originalFile.type.startsWith('image/')) {
                    try {
                        console.log("Compression en cours...", originalFile.size);
                        // Qualité 0.7 et max 1920px de large
                        const compressedFile = await compresserImageJS(originalFile, 0.7, 1920);
                        console.log("Compression terminée :", compressedFile.size);
                        formData.append('photo_cover', compressedFile);
                    } catch (err) {
                        console.error("Erreur compression, envoi original", err);
                        formData.append('photo_cover', originalFile);
                    }
                } else {
                    formData.append('photo_cover', originalFile);
                }
            }
            // -------------------------

            // Préparation des données trajets avec Lat/Lon
            const trajetsData = segments.map(s => ({
                depart: s.startName,
                departLat: s.startCoord[0], // IMPORTANT POUR BD
                departLon: s.startCoord[1], // IMPORTANT POUR BD
                arrivee: s.endName,
                arriveeLat: s.endCoord[0],  // IMPORTANT POUR BD
                arriveeLon: s.endCoord[1],  // IMPORTANT POUR BD
                mode: s.modeTransport,
                date: s.date,
                sousEtapes: s.sousEtapes
            }));
            
            formData.append('trajets', JSON.stringify(trajetsData));
            
            const villesGeo = [];
            segments.forEach(s => {
                if(!villesGeo.includes(s.startName)) villesGeo.push(s.startName);
                if(!villesGeo.includes(s.endName)) villesGeo.push(s.endName);
            });
            formData.append('villes', JSON.stringify(villesGeo));

            // Envoi au serveur
            const resp = await fetch('/formulaire/saveRoadtrip.php', { 
                method: 'POST', 
                body: formData 
            });
            
            const text = await resp.text();
            try {
                const json = JSON.parse(text);
                if(json.success) {
                    alert("Sauvegardé avec succès ! 📸");
                    window.location.href = "/mesRoadTrips.php";
                } else {
                    alert("Erreur serveur : " + json.message);
                }
            } catch(e) { 
                console.error("Réponse invalide:", text); 
                alert("Erreur technique lors de la sauvegarde.");
            }

        } catch(e) { 
            console.error(e); 
            alert("Erreur technique : " + e.message); 
        }
        finally { 
            btn.textContent = oldTxt; 
            btn.disabled = false; 
        }
    });
});