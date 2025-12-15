// --- map.js ---
document.addEventListener('DOMContentLoaded', async () => {

    // ============================================================
    // 1. INITIALISATION DE LA CARTE & VARIABLES GLOBALES
    // ============================================================

    let map = L.map('map').setView([46.5, 2.5], 6);
    
    // Couches de tuiles
    var lightLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '@ OpenStreetMap'
    }).addTo(map);
    
    // Variables d'�tat
    let segments = [];      // Stocke les objets segments (ligne, infos, sous-�tapes)
    const markers = {};     // Stocke les r�f�rences des marqueurs pour nettoyage
    
    // �tat "Bloc par Bloc"
    let currentStartCity = (typeof USER_DEFAULT_CITY !== 'undefined') ? USER_DEFAULT_CITY : ""; 
    let currentStartCoords = null; 

    const strategies = {
        Voiture: { profile: 'driving' },
        Velo: { profile: 'cycling' },
        Marche: { profile: 'walking' }
    };

    const segmentColors = [
        'blue', 'green', 'orange', 'red', 'purple', 'brown', 'pink', 'teal', 'indigo'
    ];
    
    const europeViewbox = [-25.0, 35.0, 30.0, 71.0];

    // --- GESTION INITIALE VILLE UTILISATEUR ---
    if (currentStartCity) {
        getCoordonnees(currentStartCity).then(coords => {
            if (coords) {
                currentStartCoords = coords;
                addMarker(currentStartCity, currentStartCoords, "ville", `Départ RoadTrip : ${currentStartCity}`);
                map.setView(currentStartCoords, 10);
            } else {
                console.warn("Ville par défaut introuvable via l'API. Reset.");
                currentStartCity = ""; 
            }
        });
    }

    // ============================================================
    // 2. FONCTIONS UTILITAIRES (G�ocodage, Markers, Autocomplete)
    // ============================================================

    function getNomSimple(nomComplet) {
        if (!nomComplet) return '';
        return nomComplet.split(',')[0].trim(); 
    }

    async function getCoordonnees(ville) {
        const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(ville)}&viewbox=${europeViewbox.join(',')}&bounded=1&limit=1&accept-language=fr`;
        try {
            const resp = await fetch(url);
            const data = await resp.json();
            if (data.length > 0) return [parseFloat(data[0].lat), parseFloat(data[0].lon)];
            return null;
        } catch (e) {
            console.error(e);
            return null;
        }
    }

    function addMarker(lieu, coords, type, popupContent) {
        const marker = L.marker(coords).addTo(map).bindPopup(popupContent);
        if (!markers[lieu]) markers[lieu] = [];
        markers[lieu].push({ marker, type, text: popupContent });
        return marker;
    }

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
                        response($.map(data, function(item) {
                            return { label: item.nom_ville, value: item.nom_ville }
                        }));
                    },
                    error: function() { response([]); }
                });
            },
            minLength: 3,
            delay: 600,
            select: function(event, ui) { $(element).val(ui.item.value); }
        });
    }

    // ============================================================
    // 3. LOGIQUE D'AJOUT DE BLOCS (SEGMENTS)
    // ============================================================

    const newBlockFormContainer = document.getElementById('newBlockForm');
    const btnAddSegment = document.getElementById('btnAddSegment');

    if (btnAddSegment) {
        btnAddSegment.addEventListener('click', () => {
            btnAddSegment.style.display = 'none'; 
            
            let html = '';
            let needStartInput = false;

            if (!currentStartCoords) {
                needStartInput = true;
                html += `
                    <div style="margin-bottom:10px;">
                        <label style="display:block;font-weight:bold;margin-bottom:5px;">Ville de Départ :</label>
                        <input type="text" id="inputStartBlock" class="etape" placeholder="Ex: Paris" style="width:100%; padding:8px;">
                    </div>
                `;
            } else {
                html += `
                    <div style="margin-bottom:10px; padding: 5px; background: #f0f0f0; border-radius: 4px;">
                        <strong>Départ :</strong> ${currentStartCity}
                    </div>
                `;
            }

            html += `
                <div style="margin-bottom:10px;">
                    <label style="display:block;font-weight:bold;margin-bottom:5px;">Ville d'Arrivée :</label>
                    <input type="text" id="inputEndBlock" class="etape" placeholder="Ex: Lyon" style="width:100%; padding:8px;">
                </div>
                <div style="text-align:right; margin-top:10px;">
                    <button id="btnCancelBlock" style="background:#ccc; border:none; padding:8px 12px; cursor:pointer; margin-right:5px;">Annuler</button>
                    <button id="btnValidateBlock" style="background:#28a745; color:white; border:none; padding:8px 12px; cursor:pointer;">Valider le trajet</button>
                </div>
            `;

            newBlockFormContainer.innerHTML = html;
            newBlockFormContainer.style.display = 'block';

            if (needStartInput) initAutocomplete(document.getElementById('inputStartBlock'));
            initAutocomplete(document.getElementById('inputEndBlock'));

            document.getElementById('btnCancelBlock').addEventListener('click', () => {
                newBlockFormContainer.innerHTML = '';
                btnAddSegment.style.display = 'block';
            });

            document.getElementById('btnValidateBlock').addEventListener('click', async () => {
                const btnVal = document.getElementById('btnValidateBlock');
                btnVal.disabled = true;
                btnVal.textContent = "Calcul...";

                const inputStart = document.getElementById('inputStartBlock');
                const inputEnd = document.getElementById('inputEndBlock');

                let startName = currentStartCity;
                let startCoords = currentStartCoords;

                if (inputStart) {
                    startName = inputStart.value.trim();
                    if (!startName) { alert("Veuillez saisir un départ."); btnVal.disabled = false; return; }
                    
                    startCoords = await getCoordonnees(startName);
                    if (!startCoords) { alert(`Ville de départ introuvable : ${startName}`); btnVal.disabled = false; return; }

                    addMarker(startName, startCoords, "ville", `Départ : ${startName}`);
                }

                const endName = inputEnd.value.trim();
                if (!endName) { alert("Veuillez saisir une arrivée."); btnVal.disabled = false; return; }

                const endCoords = await getCoordonnees(endName);
                if (!endCoords) { alert(`Ville d'arrivée introuvable : ${endName}`); btnVal.disabled = false; return; }

                await _ajouterSegmentEntre(startName, startCoords, endName, endCoords, segments.length, strategies['Voiture']);
                
                addMarker(endName, endCoords, "ville", `étape : ${endName}`);

                currentStartCity = endName;
                currentStartCoords = endCoords;

                newBlockFormContainer.innerHTML = '';
                btnAddSegment.style.display = 'block';

                const allCoords = segments.flatMap(s => [s.startCoord, s.endCoord]);
                if(allCoords.length) map.fitBounds(allCoords, { padding: [50, 50] });
            });
        });
    }

    // ============================================================
    // 4. CREATION ET GESTION DES SEGMENTS (ITIN�RAIRES)
    // ============================================================

    async function _ajouterSegmentEntre(startName, startCoords, endName, endCoords, index, strategy) {
        const coordString = `${startCoords[1]},${startCoords[0]};${endCoords[1]},${endCoords[0]}`;
        const url = `https://router.project-osrm.org/route/v1/${strategy.profile}/${coordString}?overview=full&geometries=geojson`;

        try {
            const resp = await fetch(url);
            const data = await resp.json();
            
            let geoData, dist, dur;

            if (data.code === 'Ok') {
                geoData = data.routes[0].geometry;
                dist = (data.routes[0].distance / 1000).toFixed(1);
                dur = Math.floor(data.routes[0].duration / 60);
            } else {
                geoData = {
                    "type": "LineString",
                    "coordinates": [ [startCoords[1], startCoords[0]], [endCoords[1], endCoords[0]] ]
                };
                dist = 0; dur = 0; 
            }

            const couleurSegment = segmentColors[index % segmentColors.length];
            
            const line = L.geoJSON(geoData, { 
                color: couleurSegment, 
                weight: 5, 
                opacity: 0.8 
            }).addTo(map);

            const startNameSimple = getNomSimple(startName);
            const endNameSimple = getNomSimple(endName);

            segments.push({
                line,
                startName, startCoord: startCoords,
                endName, endCoord: endCoords,
                distance: dist, 
                duration: dur,
                couleurSegment,
                sousEtapes: [],
                startNameSimple, 
                endNameSimple,
                modeTransport: 'Voiture', 
                options: {}
            });

            // Ajout visuel dans la liste l�gende
            const templateLegend = document.getElementById('template-legend-item');
            const clone = templateLegend.content.cloneNode(true);
            const li = clone.querySelector('li');
            li.dataset.index = index;

            clone.querySelector('.legend-color-indicator').style.background = couleurSegment;
            
            // Titre du segment (on garde la fl�che pour le style mais elle ne masque plus rien)
            const toggleBtn = clone.querySelector('.toggleSousEtapes');
            toggleBtn.dataset.index = index;
            toggleBtn.innerHTML = `${startNameSimple} → ${endNameSimple} <strong style="margin-left:5px; color: ${couleurSegment}; border: none;">▼</strong>`;
            // Optionnel : D�sactiver le curseur pointeur si on ne veut pas que �a ait l'air cliquable
            toggleBtn.style.cursor = "default"; 

            clone.querySelector('.modifierSousEtapes').dataset.index = index;
            
            // --- MODIFICATION ICI : On force l'affichage imm�diat de la liste ---
            const ul = clone.querySelector('.sousEtapesList');
            ul.dataset.index = index;
            ul.style.display = 'block'; // Force visible
            
            // Remplissage imm�diat de la liste
            let listHtml = `<li><strong>Départ:</strong> ${startNameSimple}</li>`;
            // Pas de sous-�tapes au moment de la cr�ation
            listHtml += `<li><strong>Arrivée:</strong> ${endNameSimple}</li>`;
            ul.innerHTML = listHtml;

            document.getElementById('legendList').appendChild(clone);

        } catch (e) {
            console.error("Erreur création segment :", e);
        }
    }

    async function updateRouteSegment(index, mode, options = {}) {
        const seg = segments[index];
        if (!seg) return;

        seg.modeTransport = mode; 
        seg.options = options;

        let urlBase = 'https://routing.openstreetmap.de/routed-car/route/v1/driving/';
        if (mode === 'Velo') urlBase = 'https://routing.openstreetmap.de/routed-bike/route/v1/driving/';
        else if (mode === 'Marche') urlBase = 'https://routing.openstreetmap.de/routed-foot/route/v1/driving/';

        let coordsList = [seg.startCoord];
        if (seg.sousEtapes && seg.sousEtapes.length > 0) {
            for (const sub of seg.sousEtapes) {
                const subCoords = await getCoordonnees(sub.nom);
                if (subCoords) coordsList.push(subCoords);
            }
        }
        coordsList.push(seg.endCoord);

        const coordString = coordsList.map(c => `${c[1]},${c[0]}`).join(';');
        let url = `${urlBase}${coordString}?overview=full&geometries=geojson`;

        if (mode === 'Voiture' || mode === 'driving') {
            const excludes = [];
            if (options.excludeTolls) excludes.push('toll');
            if (options.excludeMotorways) excludes.push('motorway');
            if (excludes.length > 0) url += `&exclude=${excludes.join(',')}`;
        }

        try {
            const resp = await fetch(url);
            const data = await resp.json();
            
            if (data.code !== 'Ok') {
                alert("Impossible de calculer le trajet avec ces options.");
                return;
            }

            if (seg.line) map.removeLayer(seg.line);
            
            seg.line = L.geoJSON(data.routes[0].geometry, { 
                color: seg.couleurSegment, 
                weight: 5, 
                opacity: 0.8 
            }).addTo(map);

            seg.distance = (data.routes[0].distance / 1000).toFixed(1);
            seg.duration = Math.floor(data.routes[0].duration / 60);

        } catch (e) {
            console.error("Erreur updateRouteSegment :", e);
        }
    }


    // ============================================================
    // 5. GESTION DES �V�NEMENTS DE LA LISTE (L�GENDE)
    // ============================================================

    document.getElementById('legendList').addEventListener('click', (e) => {
        const target = e.target;
        const li = target.closest('li'); 
        if (!li) return;
        
        const index = parseInt(li.dataset.index);

        // A. Clic sur bouton Transport
        const btnTransport = target.closest('.transport-btn');
        if (btnTransport) {
            li.querySelectorAll('.transport-btn').forEach(b => b.classList.remove('active'));
            btnTransport.classList.add('active');

            const checkboxPeage = li.querySelector('input[data-pref="exclude-tolls"]');
            const checkboxAutoroute = li.querySelector('input[data-pref="exclude-motorways"]');
            
            updateRouteSegment(index, btnTransport.dataset.mode, { 
                excludeTolls: checkboxPeage ? checkboxPeage.checked : false, 
                excludeMotorways: checkboxAutoroute ? checkboxAutoroute.checked : false 
            });
            return;
        }

        // B. Bouton Settings (Roue dent�e - on garde le toggle pour �a car c'est des options secondaires)
        if (target.closest('.settings-btn')) {
            const prefsDiv = li.querySelector('.route-preferences');
            prefsDiv.style.display = (prefsDiv.style.display === 'none') ? 'block' : 'none';
            return;
        }

        // C. Toggle affichage liste sous-�tapes
        // => SUPPRIM� : On ne veut plus avoir � cliquer. La liste est toujours l�.
        // Si vous voulez que la fl�che serve quand m�me � masquer au cas o�, d�commentez les lignes ci-dessous :
        /*
        if (target.closest('.toggleSousEtapes')) {
            const ul = li.querySelector('.sousEtapesList');
            ul.style.display = (ul.style.display === 'none') ? 'block' : 'none';
            return;
        }
        */

        // D. Bouton "Modifier"
        if (target.classList.contains('modifierSousEtapes') || target.closest('.modifierSousEtapes')) {
            openSegmentEditor(index);
        }
    });


    // ============================================================
    // 6. �DITEUR DE SOUS-�TAPES (SIDEBAR & TINYMCE)
    // ============================================================

    let currentSegmentIndex = null;
    const subEtapesContainer = document.getElementById('subEtapesContainer');
    const segmentDateInput = document.getElementById('segmentDate');
    const templateSubEtape = document.getElementById('template-sub-etape');

    function openSegmentEditor(index) {
        currentSegmentIndex = index;
        const seg = segments[index];
        
        document.getElementById('segmentTitle').textContent = `Modifier : ${seg.startNameSimple} → ${seg.endNameSimple}`;
        
        const container = document.getElementById('segmentFormContainer');
        container.style.display = 'block';
        document.querySelector('.sidebar').style.width = '300px'; 
        
        subEtapesContainer.innerHTML = '';
        segmentDateInput.value = seg.date || '';

        if (seg.sousEtapes) {
            seg.sousEtapes.forEach(se => addSousEtapeForm(se));
        }
    }

    function addSousEtapeForm(data = {}) {
        const uniqueId = 'editor-' + Date.now() + Math.random().toString(36).substring(2, 9);
        const clone = templateSubEtape.content.cloneNode(true);
        const div = clone.querySelector('.subEtape');

        const inputNom = div.querySelector('.subEtapeNom');
        inputNom.value = data.nom || '';

        div.querySelector('.subEtapeHeure').value = data.heure || '';
        
        const textArea = div.querySelector('.subEtapeRemarque');
        textArea.id = uniqueId;
        textArea.value = data.remarque || '';

        subEtapesContainer.appendChild(div);
        initAutocomplete(inputNom);

        tinymce.init({
            selector: `#${uniqueId}`,
            plugins: 'table lists link image code charmap searchreplace wordcount', 
            
            toolbar: 'undo redo | styles | bold italic underline forecolor | alignleft aligncenter alignright | bullist numlist outdent indent | table link image | removeformat',
            
            menubar: false,
            statusbar: false,
            height: 300, 
            branding: false,
            
            automatic_uploads: true,
            file_picker_types: 'image',
                        images_upload_handler: (blobInfo, progress) => new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.readAsDataURL(blobInfo.blob());
                reader.onload = () => {
                    resolve(reader.result);
                };
                reader.onerror = (error) => {
                    reject({ message: 'Erreur de lecture : ' + error.message, remove: true });
                };
            }),

            table_default_attributes: { border: '1' },
            table_default_styles: { 'border-collapse': 'collapse', 'width': '100%' },
            
            image_dimensions: true
        });

        div.querySelector('.removeSubEtapeBtn').addEventListener('click', () => {
            const editor = tinymce.get(uniqueId);
            if (editor) editor.remove();
            div.remove();
        });
    }

    document.getElementById('addSubEtape').addEventListener('click', () => addSousEtapeForm());

    document.getElementById('closeSegmentForm').addEventListener('click', () => {
        document.getElementById('segmentFormContainer').style.display = 'none';
        document.querySelector('.sidebar').style.width = '450px'; 
    });

    // --- ENREGISTREMENT DU SEGMENT MODIFIÉ ---
    document.getElementById('saveSegment').addEventListener('click', async () => {
        if (currentSegmentIndex === null) return;
        
        const seg = segments[currentSegmentIndex];
        seg.date = segmentDateInput.value;
        seg.sousEtapes = [];

        const divs = document.querySelectorAll('.subEtape');
        
        for (const div of divs) {
            const nom = div.querySelector('.subEtapeNom').value.trim();
            if (!nom) continue;

            const heure = div.querySelector('.subEtapeHeure').value;
            const textId = div.querySelector('.subEtapeRemarque').id;
            const remarque = tinymce.get(textId) ? tinymce.get(textId).getContent() : "";

            const rawPhotos = div.querySelector('.subEtapePhoto').files;
            let photos = [];
            
            if (rawPhotos.length > 0) {
                for (const f of rawPhotos) {
                    try {
                        const compressed = await compresserImage(f, 0.6, 1200);
                        photos.push(compressed);
                    } catch(e) { console.error(e); }
                }
            }

            seg.sousEtapes.push({ nom, remarque, heure, photos });
            
            const coords = await getCoordonnees(nom);
            if (coords) {
                const nomSimple = getNomSimple(nom);
                let txt = `<b>${nomSimple}</b>`;
                if(remarque) txt += `<div style="max-height:200px; overflow:auto;">${remarque}</div>`;
                addMarker(nom, coords, "sous_etape", txt);
            }
        }

        await updateRouteSegment(currentSegmentIndex, seg.modeTransport || 'Voiture', seg.options || {});

        const li = document.querySelector(`li[data-index="${currentSegmentIndex}"]`);
        if (li) {
            const ul = li.querySelector('.sousEtapesList');
            if (ul) {
                let listHtml = `<li style="margin-bottom:10px;"><strong>Départ:</strong> ${seg.startNameSimple}</li>`;
                
                if(seg.sousEtapes && seg.sousEtapes.length > 0) {
                    seg.sousEtapes.forEach(se => {
                        const nomSimple = getNomSimple(se.nom);

                        listHtml += `
                        <li style="margin-top: 15px; border-top: 1px dashed #ccc; padding-top: 10px;">
                            <div style="font-weight:bold; color:#2c3e50; font-size:1.1em;">
                                📍 ${nomSimple} 
                                <span style="font-size:0.8em;color:#666; font-weight:normal;">${se.heure ? ' ('+se.heure+')' : ''}</span>
                            </div>
                            
                            <div class="tinymce-content" style="margin-top:8px; font-size:0.9em; overflow-x:auto;">
                                ${se.remarque}
                            </div>
                        </li>`;
                    });
                }
                
                listHtml += `<li style="margin-top:15px; border-top:2px solid #ddd; padding-top:10px;"><strong>Arrivée:</strong> ${seg.endNameSimple}</li>`;
                ul.innerHTML = listHtml;
                
                const images = ul.querySelectorAll('img');
                images.forEach(img => {
                    img.style.maxWidth = "100%";
                    img.style.height = "auto";
                    img.style.display = "block";
                    img.style.marginTop = "5px";
                });
                
                const tables = ul.querySelectorAll('table');
                tables.forEach(tab => {
                    tab.style.width = "100%";
                    tab.style.fontSize = "0.85em";
                    tab.style.borderCollapse = "collapse";
                });
            }
        }

        alert("Segment mis à jour !");
        document.getElementById('segmentFormContainer').style.display = 'none';
        document.querySelector('.sidebar').style.width = '450px';
    });
    


    // ============================================================
    // 7. SAUVEGARDE FINALE DU ROADTRIP (DB)
    // ============================================================

    async function saveCoordsToDB(ville, lat, lon) {
        try {
            const response = await fetch('../include/geocode.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ nom: ville, lat: lat, lon: lon })
            });
            const result = await response.json();
            return result.success;
        } catch (e) {
            console.error("Erreur DB Geo:", e);
            return false;
        }
    }

    function compresserImage(file, quality = 0.6, maxWidth = 1200) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onerror = () => reject(new Error("Erreur lecture"));
            reader.onload = e => {
                const img = new Image();
                img.src = e.target.result;
                img.onerror = () => reject(new Error("Erreur image"));
                img.onload = () => {
                    const canvas = document.createElement('canvas');
                    const ratio = img.width / img.height;
                    if (img.width > maxWidth) {
                        canvas.width = maxWidth;
                        canvas.height = maxWidth / ratio;
                    } else {
                        canvas.width = img.width;
                        canvas.height = img.height;
                    }
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                    canvas.toBlob(blob => {
                        if (blob) resolve(new File([blob], file.name, { type: "image/jpeg", lastModified: Date.now() }));
                        else reject(new Error("Erreur compression"));
                    }, "image/jpeg", quality);
                };
            };
        });
    }

    document.getElementById('saveRoadtrip').addEventListener('click', async () => {
        const titre = document.getElementById('roadtripTitle').value.trim();
        const description = document.getElementById('roadtripDescription').value.trim();
        const visibilite = document.getElementById('roadtripVisibilite').value;
        let photoCover = document.getElementById('roadtripPhoto').files[0];

        if (!titre || !description) {
            alert("Veuillez remplir le titre et la description.");
            return;
        }

        if (segments.length === 0) {
            alert("Veuillez créer au moins un trajet.");
            return;
        }

        // Liste des villes uniques pour g�ocodage DB
        const villesSet = new Set();
        segments.forEach(s => {
            villesSet.add(s.startName);
            villesSet.add(s.endName);
            if(s.sousEtapes) s.sousEtapes.forEach(sub => villesSet.add(sub.nom));
        });
        
        const villesGeo = [];
        for (const ville of villesSet) {
            const coords = await getCoordonnees(ville);
            if (coords) {
                const saved = await saveCoordsToDB(ville, coords[0], coords[1]);
                if (saved) {
                    villesGeo.push({ nom: ville, lat: coords[0], lon: coords[1] });
                }
            }
        }

        // Structure Trajets
        const trajets = segments.map((seg, sIdx) => ({
            depart: seg.startName,
            arrivee: seg.endName,
            mode: seg.modeTransport || 'Voiture',
            sansAutoroute: seg.options ? seg.options.excludeMotorways : false,
            sansPeage: seg.options ? seg.options.excludeTolls : false,
            date: seg.date || null,
            sousEtapes: seg.sousEtapes.map((se, seIdx) => ({
                nom: se.nom,
                remarque: se.remarque,
                heure: se.heure,
                photos: se.photos ? se.photos.map((f, i) => `file_s${sIdx}_se${seIdx}_${i}`) : []
            }))
        }));

        const formData = new FormData();
        formData.append('titre', titre);
        formData.append('description', description);
        formData.append('visibilite', visibilite);
        formData.append('villes', JSON.stringify(villesGeo));
        formData.append('trajets', JSON.stringify(trajets));

        if (photoCover) {
            photoCover = await compresserImage(photoCover);
            formData.append('photo_cover', photoCover);
        }

        segments.forEach((seg, sIdx) => {
            seg.sousEtapes.forEach((se, seIdx) => {
                if (se.photos && se.photos.length) {
                    se.photos.forEach((f, i) => {
                        formData.append(`file_s${sIdx}_se${seIdx}_${i}`, f);
                    });
                }
            });
        });

        const saveBtn = document.getElementById('saveRoadtrip');
        const oldText = saveBtn.textContent;
        saveBtn.textContent = 'Sauvegarde...';
        saveBtn.disabled = true;

        try {
            const response = await fetch('../formulaire/saveRoadtrip.php', {
                method: 'POST',
                body: formData
            });
            const text = await response.text();
            
            let result;
            try { result = JSON.parse(text); } 
            catch (e) { throw new Error("Réponse serveur invalide: " + text); }

            if (result.success) {
                alert('RoadTrip sauvegardé avec succès !');
                window.location.href = `../mesRoadTrips.php`;
            } else {
                alert(result.message || 'Erreur lors de la sauvegarde.');
            }
        } catch (err) {
            console.error(err);
            alert('Erreur technique lors de la sauvegarde.');
        } finally {
            saveBtn.textContent = oldText;
            saveBtn.disabled = false;
        }
    });

    // ============================================================
    // 8. FONCTIONNALITES ACCESSOIRES (Lightbox, etc)
    // ============================================================
    
    const modal = document.getElementById('imageModal');
    if (modal) {
        modal.addEventListener('click', () => modal.style.display = 'none');
    }
    
    document.addEventListener('click', e => {
        if (e.target.classList.contains('popup-photo')) {
            const m = document.getElementById('imageModal');
            const mi = document.getElementById('imageModalContent');
            m.style.display = 'block';
            mi.src = e.target.src;
        }
    });

});

