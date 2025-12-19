// --- map.js ---
document.addEventListener('DOMContentLoaded', async () => {
 
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
    // 2. GESTION DU STATUT & VISIBILITÉ (UI Logic)
    // ============================================================
    const statusSelect = document.getElementById('roadtripStatut');
    const visibilitySelect = document.getElementById('roadtripVisibilite');

    function updateVisibilityState() {
        if (statusSelect.value === 'brouillon') {
            // Si brouillon, forcer Privé et désactiver
            visibilitySelect.value = 'prive';
            visibilitySelect.disabled = true;
        } else {
            // Si terminé, activer le choix
            visibilitySelect.disabled = false;
        }
    }
    
    if(statusSelect && visibilitySelect) {
        statusSelect.addEventListener('change', updateVisibilityState);
        updateVisibilityState(); // Init
    }


    // ============================================================
    // 3. FONCTIONS UTILITAIRES
    // ============================================================
    
    async function getCoordonnees(ville) {
        // ... (Code existant inchangé) ...
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

    // ============================================================
    // 4. LOGIQUE DATES INTELLIGENTES
    // ============================================================
    
    function updateDateConstraints() {
        // Parcours tous les inputs date de la légende dans l'ordre
        const dateInputs = document.querySelectorAll('.legend-date-input');
        
        for (let i = 0; i < dateInputs.length; i++) {
            const currentInput = dateInputs[i];
            
            // Si ce n'est pas le premier, on définit son MIN basé sur la date du précédent
            if (i > 0) {
                const prevInput = dateInputs[i - 1];
                if (prevInput.value) {
                    currentInput.min = prevInput.value;
                    
                    // Si la date actuelle est antérieure au min, on la reset ou on l'aligne
                    if (currentInput.value && currentInput.value < prevInput.value) {
                        currentInput.value = prevInput.value;
                    }
                }
            }
            
            // Ajout d'un écouteur pour propager le changement
            currentInput.addEventListener('change', updateDateConstraints);
        }
    }


    // ============================================================
    // 5. CHARGEMENT MODE ÉDITION
    // ============================================================

    if (typeof MODE_EDITION !== 'undefined' && MODE_EDITION === true && EXISTING_TRAJETS) {
        console.log("Mode édition activé. Chargement des trajets...");
        await loadExistingRoadTrip();
    } else {
        // Mode création standard : Marker sur ville départ user
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
        // Pour chaque trajet existant, on le reconstruit
        // On doit le faire séquentiellement pour garder l'ordre
        for (let i = 0; i < EXISTING_TRAJETS.length; i++) {
            const t = EXISTING_TRAJETS[i];
            
            // Récup coordonnées (normalement on devrait les avoir stockées, mais on les re-fetch pour être sûr)
            const startCoords = await getCoordonnees(t.depart);
            const endCoords = await getCoordonnees(t.arrivee);
            
            if (startCoords && endCoords) {
                // Créer le segment
                // Note : On passe la date ici pour qu'elle soit mise dans l'input
                await _ajouterSegmentEntre(t.depart, startCoords, t.arrivee, endCoords, segments.length, strategies['Voiture'], t);
                
                // Mettre à jour la variable globale pour le prochain ajout manuel
                currentStartCity = t.arrivee;
                currentStartCoords = endCoords;
                
                // Ajouter marqueurs
                addMarker(t.depart, startCoords, "ville", t.depart);
                addMarker(t.arrivee, endCoords, "ville", t.arrivee);
                
                // Traiter les sous-étapes
                const currentSeg = segments[segments.length - 1]; // Le dernier ajouté
                if (t.sousEtapes && t.sousEtapes.length > 0) {
                     // On remplit le tableau du segment
                     currentSeg.sousEtapes = t.sousEtapes;
                     // On ajoute les marqueurs sur la carte
                     for (const se of t.sousEtapes) {
                         const c = await getCoordonnees(se.nom);
                         if(c) addMarker(se.nom, c, "sous_etape", `<b>${se.nom}</b><br>${se.heure}`);
                     }
                     // On met à jour l'affichage HTML de la liste
                     updateLegendHtml(segments.length - 1);
                }
            }
        }
        
        // Centrer la carte
        if (segments.length > 0) {
            const group = new L.featureGroup(segments.map(s => s.line));
            map.fitBounds(group.getBounds());
        }
    }


    // ============================================================
    // 6. AJOUT DE SEGMENTS
    // ============================================================
 
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

    // Fonction interne pour créer le segment visuel et logique
    // Paramètre 'existingData' optionnel pour pré-remplir (date, mode...)
    async function _ajouterSegmentEntre(startName, startCoords, endName, endCoords, index, strategy, existingData = null) {
        const coordString = `${startCoords[1]},${startCoords[0]};${endCoords[1]},${endCoords[0]}`;
        const url = `https://router.project-osrm.org/route/v1/${strategy.profile}/${coordString}?overview=full&geometries=geojson`;
 
        try {
            const resp = await fetch(url);
            const data = await resp.json();
            let geoData = (data.code === 'Ok') ? data.routes[0].geometry : { "type": "LineString", "coordinates": [[startCoords[1], startCoords[0]], [endCoords[1], endCoords[0]]] };
            
            const color = segmentColors[index % segmentColors.length];
            const line = L.geoJSON(geoData, { color: color, weight: 5 }).addTo(map);
 
            segments.push({
                line, startName, startCoord: startCoords, endName, endCoord: endCoords,
                couleurSegment: color,
                sousEtapes: [],
                startNameSimple: getNomSimple(startName),
                endNameSimple: getNomSimple(endName),
                modeTransport: existingData ? existingData.mode : 'Voiture',
                options: {},
                date: existingData ? existingData.date_trajet : '' // Stockage date
            });
 
            // AJOUT DOM LÉGENDE
            const template = document.getElementById('template-legend-item');
            const clone = template.content.cloneNode(true);
            const li = clone.querySelector('li');
            li.dataset.index = index;
            
            clone.querySelector('.legend-color-indicator').style.background = color;
            clone.querySelector('.toggleSousEtapes').innerHTML = `${getNomSimple(startName)} → ${getNomSimple(endName)}`;
            
            // Gestion DATE
            const dateInput = clone.querySelector('.legend-date-input');
            if(existingData && existingData.date_trajet) {
                dateInput.value = existingData.date_trajet;
            }
            // Au changement de date, on met à jour l'objet segment et les contraintes
            dateInput.addEventListener('change', (e) => {
                segments[index].date = e.target.value;
                updateDateConstraints();
            });

            clone.querySelector('.modifierSousEtapes').dataset.index = index;
            document.getElementById('legendList').appendChild(clone);
            
            updateDateConstraints(); // Appliquer contraintes min/max
            updateLegendHtml(index); // Afficher texte de base

        } catch (e) { console.error(e); }
    }

    async function updateRouteSegment(index, mode, options = {}) {
        const seg = segments[index];
        if (!seg) return;

        seg.modeTransport = mode; 
        seg.options = options;

        // 1. Choix du profil OSRM selon le mode
        let profile = 'driving';
        if (mode === 'Velo') profile = 'cycling';
        else if (mode === 'Marche') profile = 'walking';

        // 2. Construction de la liste des points : [Départ, ...Sous-étapes, Arrivée]
        let coordsList = [seg.startCoord];

        if (seg.sousEtapes && seg.sousEtapes.length > 0) {
            for (const sub of seg.sousEtapes) {
                // On s'assure d'avoir les coordonnées
                if (!sub.coords) {
                    sub.coords = await getCoordonnees(sub.nom);
                }
                if (sub.coords) {
                    coordsList.push(sub.coords);
                }
            }
        }

        coordsList.push(seg.endCoord);

        // 3. Construction de l'URL pour l'API
        // Format OSRM : lon,lat;lon,lat;...
        const coordString = coordsList.map(c => `${c[1]},${c[0]}`).join(';');
        let url = `https://router.project-osrm.org/route/v1/${profile}/${coordString}?overview=full&geometries=geojson`;

        // Ajout des options (péages, autoroutes...)
        const excludes = [];
        if (options.excludeTolls) excludes.push('toll');
        if (options.excludeMotorways) excludes.push('motorway');
        if (excludes.length > 0) url += `&exclude=${excludes.join(',')}`;

        try {
            const resp = await fetch(url);
            const data = await resp.json();

            if (data.code !== 'Ok') {
                console.warn("Erreur calcul itinéraire", data);
                return;
            }

            // 4. Mise à jour visuelle sur la carte
            // On supprime l'ancienne ligne
            if (seg.line) map.removeLayer(seg.line);

            // On crée la nouvelle ligne passant par les points
            seg.line = L.geoJSON(data.routes[0].geometry, { 
                color: seg.couleurSegment, 
                weight: 5, 
                opacity: 0.8 
            }).addTo(map);

        } catch (e) {
            console.error("Erreur updateRouteSegment :", e);
        }
    }


    // ============================================================
    // 7. ÉDITEUR SOUS-ÉTAPES (AVEC HEURE OBLIGATOIRE)
    // ============================================================

    let currentSegmentIndex = null;
    const subEtapesContainer = document.getElementById('subEtapesContainer');
    
    // Fonction helper pour mettre à jour le HTML de la liste (légende)
    function updateLegendHtml(index) {
        const seg = segments[index];
        const li = document.querySelector(`li[data-index="${index}"]`);
        if(!li) return;
        
        const ul = li.querySelector('.sousEtapesList');
        let html = `<li><strong>Départ:</strong> ${seg.startNameSimple}</li>`;
        
        if(seg.sousEtapes && seg.sousEtapes.length > 0) {
            seg.sousEtapes.forEach(se => {
                html += `<li style="margin-top:5px; border-left:2px solid ${seg.couleurSegment}; padding-left:5px;">
                            📍 ${getNomSimple(se.nom)} 
                            <small>(${se.heure})</small>
                         </li>`;
            });
        }
        
        html += `<li><strong>Arrivée:</strong> ${seg.endNameSimple}</li>`;
        ul.innerHTML = html;
    }

    // Ouverture editeur
    document.getElementById('legendList').addEventListener('click', (e) => {
        if (e.target.classList.contains('modifierSousEtapes')) {
            const idx = e.target.dataset.index;
            openSegmentEditor(idx);
        }
    });

    function openSegmentEditor(index) {
        currentSegmentIndex = index;
        const seg = segments[index];
        
        document.getElementById('segmentFormContainer').style.display = 'block';
        subEtapesContainer.innerHTML = '';
        
        if (seg.sousEtapes) seg.sousEtapes.forEach(se => addSousEtapeForm(se));
    }
    
    // Ajout d'une ligne de formulaire sous-étape
    function addSousEtapeForm(data = {}) {
        const uniqueId = 'editor-' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        const clone = document.getElementById('template-sub-etape').content.cloneNode(true);
        const div = clone.querySelector('.subEtape');
        
        div.querySelector('.subEtapeNom').value = data.nom || '';
        div.querySelector('.subEtapeHeure').value = data.heure || '';
        
        const txt = div.querySelector('.subEtapeRemarque');
        txt.id = uniqueId; 
        txt.value = data.remarque || '';
        
        subEtapesContainer.appendChild(div);
        initAutocomplete(div.querySelector('.subEtapeNom'));
        
        // Init TinyMCE avec configuration complète
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
                min_height: 200,

                plugins: 'image link lists table code help wordcount',
                toolbar: 'undo redo | bold italic | bullist | link image',

                image_title: true,
                automatic_uploads: true,
                images_upload_url: '/formulaire/traitementImageTiny.php', 
                file_picker_types: 'image',

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
                                const MAX_WIDTH = 1200;
                                const MAX_HEIGHT = 1200;
                                let width = img.width;
                                let height = img.height;

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

                                const canvas = document.createElement('canvas');
                                canvas.width = width;
                                canvas.height = height;
                                const ctx = canvas.getContext('2d');
                                ctx.drawImage(img, 0, 0, width, height);

                                canvas.toBlob((blob) => {
                                    const newFile = new File([blob], file.name, { 
                                        type: 'image/jpeg', 
                                        lastModified: Date.now() 
                                    });

                                    const id = 'blobid' + (new Date()).getTime();
                                    const blobCache = tinymce.activeEditor.editorUpload.blobCache;

                                    const reader2 = new FileReader();
                                    reader2.readAsDataURL(newFile);
                                    reader2.onload = (e2) => {
                                        const base64 = e2.target.result.split(',')[1];
                                        const blobInfo = blobCache.create(id, newFile, base64);
                                        blobCache.add(blobInfo);
                                        
                                        cb(blobInfo.blobUri(), { title: file.name });
                                    };

                                }, 'image/jpeg', 0.7); 
                            };
                        };
                    });
                    input.click();
                }
            }).then(editors => {
                // Si on a du contenu existant, on l'ajoute
                if (data.remarque && editors.length > 0) {
                    editors[0].setContent(data.remarque);
                }
            });
        }, 100);

        div.querySelector('.removeSubEtapeBtn').addEventListener('click', () => {
            tinymce.get(uniqueId)?.remove();
            div.remove();
        });
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
                alert("L'heure est obligatoire pour toutes les sous-étapes.");
                error = true;
                break;
            }

            const coords = await getCoordonnees(nom);

            newSubs.push({ 
                nom: nom, 
                heure: heure, 
                remarque: remarque,
                coords: coords 
            });
            if(coords) addMarker(nom, coords, "sous_etape", `<b>${nom}</b><br>${heure}`);
        }
        
        if(error) return;

        seg.sousEtapes = newSubs;

        await updateRouteSegment(currentSegmentIndex, seg.modeTransport || 'Voiture', seg.options || {});
        
        updateLegendHtml(currentSegmentIndex);
        document.getElementById('segmentFormContainer').style.display = 'none';
    });


    // ============================================================
    // 8. SAUVEGARDE FINALE
    // ============================================================

    document.getElementById('saveRoadtrip').addEventListener('click', async (e) => {
        // Validation basique
        if(segments.length === 0) { alert('Aucun trajet !'); return; }
        
        const titre = document.getElementById('roadtripTitle').value;
        const statut = document.getElementById('roadtripStatut').value;
        
        // VERIFICATION : Dates des trajets
        for (let i = 0; i < segments.length; i++) {
            // On récupère la valeur depuis l'input DOM car l'utilisateur a pu le changer
            const li = document.querySelector(`li[data-index="${i}"]`);
            const dateInput = li.querySelector('.legend-date-input');
            
            if (!dateInput.value) {
                alert(`Attention : La date du trajet ${i+1} n'est pas renseignée !`);
                return;
            }
            segments[i].date = dateInput.value;
        }

        // Préparation FormData
        const formData = new FormData();
        
        // Gestion ID pour Modification
        const editId = e.target.dataset.id;
        if (editId) formData.append('id_roadtrip', editId);

        formData.append('titre', titre);
        formData.append('description', document.getElementById('roadtripDescription').value);
        formData.append('visibilite', document.getElementById('roadtripVisibilite').value);
        formData.append('statut', statut); // Ajout du statut
        
        const photo = document.getElementById('roadtripPhoto').files[0];
        if(photo) formData.append('photo_cover', photo);

        // Construction objet Trajets propre
        const trajetsData = segments.map(s => ({
            depart: s.startName,
            arrivee: s.endName,
            mode: s.modeTransport,
            date: s.date, // Nouvelle donnée obligatoire
            sousEtapes: s.sousEtapes // Contient nom, heure, remarque
        }));
        
        formData.append('trajets', JSON.stringify(trajetsData));
        
        // Villes pour géocodage
        const villesGeo = [];
        segments.forEach(s => {
            if(!villesGeo.includes(s.startName)) villesGeo.push(s.startName);
            if(!villesGeo.includes(s.endName)) villesGeo.push(s.endName);
            s.sousEtapes.forEach(se => {
                if(!villesGeo.includes(se.nom)) villesGeo.push(se.nom);
            });
        });
        formData.append('villes', JSON.stringify(villesGeo));

        // Envoi
        const btn = document.getElementById('saveRoadtrip');
        const oldTxt = btn.textContent;
        btn.textContent = "Sauvegarde...";
        btn.disabled = true;

        try {
            const resp = await fetch('/formulaire/saveRoadtrip.php', { method: 'POST', body: formData });
            const json = await resp.json();
            
            if(json.success) {
                alert("RoadTrip sauvegardé !");
                window.location.href = "/mesRoadTrips.php";
            } else {
                alert("Erreur: " + json.message);
            }
        } catch(e) { console.error(e); alert("Erreur technique"); }
        finally { btn.textContent = oldTxt; btn.disabled = false; }
    });
});

    async function _ajouterSegmentEntre(startN, startC, endN, endC, index, strategy, existingData = null) {
        const color = segmentColors[index % segmentColors.length];
        const line = L.polyline([startC, endC], { color, weight: 5 }).addTo(map);
        
        const modeTransport = existingData?.mode || 'Voiture';
        
        const seg = {
            line,
            startName: startN,
            endName: endN,
            startCoords: startC,
            endCoords: endC,
            color,
            modeTransport: modeTransport,
            options: {},
            sousEtapes: [],
            date: existingData?.date || '' 
        };
        
        segments.push(seg);
        
        const legendItem = createLegendItem(seg, index);
        document.getElementById('legendList').appendChild(legendItem);
        
        // Mise à jour des dates
        updateDateConstraints();
        
        // Charger l'itinéraire
        await updateRouteSegment(index, modeTransport, seg.options);
    }

    function createLegendItem(seg, index) {
        const template = document.getElementById('template-legend-item').content.cloneNode(true);
        const li = template.querySelector('li');
        li.setAttribute('data-index', index);
        
        const colorIndicator = li.querySelector('.legend-color-indicator');
        colorIndicator.style.backgroundColor = seg.color;
        
        const toggleBtn = li.querySelector('.legend-toggle-btn');
        toggleBtn.textContent = `${seg.startName} → ${seg.endName}`;
        
        // Date
        const dateInput = li.querySelector('.legend-date-input');
        if(seg.date) dateInput.value = seg.date;
        
        // Transport
        const transportBtns = li.querySelectorAll('.transport-btn');
        transportBtns.forEach(btn => {
            if(btn.dataset.mode === seg.modeTransport) btn.classList.add('active');
            else btn.classList.remove('active');
            
            btn.addEventListener('click', async () => {
                transportBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                seg.modeTransport = btn.dataset.mode;
                await updateRouteSegment(index, btn.dataset.mode, seg.options);
            });
        });
        
        // Options route
        const settingsBtn = li.querySelector('.settings-btn');
        const routePref = li.querySelector('.route-preferences');
        settingsBtn.addEventListener('click', () => {
            routePref.style.display = routePref.style.display === 'none' ? 'block' : 'none';
        });
        
        const prefCheckboxes = li.querySelectorAll('.pref-checkbox');
        prefCheckboxes.forEach(cb => {
            cb.addEventListener('change', async () => {
                const pref = cb.dataset.pref;
                seg.options[pref] = cb.checked;
                await updateRouteSegment(index, seg.modeTransport, seg.options);
            });
        });
        
        // Toggle sous-étapes
        toggleBtn.addEventListener('click', () => {
            const ul = li.querySelector('.sousEtapesList');
            ul.style.display = ul.style.display === 'none' ? 'block' : 'none';
        });
        
        // Bouton Modifier Sous-Étapes
        const btnModifier = li.querySelector('.modifierSousEtapes');
        btnModifier.addEventListener('click', () => {
            openSegmentForm(index);
        });
        
        return li;
    }

    async function updateRouteSegment(index, mode, options) {
        const seg = segments[index];
        const strat = strategies[mode] || strategies['Voiture'];
        
        let waypoints = [seg.startCoords];
        seg.sousEtapes.forEach(se => {
            if (se.coords) waypoints.push(se.coords);
        });
        waypoints.push(seg.endCoords);
        
        let opts = [];
        if (options['exclude-tolls']) opts.push('exclude=toll');
        if (options['exclude-motorways']) opts.push('exclude=motorway');
        const optsStr = opts.length ? '&' + opts.join('&') : '';
        
        const coords = waypoints.map(c => c[1] + ',' + c[0]).join(';');
        const url = `https://router.project-osrm.org/route/v1/${strat.profile}/${coords}?overview=full&geometries=geojson${optsStr}`;
        
        try {
            const resp = await fetch(url);
            const data = await resp.json();
            if (data.routes && data.routes[0]) {
                const route = data.routes[0];
                const geom = route.geometry.coordinates.map(c => [c[1], c[0]]);
                map.removeLayer(seg.line);
                seg.line = L.polyline(geom, { color: seg.color, weight: 5 }).addTo(map);
            }
        } catch(e) { console.error("Erreur OSRM:", e); }
    }

    function updateLegendHtml(index) {
        const seg = segments[index];
        const li = document.querySelector(`li[data-index="${index}"]`);
        if(!li) return;
        
        const ul = li.querySelector('.sousEtapesList');
        ul.innerHTML = '';
        seg.sousEtapes.forEach((se, i) => {
            const liSub = document.createElement('li');
            liSub.innerHTML = `<strong>${se.nom}</strong> à ${se.heure}`;
            if(se.remarque) {
                const remarkDiv = document.createElement('div');
                remarkDiv.innerHTML = se.remarque;
                remarkDiv.style.fontSize = '0.85em';
                remarkDiv.style.marginTop = '4px';
                liSub.appendChild(remarkDiv);
            }
            ul.appendChild(liSub);
        });
    }


    // ============================================================
    // 7. FORMULAIRE SOUS-ÉTAPES
    // ============================================================
    
    let currentSegmentIndex = null;
    
    function openSegmentForm(index) {
        currentSegmentIndex = index;
        const seg = segments[index];
        
        document.getElementById('segmentTitle').textContent = `${seg.startName} → ${seg.endName}`;
        const container = document.getElementById('subEtapesContainer');
        container.innerHTML = '';
        
        // Charger sous-étapes existantes
        if(seg.sousEtapes && seg.sousEtapes.length > 0) {
            seg.sousEtapes.forEach(se => {
                addSubEtapeRow(se.nom, se.heure, se.remarque);
            });
        } else {
            addSubEtapeRow();
        }
        
        document.getElementById('segmentFormContainer').style.display = 'block';
    }

    function addSubEtapeRow(nom = '', heure = '', remarque = '') {
        const template = document.getElementById('template-sub-etape').content.cloneNode(true);
        const div = template.querySelector('.subEtape');
        
        div.querySelector('.subEtapeNom').value = nom;
        div.querySelector('.subEtapeHeure').value = heure;
        
        const textarea = div.querySelector('.subEtapeRemarque');
        const uniqueId = 'remarque_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        textarea.id = uniqueId;
        
        div.querySelector('.removeSubEtapeBtn').addEventListener('click', () => {
            // Détruire l'éditeur TinyMCE avant de supprimer l'élément
            if (tinymce.get(uniqueId)) {
                tinymce.get(uniqueId).remove();
            }
            div.remove();
        });
        
        initAutocomplete(div.querySelector('.subEtapeNom'));
        document.getElementById('subEtapesContainer').appendChild(div);
        
        // Initialiser TinyMCE pour ce textarea
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
                min_height: 200,

                plugins: 'image link lists table code help wordcount',
                toolbar: 'undo redo | bold italic | bullist | link image',

                image_title: true,
                automatic_uploads: true,
                images_upload_url: '/formulaire/traitementImageTiny.php', 
                file_picker_types: 'image',

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
                                const MAX_WIDTH = 1200;
                                const MAX_HEIGHT = 1200;
                                let width = img.width;
                                let height = img.height;

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

                                const canvas = document.createElement('canvas');
                                canvas.width = width;
                                canvas.height = height;
                                const ctx = canvas.getContext('2d');
                                ctx.drawImage(img, 0, 0, width, height);

                                canvas.toBlob((blob) => {
                                    const newFile = new File([blob], file.name, { 
                                        type: 'image/jpeg', 
                                        lastModified: Date.now() 
                                    });

                                    const id = 'blobid' + (new Date()).getTime();
                                    const blobCache = tinymce.activeEditor.editorUpload.blobCache;

                                    const reader2 = new FileReader();
                                    reader2.readAsDataURL(newFile);
                                    reader2.onload = (e2) => {
                                        const base64 = e2.target.result.split(',')[1];
                                        const blobInfo = blobCache.create(id, newFile, base64);
                                        blobCache.add(blobInfo);
                                        
                                        cb(blobInfo.blobUri(), { title: file.name });
                                    };

                                }, 'image/jpeg', 0.7); 
                            };
                        };
                    });
                    input.click();
                }
            }).then(editors => {
                // Si on a du contenu existant, on l'ajoute
                if (remarque && editors.length > 0) {
                    editors[0].setContent(remarque);
                }
            });
        }, 100);
    }

    document.getElementById('closeSegmentForm').addEventListener('click', () => {
        document.getElementById('segmentFormContainer').style.display = 'none';
    });

    document.getElementById('addSubEtape').addEventListener('click', () => {
        addSubEtapeRow();
    });

    document.getElementById('saveSegment').addEventListener('click', async () => {
        if(currentSegmentIndex === null) return;
        const seg = segments[currentSegmentIndex];
        
        // Supprimer marqueurs sous-étapes anciens
        seg.sousEtapes.forEach(se => {
            if(markers[se.nom]) {
                markers[se.nom].forEach(m => {
                    if(m.type === 'sous_etape') map.removeLayer(m.marker);
                });
                delete markers[se.nom];
            }
        });
        
        const subDivs = document.querySelectorAll('#subEtapesContainer .subEtape');
        const newSubs = [];
        let error = false;
        
        for(const div of subDivs) {
            const nom = getNomSimple(div.querySelector('.subEtapeNom').value.trim());
            const heure = div.querySelector('.subEtapeHeure').value;
            
            // Récupérer le contenu de TinyMCE
            const textareaId = div.querySelector('.subEtapeRemarque').id;
            const editor = tinymce.get(textareaId);
            const remarque = editor ? editor.getContent() : '';
            
            if(!nom || !heure) {
                alert("Nom et heure requis");
                error = true;
                break;
            }
            
            const coords = await getCoordonnees(nom);
            newSubs.push({
                nom: nom,
                heure: heure,
                remarque: remarque,
                coords: coords 
            });
            if(coords) addMarker(nom, coords, "sous_etape", `<b>${nom}</b><br>${heure}`);
        }
        
        if(error) return;

        seg.sousEtapes = newSubs;

        await updateRouteSegment(currentSegmentIndex, seg.modeTransport || 'Voiture', seg.options || {});
        
        updateLegendHtml(currentSegmentIndex);
        document.getElementById('segmentFormContainer').style.display = 'none';
    });


    // ============================================================
    // 8. SAUVEGARDE FINALE
    // ============================================================

    document.getElementById('saveRoadtrip').addEventListener('click', async (e) => {
        // Validation basique
        if(segments.length === 0) { alert('Aucun trajet !'); return; }
        
        const titre = document.getElementById('roadtripTitle').value;
        const statut = document.getElementById('roadtripStatut').value;
        
        // VERIFICATION : Dates des trajets
        for (let i = 0; i < segments.length; i++) {
            // On récupère la valeur depuis l'input DOM car l'utilisateur a pu le changer
            const li = document.querySelector(`li[data-index="${i}"]`);
            const dateInput = li.querySelector('.legend-date-input');
            
            if (!dateInput.value) {
                alert(`Attention : La date du trajet ${i+1} n'est pas renseignée !`);
                return;
            }
            segments[i].date = dateInput.value;
        }

        // Préparation FormData
        const formData = new FormData();
        
        // Gestion ID pour Modification
        const editId = e.target.dataset.id;
        if (editId) formData.append('id_roadtrip', editId);

        formData.append('titre', titre);
        formData.append('description', document.getElementById('roadtripDescription').value);
        formData.append('visibilite', document.getElementById('roadtripVisibilite').value);
        formData.append('statut', statut);
        
        const photo = document.getElementById('roadtripPhoto').files[0];
        if(photo) formData.append('photo_cover', photo);

        // Construction objet Trajets propre
        const trajetsData = segments.map(s => ({
            depart: s.startName,
            arrivee: s.endName,
            mode: s.modeTransport,
            date: s.date,
            sousEtapes: s.sousEtapes
        }));
        
        formData.append('trajets', JSON.stringify(trajetsData));
        
        // Villes pour géocodage
        const villesGeo = [];
        segments.forEach(s => {
            if(!villesGeo.includes(s.startName)) villesGeo.push(s.startName);
            if(!villesGeo.includes(s.endName)) villesGeo.push(s.endName);
            s.sousEtapes.forEach(se => {
                if(!villesGeo.includes(se.nom)) villesGeo.push(se.nom);
            });
        });
        formData.append('villes', JSON.stringify(villesGeo));

        // Envoi
        const btn = document.getElementById('saveRoadtrip');
        const oldTxt = btn.textContent;
        btn.textContent = "Sauvegarde...";
        btn.disabled = true;

        try {
            const resp = await fetch('/formulaire/saveRoadtrip.php', { method: 'POST', body: formData });
            const json = await resp.json();
            
            if(json.success) {
                alert("RoadTrip sauvegardé !");
                window.location.href = "/mesRoadTrips.php";
            } else {
                alert("Erreur: " + json.message);
            }
        } catch(e) { 
            console.error(e); 
            alert("Erreur technique"); 
        }
        finally { 
            btn.textContent = oldTxt; 
            btn.disabled = false; 
        }
    });