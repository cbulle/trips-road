// --- map.js ---
document.addEventListener('DOMContentLoaded', () => {

    // --- Initialisation de la carte en mode clair ---
    let map = L.map('map').setView([46.5, 2.5], 6);
    var lightLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '� OpenStreetMap'
    }).addTo(map);
    
    // --- Initialisation de la carte en mode sombre
    var darkTiles = L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
        maxZoom: 19,
        attribution: '� OpenStreetMap & � CartoDB'
    });

    let segments = [];
    const markers = {}; // Stockage centralis� de tous les markers

    const strategies = {
        Voiture: { profile: 'driving' },
        Velo: { profile: 'cycling' },
        Marche: { profile: 'walking' }
    };

    const segmentColors = [
        'blue', 'green', 'orange', 'red', 'purple', 'brown', 'pink', 'yellow',
        'cyan', 'magenta', 'lime', 'teal', 'indigo', 'violet', 'gold', 'silver',
        'maroon', 'navy', 'olive', 'coral'
    ];
    
    const europeViewbox = [-25.0, 35.0, 30.0, 71.0]; // Zone Europe

    // --- FONCTION UTILITAIRE : Extrait le nom simple de la ville ---
    function getNomSimple(nomComplet) {
        if (!nomComplet) return '';
        // Prend tout ce qui est avant la premi�re virgule (ex: "Lyon, Rh�ne, France" -> "Lyon")
        return nomComplet.split(',')[0].trim(); 
    }

    // --- Fonction de g�ocodage (Utilise Nominatim) ---
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

    // --- Fonction g�n�rique pour cr�er un marker et le stocker ---
    function addMarker(lieu, coords, type, popupContent) {
        const marker = L.marker(coords).addTo(map).bindPopup(popupContent);
        if (!markers[lieu]) markers[lieu] = [];
        markers[lieu].push({ marker, type, text: popupContent });
        return marker;
    }

    // --- Remplacement de initSelect2 par initAutocomplete ---
    function initAutocomplete(element) {
        $(element).autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: './fonctions/recherche_villes.php',
                    dataType: "json",
                    method: "GET",
                    data: {
                        q: request.term
                    },
                    success: function(data) {
                        response($.map(data, function(item) {
                            return {
                                label: item.nom_ville,
                                value: item.nom_ville
                            }
                        }));
                    },
                    error: function() {
                        response([]);
                    }
                });
            },
            minLength: 3,
            delay: 600,
            select: function(event, ui) {
                $(element).val(ui.item.value);
            }
        });
    }

    // --- 1. Initialisation au chargement de la page ---
    document.querySelectorAll('.etape').forEach(initAutocomplete);

    // --- 2. Ajout dynamique d'�tapes (REFATO AVEC TEMPLATE) ---
    const etapesContainer = document.getElementById('etapesContainer');
    const templateEtape = document.getElementById('template-etape');

    document.getElementById('addEtape').addEventListener('click', () => {
        const clone = templateEtape.content.cloneNode(true);
        const containerRow = clone.querySelector('.etape-row');
        const input = clone.querySelector('input.etape');
        const removeBtn = clone.querySelector('.btn-remove-etape');

        removeBtn.addEventListener('click', () => {
            $(input).autocomplete('destroy'); 
            containerRow.remove();
        });

        etapesContainer.appendChild(clone);
        initAutocomplete(input); 

        if (document.getElementById('btnCalculer').style.display === 'none') {
            document.getElementById('btnRecalculer').style.display = 'inline-block';
            document.getElementById('btnLegende').style.display = 'none';
        }
    });

    // --- Calcul de l'itin�raire principal ---
    async function calculItineraire() {
        document.getElementById('etapesContainer').style.display = 'none';
        document.getElementById('addEtape').style.display = 'none';
        document.getElementById('btnCalculer').style.display = 'none';
        document.getElementById('legend').style.display = 'block';
        document.getElementById('btnModifier').style.display = 'inline-block';

        const villes = [];
        $('.etape').each(function() {
            const val = $(this).val();
            if (val && val.trim() !== '') {
                villes.push(val.trim());
            }
        });

        if (villes.length < 2) { 
            alert("Veuillez renseigner au moins deux villes."); 
            return; 
        }
        
        const mode = 'Voiture';
        const strategy = strategies[mode] || strategies['Voiture'];

        const coordsVilles = [];
        for (const ville of villes) {
            const coords = await getCoordonnees(ville);
            if (!coords) { 
                alert(`Ville introuvable ou hors Europe : ${ville}`); 
                return; 
            }
            coordsVilles.push(coords);
        }

        // Supprimer tous les anciens markers et segments
        Object.values(markers).flat().forEach(m => map.removeLayer(m.marker));
        for (const seg of segments) if (seg.line) map.removeLayer(seg.line);
        segments = [];

        // Marqueurs d�part/arriv�e
        addMarker(villes[0], coordsVilles[0], "ville", `D�part : ${villes[0]}`);
        addMarker(villes[villes.length - 1], coordsVilles[coordsVilles.length - 1], "ville", `Arriv�e : ${villes[villes.length - 1]}`);

        // Marqueurs pour chaque �tape interm�diaire
        for (let i = 1; i < coordsVilles.length - 1; i++) {
            addMarker(villes[i], coordsVilles[i], "ville", `�tape : ${villes[i]}`);
        }

        // Construction des segments
        for (let i = 0; i < coordsVilles.length - 1; i++) {
            await _ajouterSegmentEntre(villes[i], coordsVilles[i], villes[i+1], coordsVilles[i+1], i, strategy);
        }

        map.fitBounds(coordsVilles.map(c => [c[0], c[1]]));
    }

    async function recalculerDerniersSegmentsMultiples() {
        const inputs = Array.from(document.querySelectorAll('#etapesContainer input.etape'))
            .map(input => input.value.trim())
            .filter(v => v.length > 0);

        if (inputs.length < 2) {
            alert("Veuillez saisir au moins deux villes.");
            return;
        }

        // S�quence de villes existantes
        let existingSequence = [];
        if (segments.length > 0) {
            existingSequence.push(segments[0].startName);
            for (const s of segments) existingSequence.push(s.endName);
        }

        // Pas de segment existant : fallback complet
        if (existingSequence.length === 0) {
            await calculItineraire();
            return;
        }

        // Derni�re ville connue
        const lastKnownCity = existingSequence[existingSequence.length - 1];
        const lastKnownIndexInInputs = inputs.lastIndexOf(lastKnownCity);

        if (lastKnownIndexInInputs === -1) {
            console.warn("Derni�re ville connue introuvable. Recalcul global.");
            await calculItineraire();
            return;
        }

        if (lastKnownIndexInInputs === inputs.length - 1) {
            alert("Aucune nouvelle ville � ajouter.");
            return;
        }

        // Nouvelles villes � ajouter
        const nouvelles = inputs.slice(lastKnownIndexInInputs + 1);
        let depart = lastKnownCity;
        let departCoords = await getCoordonnees(depart);

        try {
            for (const villeArr of nouvelles) {
                const coords = await getCoordonnees(villeArr);
                if (!coords) {
                    alert(`Ville introuvable ou hors Europe : ${villeArr}`);
                    return;
                }

                addMarker(villeArr, coords, "ville", `�tape : ${villeArr}`);
                await _ajouterSegmentEntre(depart, departCoords, villeArr, coords, segments.length, strategies['Voiture']);

                depart = villeArr;
                departCoords = coords;
            }

            // Marker d'arriv�e
            const derniereSeg = segments[segments.length - 1];
            if (markers['Arrivee']) {
                markers['Arrivee'].forEach(m => map.removeLayer(m.marker));
                delete markers['Arrivee'];
            }
            markers['Arrivee'] = [{
                marker: L.marker(derniereSeg.endCoord).addTo(map).bindPopup(`Arriv�e : ${derniereSeg.endName}`),
                type: "ville",
                text: `Arriv�e : ${derniereSeg.endName}`
            }];

            // Ajuster la vue
            const allCoords = segments.flatMap(s => [s.startCoord, s.endCoord]);
            if (allCoords.length) map.fitBounds(allCoords);

            alert(`${nouvelles.length} segment(s) ajout�s.`);

        } catch (err) {
            console.error("Erreur lors de l'ajout des nouveaux segments :", err);
            alert("Erreur lors de l'ajout des nouveaux segments. Voir console.");
        }
    }

    async function updateRouteSegment(index, mode, options = {}) {
        const seg = segments[index];
        if (!seg) return;

        seg.modeTransport = mode; 
        seg.options = options;

        let urlBase = '';
        
        if (mode === 'Velo') {
            urlBase = 'https://routing.openstreetmap.de/routed-bike/route/v1/driving/';
        } else if (mode === 'Marche') {
            urlBase = 'https://routing.openstreetmap.de/routed-foot/route/v1/driving/';
        } else {
            urlBase = 'https://routing.openstreetmap.de/routed-car/route/v1/driving/';
        }

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

        if (mode === 'Voiture' || !mode || mode === 'driving') {
            const excludes = [];
            
            if (options.excludeTolls) excludes.push('toll');
            if (options.excludeMotorways) excludes.push('motorway');
            
            if (excludes.length > 0) {
                url += `&exclude=${excludes.join(',')}`;
            }
        }

        console.log(`URL appelée (${mode}) : ${url}`);

        try {
            const resp = await fetch(url, { cache: "reload" });
            const data = await resp.json();
            
            if (data.code !== 'Ok') {
                console.warn("Erreur API OSRM :", data.message);
                
                if (data.message && data.message.includes('Exclude flag')) {
                    alert("Ce serveur ne supporte pas cette combinaison d'options (péages/autoroutes) pour ce trajet.");
                } else {
                    alert(`Impossible de calculer le trajet. Vérifiez que la distance n'est pas trop grande pour le mode ${mode}.`);
                }
                return;
            }

            const route = data.routes[0];

            if (seg.line) map.removeLayer(seg.line);
            
            seg.line = L.geoJSON(route.geometry, { 
                color: seg.couleurSegment, 
                weight: 5, 
                opacity: 0.8 
            }).addTo(map);

            seg.distance = (route.distance / 1000).toFixed(1);
            seg.duration = Math.floor(route.duration / 60);

            console.log(`-> Résultat : ${seg.distance}km, ${seg.duration}min`);

        } catch (e) {
            console.error("Erreur technique :", e);
        }
    }

    // --- Fonction modifi�e : Ajout d'un segment initial ---
    async function _ajouterSegmentEntre(startName, startCoords, endName, endCoords, index, strategy) {
        const coordString = `${startCoords[1]},${startCoords[0]};${endCoords[1]},${endCoords[0]}`;
        const url = `https://router.project-osrm.org/route/v1/${strategy.profile}/${coordString}?overview=full&geometries=geojson`;

        try {
            const resp = await fetch(url);
            const data = await resp.json();
            if (data.code !== 'Ok') return;

            const route = data.routes[0];
            const couleurSegment = segmentColors[index % segmentColors.length];
            const line = L.geoJSON(route.geometry, { 
                color: couleurSegment, 
                weight: 5, 
                opacity: 0.8 
            }).addTo(map);

            const startNameSimple = getNomSimple(startName);
            const endNameSimple = getNomSimple(endName);

            segments.push({
                line,
                startName: startName,
                startCoord: startCoords,
                endName: endName,
                endCoord: endCoords,
                distance: (route.distance / 1000).toFixed(1),
                duration: Math.floor(route.duration / 60),
                couleurSegment,
                sousEtapes: [],
                startNameSimple: startNameSimple,
                endNameSimple: endNameSimple
            });

            // --- UTILISATION DU TEMPLATE ---
            const templateLegend = document.getElementById('template-legend-item');
            const clone = templateLegend.content.cloneNode(true);
            const li = clone.querySelector('li');
            
            li.dataset.index = index;

            const colorBox = clone.querySelector('.legend-color-indicator');
            colorBox.style.background = couleurSegment;

            const toggleBtn = clone.querySelector('.toggleSousEtapes');
            toggleBtn.dataset.index = index;
            toggleBtn.innerHTML = `${startNameSimple} → ${endNameSimple} <strong style="margin-left:5px; color: ${couleurSegment}; border: none;">▼</strong>`;

            const btnVoiture = clone.querySelector('.transport-btn[data-mode="Voiture"]');
            if (btnVoiture) btnVoiture.classList.add('active');

            const modifBtn = clone.querySelector('.modifierSousEtapes');
            modifBtn.dataset.index = index;

            const ulSub = clone.querySelector('.sousEtapesList');
            ulSub.dataset.index = index;

            document.getElementById('legendList').appendChild(clone);

        } catch (e) {
            console.error("Erreur segment :", e);
        }
    }

    // Fonction pour afficher le formulaire de segment
    function showSegmentForm() {
        const container = document.getElementById('segmentFormContainer');
        container.style.display = 'block';
        document.getElementsByClassName('sidebar')[0].style.width = '300px';
        container.classList.remove('hidden');
    }

    // Initialisation event listener fermeture formulaire
    const closeSegmentBtn = document.getElementById('closeSegmentForm');
    if (closeSegmentBtn) {
        closeSegmentBtn.addEventListener('click', () => {
            const container = document.getElementById('segmentFormContainer');
            container.classList.add('hidden');
            document.getElementsByClassName('sidebar')[0].style.width = '450px';
            setTimeout(() => {
                container.style.display = 'none';
                container.classList.remove('hidden');
            }, 300);
        });
    }

    // --- Gestion des sous-�tapes (REFATO AVEC TEMPLATE) ---
    const segmentFormContainer = document.getElementById('segmentFormContainer');
    const subEtapesContainer = document.getElementById('subEtapesContainer');
    const segmentDateInput = document.getElementById('segmentDate');
    let currentSegmentIndex = null;
    const templateSubEtape = document.getElementById('template-sub-etape');

    function addSousEtapeForm(data = {}) {
        const uniqueId = 'editor-' + Date.now() + Math.random().toString(36).substring(2, 9);
        
        const clone = templateSubEtape.content.cloneNode(true);
        const div = clone.querySelector('.subEtape');

        const inputNom = clone.querySelector('.subEtapeNom');
        inputNom.value = data.nom || '';

        const textArea = clone.querySelector('.subEtapeRemarque');
        textArea.id = uniqueId;
        textArea.value = data.remarque || '';

        const inputHeure = clone.querySelector('.subEtapeHeure');
        inputHeure.value = data.heure || '';

        subEtapesContainer.appendChild(div);
        initAutocomplete(inputNom);

        // INITIALISATION DE TINYMCE
        tinymce.init({
            selector: `#${uniqueId}`,
            plugins: 'table lists link code visualblocks autoresize fullscreen textcolor colorpicker',
            toolbar: 'bold italic underline | forecolor backcolor | bullist numlist | indent outdent | alignleft aligncenter alignright alignjustify | table | code | visualblocks | fullscreen',
            menubar: false,
            height: 1500,
            branding: false,
            statusbar: false
        });

        const removeBtn = div.querySelector('.removeSubEtapeBtn');
        removeBtn.addEventListener('click', () => {
            const editor = tinymce.get(uniqueId);
            if (editor) editor.remove();
            div.remove(); 
        });
    }

    // --- Clic sur un segment pour modifier sous-�tapes ---
    document.getElementById('legendList').addEventListener('click', e => {
        const li = e.target.closest('li');
        if (!li) return;

        if (e.target.classList.contains('modifierSousEtapes')) {
            const index = parseInt(li.dataset.index);
            if (isNaN(index)) return;
            currentSegmentIndex = index;

            const seg = segments[index];
            const start = seg.startNameSimple || getNomSimple(seg.startName); 
            const end = seg.endNameSimple || getNomSimple(seg.endName); 
            
            document.getElementById('segmentTitle').textContent = `Modifier le segment : ${start} � ${end}`;
            showSegmentForm();
            
            subEtapesContainer.innerHTML = ''; 
            seg.sousEtapes.forEach(se => addSousEtapeForm(se));
            segmentDateInput.value = seg.date || '';
        }
    });

    document.getElementById('addSubEtape').addEventListener('click', () => addSousEtapeForm());

    // --- Sauvegarder sous-�tapes + ajout markers ---
    document.getElementById('saveSegment').addEventListener('click', async () => {
        if (currentSegmentIndex === null) return;
        const seg = segments[currentSegmentIndex];
        seg.date = segmentDateInput.value;
        seg.sousEtapes = [];

        const sousEtapeNoms = [];
        const subDivs = Array.from(document.querySelectorAll('.subEtape'));
        for (const div of subDivs) {
            const nom = div.querySelector('.subEtapeNom').value.trim();
            const heure = div.querySelector('.subEtapeHeure').value.trim();
            const textareaElement = div.querySelector('textarea.subEtapeRemarque');
            
            const remarque = tinymce.get(textareaElement.id) 
                ? tinymce.get(textareaElement.id).getContent() 
                : textareaElement.value;
            
            const rawPhotos = Array.from(div.querySelector('.subEtapePhoto').files);
            const photos = [];
            
            for (const f of rawPhotos) {
                const compressed = await compresserImage(f, 0.6, 1200);
                photos.push(compressed);
            }

            const se = { nom, remarque, heure, photos };
            if (!nom) continue;
            seg.sousEtapes.push(se);
            sousEtapeNoms.push(nom);
        }

        if (sousEtapeNoms.length === 0) {
            alert("Aucune sous-�tape renseign�e. Enregistrement simple.");
            segmentFormContainer.style.display = 'none';
            return;
        }

        // --- Calcul de l'itin�raire passant par les sous-�tapes ---
        const allPlaces = [seg.startName, ...sousEtapeNoms, seg.endName];
        const coordsList = [];
        
        for (const place of allPlaces) {
            const coords = await getCoordonnees(place);
            if (!coords) {
                alert(`Lieu introuvable ou hors Europe : ${place}. Annulation du segment.`);
                return;
            }

            const [lat, lon] = coords;
            const saved = await saveCoordsToDB(place, lat, lon);
            if (!saved) {
                alert(`Erreur lors de la sauvegarde/v�rification des coordonn�es de la ville ${place} en base de donn�es. Annulation de la sauvegarde du segment.`);
                return;
            }

            coordsList.push(coords);
        }

        const coordPairs = coordsList.map(c => `${c[1]},${c[0]}`).join(';');
        const strategy = strategies['Voiture'];
        const url = `https://router.project-osrm.org/route/v1/${strategy.profile}/${coordPairs}?overview=full&geometries=geojson`;

        try {
            const resp = await fetch(url);
            const data = await resp.json();
            if (data.code !== 'Ok') {
                alert("Erreur lors du recalcul du trajet.");
                return;
            }

            if (seg.line) map.removeLayer(seg.line);
            const route = data.routes[0];
            seg.line = L.geoJSON(route.geometry, { 
                color: seg.couleurSegment, 
                weight: 5, 
                opacity: 0.8 
            }).addTo(map);
            seg.distance = (route.distance / 1000).toFixed(1);
            seg.duration = Math.floor(route.duration / 60);

            // --- Ajout des marqueurs pour chaque sous-�tape ---
            for (const se of seg.sousEtapes) {
                const coords = await getCoordonnees(se.nom); 
                if (!coords) continue;

                let popupText = `<b>${se.nom}</b>`;
                
                if (se.remarque) popupText += `<br><em>${se.remarque}</em>`;
                if (se.heure) popupText += `<br>Heure : ${se.heure}`;

                if (se.photos && se.photos.length > 0) {
                    se.photos.forEach(f => {
                        const url = URL.createObjectURL(f);
                        popupText += `<br><img src="${url}" class="popup-photo">`;
                    });
                }

                addMarker(se.nom, coords, "sous_etape", popupText);
            }

            alert(`Segment "${seg.startName} � ${seg.endName}" recalcul� (${seg.distance} km, ${seg.duration} min).`);
            segmentFormContainer.style.display = 'none';
        } catch (err) {
            console.error(err);
            alert("Erreur lors du recalcul d'itin�raire.");
        }
        document.getElementsByClassName('sidebar')[0].style.width = '450px';
    });

    // ============================================================
    // GESTION DES INTERACTIONS (BOUTONS & CHECKBOXES)
    // ============================================================

    document.getElementById('legendList').addEventListener('click', (e) => {
        const target = e.target;
        const li = target.closest('li'); 
        if (!li) return;
        
        const index = parseInt(li.dataset.index);

        const btnTransport = target.closest('.transport-btn');
        
        if (btnTransport) {
            li.querySelectorAll('.transport-btn').forEach(b => b.classList.remove('active'));
            btnTransport.classList.add('active');

            const newMode = btnTransport.dataset.mode;
            
            const checkboxPeage = li.querySelector('input[data-pref="exclude-tolls"]');
            const checkboxAutoroute = li.querySelector('input[data-pref="exclude-motorways"]');
            
            const excludeTolls = checkboxPeage ? checkboxPeage.checked : false;
            const excludeMotorways = checkboxAutoroute ? checkboxAutoroute.checked : false;

            console.log(`Clic détecté : Segment ${index}, Mode ${newMode}`);

            updateRouteSegment(index, newMode, { 
                excludeTolls: excludeTolls, 
                excludeMotorways: excludeMotorways 
            });
            return;
        }

        const settingsBtn = target.closest('.settings-btn');
        if (settingsBtn) {
            const prefsDiv = li.querySelector('.route-preferences');
            if (prefsDiv) {
                prefsDiv.style.display = (prefsDiv.style.display === 'none') ? 'block' : 'none';
            }
            return;
        }

        if (target.classList.contains('modifierSousEtapes') || target.closest('.modifierSousEtapes')) {
            currentSegmentIndex = index;
            const seg = segments[index];
            const start = seg.startNameSimple || getNomSimple(seg.startName); 
            const end = seg.endNameSimple || getNomSimple(seg.endName); 
            
            document.getElementById('segmentTitle').textContent = `Modifier le segment : ${start} à ${end}`;
            showSegmentForm();
            
            const subContainer = document.getElementById('subEtapesContainer');
            subContainer.innerHTML = ''; 
            
            if (seg.sousEtapes && seg.sousEtapes.length > 0) {
                seg.sousEtapes.forEach(se => addSousEtapeForm(se));
            }
            
            document.getElementById('segmentDate').value = seg.date || '';
            return;
        }

        const toggleBtn = target.closest('.toggleSousEtapes');
        if (toggleBtn) {
            const ul = li.querySelector('.sousEtapesList');
            if (ul) ul.style.display = (ul.style.display === 'none') ? 'block' : 'none';
            
            if (ul.innerHTML === '') {
                const seg = segments[index];
                let html = `<li><strong>Départ:</strong> ${getNomSimple(seg.startName)}</li>`;
                if(seg.sousEtapes) {
                    seg.sousEtapes.forEach(se => html += `<li>- ${se.nom}</li>`);
                }
                html += `<li><strong>Arrivée:</strong> ${getNomSimple(seg.endName)}</li>`;
                ul.innerHTML = html;
            }
        }
    });

    document.getElementById('btnModifier').addEventListener('click', () => {
        document.getElementById('etapesContainer').style.display = 'block';
        document.getElementById('addEtape').style.display = 'inline-block';
        document.getElementById('btnCalculer').style.display = 'none';
        if (document.getElementById('segmentFormContainer').style.display === 'block') {
            document.getElementById('segmentFormContainer').style.display = 'none';
        }

        document.getElementById('legend').style.display = 'none';
        document.getElementById('btnModifier').style.display = 'none';
        document.getElementById('btnLegende').style.display = 'inline-block';
    });

    document.getElementById('btnLegende').addEventListener('click', () => {
        document.getElementById('etapesContainer').style.display = 'none';
        document.getElementById('addEtape').style.display = 'none';
        document.getElementById('btnCalculer').style.display = 'none';

        document.getElementById('legend').style.display = 'block';

        document.getElementById('btnModifier').style.display = 'inline-block';
        document.getElementById('btnLegende').style.display = 'none';
        document.getElementById('btnRecalculer').style.display = 'none';
    });

    document.getElementById('btnCalculer').addEventListener('click', calculItineraire);

    document.getElementById('btnRecalculer').addEventListener('click', recalculerDerniersSegmentsMultiples);
    document.getElementById('btnRecalculer').addEventListener('click', () => {
        document.getElementById('etapesContainer').style.display = 'none';
        document.getElementById('addEtape').style.display = 'none';
        document.getElementById('btnCalculer').style.display = 'none';
        document.getElementById('legend').style.display = 'block';
        document.getElementById('btnModifier').style.display = 'inline-block';
        document.getElementById('btnRecalculer').style.display = 'none';
    });

    // --- Lightbox images ---
    document.addEventListener('click', e => {
        if (e.target.classList.contains('sousetape-photo')) {
            const modal = document.getElementById('imageModal');
            const modalImg = document.getElementById('imageModalContent');
            modal.style.display = 'block';
            modalImg.src = e.target.src;
        } else if (e.target.classList.contains('image-modal')) {
            e.target.style.display = 'none';
        }
    });

    const burgerCheckbox = document.getElementById('burger');

    if (burgerCheckbox) {
        burgerCheckbox.addEventListener('click', () => {
            const transportDiv = document.querySelector('.transport-options');
            const settingsBtn = document.querySelector('.settings-btn');

            const toggleElement = (element) => {
                if (!element) return;

                const styleReel = window.getComputedStyle(element).display;

                if (styleReel !== 'none') {
                    element.style.display = 'none';
                } else {
                    element.style.display = 'inline-flex'; 
                }
            };

            toggleElement(transportDiv);
            toggleElement(settingsBtn);
        });
    }

    /*========================================================================
      FONCTION DE SAUVEGARDE/V�RIFICATION DES COORDONN�ES EN BASE DE DONN�ES
    ========================================================================*/
    async function saveCoordsToDB(ville, lat, lon) {
        try {
            const response = await fetch('../include/geocode.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ nom: ville, lat: lat, lon: lon })
            });
            const result = await response.json();
            
            if (!result.success) {
                console.error(`Erreur DB pour ${ville}:`, result.message);
                return false;
            }
            return true;
        } catch (e) {
            console.error("Erreur r�seau ou serveur lors de la sauvegarde/v�rification des coordonn�es:", e);
            return false;
        }
    }

    /*========================================================================
    SAUVEGARDE D'UN ROADTRIP DANS LA BASE DE DONN�ES
    ========================================================================*/
    document.getElementById('saveRoadtrip').addEventListener('click', async () => {
        const titre = document.getElementById('roadtripTitle').value.trim();
        const description = document.getElementById('roadtripDescription').value.trim();
        const visibilite = document.getElementById('roadtripVisibilite').value;
        let photoCover = document.getElementById('roadtripPhoto').files[0];
        
        if (!titre || !description) {
            alert("Veuillez remplir le titre et la description.");
            return;
        }

        const villesInputs = Array.from(document.querySelectorAll('#etapesContainer input.etape'))
            .map(input => input.value.trim())
            .filter(v => v.length > 0);

        if (villesInputs.length < 2) {
            alert("Veuillez saisir au moins deux villes.");
            return;
        }

        const villesGeo = [];
        for (const ville of villesInputs) {
            const coords = await getCoordonnees(ville);
            if (!coords) {
                alert(`Ville introuvable ou hors Europe : ${ville}`);
                return;
            }
            
            const [lat, lon] = coords;
            
            const saved = await saveCoordsToDB(ville, lat, lon);
            if (!saved) {
                alert(`Erreur lors de la sauvegarde/v�rification des coordonn�es de la ville ${ville} en base de donn�es. Annulation de la sauvegarde du RoadTrip.`);
                return;
            }
            
            villesGeo.push({ 
                nom: ville, 
                lat: lat, 
                lon: lon 
            });
        }

        const trajets = segments.map((seg, sIdx) => ({
            depart: seg.startName,
            arrivee: seg.endName,
            mode: seg.modeTransport || 'Voiture', 
            sansAutoroute: seg.options ? seg.options.excludeMotorways : false,
            sansPeage: seg.options ? seg.options.excludeTolls : false,
            
            sousEtapes: seg.sousEtapes.map((se, seIdx) => ({
                nom: se.nom,
                remarque: se.remarque,
                heure: se.heure,
                sansAutoroute: seg.options ? seg.options.excludeMotorways : false, 
                sansPeage: seg.options ? seg.options.excludeTolls : false,
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
            photoCover = await compresserImage(photoCover, 0.6, 1200);
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
        const originalText = saveBtn.textContent;
        saveBtn.textContent = 'Sauvegarde en cours...';
        saveBtn.disabled = true;

        try {
            const response = await fetch('../formulaire/saveRoadtrip.php', {
                method: 'POST',
                body: formData
            });

            const text = await response.text();
            console.log("R�ponse brute du serveur :", text);

            let result;
            try {
                result = JSON.parse(text);
            } catch (e) {
                console.error("Erreur de parsing JSON:", e);
                throw new Error("R�ponse invalide du serveur");
            }

            if (result.success) {
                alert('RoadTrip sauvegard� avec succ�s !');
                
                document.getElementById('roadtripTitle').value = '';
                document.getElementById('roadtripDescription').value = '';
                document.getElementById('roadtripVisibilite').selectedIndex = 0;
                document.getElementById('roadtripPhoto').value = '';
                document.getElementById('etapesContainer').innerHTML = '';
                
                segments.forEach(seg => {
                    if (seg.line) map.removeLayer(seg.line);
                });
                Object.values(markers).flat().forEach(m => map.removeLayer(m.marker));
                segments.length = 0;
                
                window.location.href = `../mesRoadTrips.php`;
                
            } else {
                alert(result.message || 'Erreur lors de la sauvegarde.');
            }
            
        } catch (err) {
            console.error("Erreur lors de la sauvegarde:", err);
            alert('Erreur r�seau ou serveur. Consultez la console pour plus de d�tails.');
        } finally {
            saveBtn.textContent = originalText;
            saveBtn.disabled = false;
        }
    });
});

/*========================================================================
  FONCTION DE COMPRESSION D'IMAGE
========================================================================*/
function compresserImage(file, quality = 0.6, maxWidth = 1200) {
    console.log("Compression de l'image:", file.name);
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.readAsDataURL(file);

        reader.onerror = () => reject(new Error("Erreur de lecture du fichier"));

        reader.onload = e => {
            const img = new Image();
            img.src = e.target.result;

            img.onerror = () => reject(new Error("Erreur de chargement de l'image"));

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
                    if (blob) {
                        const compressedFile = new File([blob], file.name, { 
                            type: "image/jpeg",
                            lastModified: Date.now()
                        });
                        console.log(`Image compress�e: ${(file.size / 1024).toFixed(2)}KB � ${(blob.size / 1024).toFixed(2)}KB`);
                        resolve(compressedFile);
                    } else {
                        reject(new Error("Erreur de compression"));
                    }
                }, "image/jpeg", quality);
            };
        };
    });
}

function toggleSousEtapes(trajetId) {
    const container = document.getElementById('sous-etapes-' + trajetId);
    const card = document.querySelector('[data-trajet-id="' + trajetId + '"]');
    
    if (container && card) {
        container.classList.toggle('active');
        card.classList.toggle('active');
    }
}

// Initialisation globale pour lightbox
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('imageModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                this.style.display = 'none';
            }
        });
    }

    document.addEventListener('click', function(e) {
        if (e.target.tagName === 'IMG' && e.target.closest('.photos-container')) {
            const modal = document.getElementById('imageModal');
            const modalImg = document.getElementById('imageModalContent');
            if (modal && modalImg) {
                modal.style.display = 'block';
                modalImg.src = e.target.src;
            }
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

    console.log("D�marrage calcul distances (avec pr�f�rences)...");

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