/*=======================================
  Formulaire d'inscription et de connexion
=======================================*/

function showLogin() {
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.style.display = 'block';
        document.getElementById('registerForm').style.display = 'none';
        document.getElementById('btnLogin').classList.add('active');
        document.getElementById('btnRegister').classList.remove('active');
    }
}

function showRegister() {
    const regForm = document.getElementById('registerForm');
    if (regForm) {
        document.getElementById('loginForm').style.display = 'none';
        regForm.style.display = 'block';
        document.getElementById('btnLogin').classList.remove('active');
        document.getElementById('btnRegister').classList.add('active');
    }
}

function openModal() {
    const modal = document.querySelector('.formulaire'); 
    if (modal) {
        modal.style.display = 'block';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    if (document.querySelector('.formulaire')) {
        openModal();
        showLogin(); 
    }
});

/*=======================================
  Changement de th�me
=======================================*/

const savedTheme = localStorage.getItem("theme");
const toggleSombre = document.getElementById("checkboxSombre");

const savedMalvoyant = localStorage.getItem("Police");
const toggleMalvoyant = document.getElementById("checkboxMalvoyant");

if (savedTheme === "dark") {
    document.documentElement.classList.add("dark");
    document.documentElement.classList.add("SombreBtn");
}

if (toggleSombre) {
    toggleSombre.checked = savedTheme === "dark";

    toggleSombre.addEventListener("change", () => {
        if (toggleSombre.checked) {
            document.documentElement.classList.add("dark");
            document.documentElement.classList.add("SombreBtn");
            localStorage.setItem("theme", "dark");
        } else {
            document.documentElement.classList.remove("dark");
            document.documentElement.classList.remove("SombreBtn");
            localStorage.setItem("theme", "light");
        }
    });
}

if (savedMalvoyant === "malvoyant") {
    document.documentElement.classList.add("malvoyant");
    document.documentElement.classList.add("MalvoyantBtn");
}

if (toggleMalvoyant) {
    toggleMalvoyant.checked = savedMalvoyant === "malvoyant";

    toggleMalvoyant.addEventListener("change", () => {
        if (toggleMalvoyant.checked) {
            document.documentElement.classList.add("malvoyant");
            document.documentElement.classList.add("MalvoyantBtn");
            localStorage.setItem("Police", "malvoyant");
        } else {
            document.documentElement.classList.remove("malvoyant");
            document.documentElement.classList.remove("MalvoyantBtn");
            localStorage.setItem("Police", "voyant");
        }
    });
}

/*========================================================================
  CALCUL ASYNCHRONE DES DISTANCES ET TEMPS ENTRE DEUX POINTS
========================================================================*/

document.addEventListener("DOMContentLoaded", function() {
    const elements = document.querySelectorAll('.js-calculate-distance');
    if (elements.length === 0) return;

    console.log("Démarrage calcul distances (avec préférences)...");

    elements.forEach(function(el, i) {
        setTimeout(function() {
            const latDep = el.dataset.latDep;
            const lonDep = el.dataset.lonDep;
            const latArr = el.dataset.latArr;
            const lonArr = el.dataset.lonArr;
            let mode = el.dataset.mode || 'voiture';
            
            const sansAutoroute = el.dataset.sansAutoroute === "1";
            const sansPeage = el.dataset.sansPeage === "1";

            const distEl = el.querySelector('.result-distance');
            const timeEl = el.querySelector('.result-time');

            if (!latDep || !lonDep || !latArr || !lonArr) {
                distEl.innerHTML = "N/A"; 
                timeEl.innerHTML = "N/A"; 
                return;
            }

            const profiles = { 'voiture': 'car', 'velo': 'bike', 'marche': 'foot' };
            const profile = profiles[mode.toLowerCase()] || 'car';
            
            let url = `https://router.project-osrm.org/route/v1/${profile}/${lonDep},${latDep};${lonArr},${latArr}?overview=false`;
            
            const excludes = [];
            if (sansPeage) excludes.push('toll');
            if (sansAutoroute) excludes.push('motorway');
            
            if (excludes.length > 0) {
                url += `&exclude=${excludes.join(',')}`;
            }

            fetch(url)
                .then(r => r.json())
                .then(data => {
                    if (data.code === 'Ok' && data.routes && data.routes.length > 0) {
                        const route = data.routes[0];
                        distEl.innerHTML = `<strong>${(route.distance / 1000).toFixed(1).replace('.', ',')} km</strong>`;
                        const h = Math.floor(route.duration / 3600);
                        const m = Math.floor((route.duration % 3600) / 60);
                        timeEl.innerHTML = (h > 0 ? `${h}h ` : "") + `${m}min`;
                    } else {
                        distEl.innerHTML = "-"; 
                        timeEl.innerHTML = "-";
                    }
                })
                .catch(() => {
                    distEl.innerHTML = "<span class='error-data'>Err</span>";
                    timeEl.innerHTML = "<span class='error-data'>Err</span>";
                });
        }, i * 1500);
    });
});

/*=======================================
  Barre de recherche
=======================================*/

let data = [];
let userId = null; 

fetch("../bd/rech_bd.php")
    .then(response => response.json())
    .then(json => {
        userId = json.userId;  
        data = json.roadtrips;  
        
        console.log("Connecté avec l'ID :", userId);
        console.log("Liste des roadtrips chargés :", data);
    })
    .catch(error => console.error("Erreur fetch :", error));

const searchBox = document.getElementById('searchInput');
const resultsTableBody = document.querySelector('#results-table tbody');

if (searchBox && resultsTableBody) {
    searchBox.addEventListener('input', function(event) {
        const query = event.target.value.trim().toLowerCase();
        resultsTableBody.innerHTML = '';

        if (query.length < 2) return;

        const filteredData = data.filter(item => {
            const match = item.titre.toLowerCase().includes(query);

    const myId = userId; 

    const filteredData = data.filter(item => {
        const matchTitle = item.titre.toLowerCase().includes(query);
        if (!matchTitle) return false;

        
        if (item.visibilite === "public") {
            return true;
        }

       
        if (item.visibilite === "prive" && myId !== null && item.id_utilisateur == myId) {
            return true;
        }

       
        return false;
    });

   
    if (filteredData.length > 0) {
        filteredData.forEach(item => {
            const row = document.createElement('tr');
            
           
            const nomCell = document.createElement('td');
            nomCell.textContent = item.titre + ' (Road-Trip)';
            
            
            if (item.visibilite === 'prive') {
                nomCell.textContent += ' (Privé)';
                nomCell.style.fontStyle = 'italic';
            }
            
            row.appendChild(nomCell);
            resultsTableBody.appendChild(row);
        });
    }
});
    });
}

