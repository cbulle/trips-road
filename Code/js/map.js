// --- map.js ---
document.addEventListener('DOMContentLoaded', () => {

  // --- Initialisation de la carte en mode clair ---
  let map = L.map('map').setView([46.5, 2.5], 6);
  var lightLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '© OpenStreetMap'
  }).addTo(map);
  
  // --- Initialisation de la carte en mode sombre
  var darkTiles = L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
    maxZoom: 19,
    attribution: '© OpenStreetMap & © CartoDB'
  });


  let segments = [];
  const markers = {}; // Stockage centralisé de tous les markers


  const strategies = {
    Voiture: { profile: 'driving' },
    Velo: { profile: 'bike' },
    Marche: { profile: 'foot' }
  };

  const segmentColors = [
    'blue',
    'green',
    'orange',
    'red',
    'purple',
    'brown',
    'pink',
    'yellow',
    'cyan',
    'magenta',
    'lime',
    'teal',
    'indigo',
    'violet',
    'gold',
    'silver',
    'maroon',
    'navy',
    'olive',
    'coral'
  ];
  const europeViewbox = [-25.0, 35.0, 30.0, 71.0]; // Zone Europe

  // --- FONCTION UTILITAIRE : Extrait le nom simple de la ville ---
  function getNomSimple(nomComplet) {
      if (!nomComplet) return '';
      // Prend tout ce qui est avant la première virgule (ex: "Lyon, Rhône, France" -> "Lyon")
      return nomComplet.split(',')[0].trim(); 
  }

  // --- Fonction de géocodage (Utilise Nominatim) ---
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

  // --- Fonction générique pour créer un marker et le stocker ---
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
                method: "GET", // Explicite la méthode
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
                    // En cas d'erreur (timeout), on ne fait rien ou on vide la liste
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
  // Cible les INPUTs de classe .etape 
  document.querySelectorAll('.etape').forEach(initAutocomplete);


  // --- 2. Ajout dynamique d'étapes (REFATO AVEC TEMPLATE) ---
  const etapesContainer = document.getElementById('etapesContainer');
  const templateEtape = document.getElementById('template-etape');

  document.getElementById('addEtape').addEventListener('click', () => {
    // Cloner le template
    const clone = templateEtape.content.cloneNode(true);
    const containerRow = clone.querySelector('.etape-row'); // Le div conteneur dans le template
    const input = clone.querySelector('input.etape');
    const removeBtn = clone.querySelector('.btn-remove-etape');

    removeBtn.addEventListener('click', () => {
      // IMPORTANT : Détruire l'instance Autocomplete avant de supprimer le DOM
      $(input).autocomplete('destroy'); 
      containerRow.remove();
    });

    etapesContainer.appendChild(clone);

    // Initialiser Autocomplete sur le nouvel input
    initAutocomplete(input); 

    if (document.getElementById('btnCalculer').style.display === 'none') {
      document.getElementById('btnRecalculer').style.display = 'inline-block';
      document.getElementById('btnLegende').style.display = 'none';
    }
  });


  
  // --- Calcul de l'itinéraire principal ---
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

    if (villes.length < 2) { alert("Veuillez renseigner au moins deux villes."); return; }
    
    const mode = 'Voiture';
    const strategy = strategies[mode] || strategies['Voiture'];

    const coordsVilles = [];
    for (const ville of villes) {
      const coords = await getCoordonnees(ville);
      if (!coords) { alert(`Ville introuvable ou hors Europe : ${ville}`); return; }
      coordsVilles.push(coords);
    }

    // Supprimer tous les anciens markers et segments
    Object.values(markers).flat().forEach(m => map.removeLayer(m.marker));
    for (const seg of segments) if (seg.line) map.removeLayer(seg.line);
    segments = [];

    // Marqueurs départ/arrivée
    addMarker(villes[0], coordsVilles[0], "ville", `Départ : ${villes[0]}`);
    addMarker(villes[villes.length - 1], coordsVilles[coordsVilles.length - 1], "ville", `Arrivée : ${villes[villes.length - 1]}`);

    // Marqueurs pour chaque étape intermédiaire
    for (let i = 1; i < coordsVilles.length - 1; i++) {
      addMarker(villes[i], coordsVilles[i], "ville", `Étape : ${villes[i]}`);
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

    // Séquence de villes existantes
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

    // Dernière ville connue
    const lastKnownCity = existingSequence[existingSequence.length - 1];
    const lastKnownIndexInInputs = inputs.lastIndexOf(lastKnownCity);

    if (lastKnownIndexInInputs === -1) {
      console.warn("Dernière ville connue introuvable. Recalcul global.");
      await calculItineraire();
      return;
    }

    if (lastKnownIndexInInputs === inputs.length - 1) {
      alert("Aucune nouvelle ville à ajouter.");
      return;
    }

    // Nouvelles villes à ajouter
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

        // Ajouter marker pour la nouvelle ville
        addMarker(villeArr, coords, "ville", `Étape : ${villeArr}`);

        // Ajouter le segment complet
        await _ajouterSegmentEntre(depart, departCoords, villeArr, coords, segments.length, strategies['Voiture']);

        // Mise à jour pour le prochain départ
        depart = villeArr;
        departCoords = coords;
      }

      // Marker d'arrivée
      const derniereSeg = segments[segments.length - 1];
      if (markers['Arrivee']) {
        markers['Arrivee'].forEach(m => map.removeLayer(m.marker));
        delete markers['Arrivee'];
      }
      markers['Arrivee'] = [{ marker: L.marker(derniereSeg.endCoord).addTo(map).bindPopup(`Arrivée : ${derniereSeg.endName}`), type: "ville", text: `Arrivée : ${derniereSeg.endName}` }];

      // Ajuster la vue pour inclure tous les segments
      const allCoords = segments.flatMap(s => [s.startCoord, s.endCoord]);
      if (allCoords.length) map.fitBounds(allCoords);

      alert(`${nouvelles.length} segment(s) ajoutés.`);

    } catch (err) {
      console.error("Erreur lors de l'ajout des nouveaux segments :", err);
      alert("Erreur lors de l'ajout des nouveaux segments. Voir console.");
    }
  }


  // --- Fonction pour ajouter un segment (REFATO AVEC TEMPLATE) ---
  async function _ajouterSegmentEntre(startName, startCoords, endName, endCoords, index, strategy) {
    const coordString = `${startCoords[1]},${startCoords[0]};${endCoords[1]},${endCoords[0]}`;
    const url = `https://router.project-osrm.org/route/v1/${strategy.profile}/${coordString}?overview=full&geometries=geojson`;

    try {
      const resp = await fetch(url);
      const data = await resp.json();
      if (data.code !== 'Ok') return;

      const route = data.routes[0];
      const couleurSegment = segmentColors[index % segmentColors.length];
      const line = L.geoJSON(route.geometry, { color: couleurSegment, weight: 5, opacity: 0.8 }).addTo(map);

      // NOUVEAU : Calculer les noms simples pour la légende
      const startNameSimple = getNomSimple(startName);
      const endNameSimple = getNomSimple(endName);

      segments.push({
        line,
        startName: startName, // CONSERVE LE NOM COMPLET
        startCoord: startCoords,
        endName: endName,     // CONSERVE LE NOM COMPLET
        endCoord: endCoords,
        distance: (route.distance / 1000).toFixed(1),
        duration: Math.floor(route.duration / 60),
        couleurSegment,
        sousEtapes: [],
        startNameSimple: startNameSimple, // Ajout des noms simples
        endNameSimple: endNameSimple
      });

      // --- UTILISATION DU TEMPLATE POUR LA LÉGENDE ---
      const templateLegend = document.getElementById('template-legend-item');
      const clone = templateLegend.content.cloneNode(true);
      const li = clone.querySelector('li');
      
      li.dataset.index = index;

      // Configuration de la couleur
      const colorBox = clone.querySelector('.legend-color-indicator');
      colorBox.style.background = couleurSegment; // Seul style dynamique conservé en JS

      // Configuration du texte du bouton toggle
      const toggleBtn = clone.querySelector('.toggleSousEtapes');
      toggleBtn.dataset.index = index;
      toggleBtn.innerHTML = `${startNameSimple} → ${endNameSimple} <strong style="margin-left:5px; color: ${couleurSegment}; border: none;">▼</strong>`; // Strong color dynamic

      // Configuration du bouton modifier
      const modifBtn = clone.querySelector('.modifierSousEtapes');
      modifBtn.dataset.index = index;

      // Configuration de la liste
      const ulSub = clone.querySelector('.sousEtapesList');
      ulSub.dataset.index = index;

      document.getElementById('legendList').appendChild(clone);

    } catch (e) {
      console.error("Erreur segment :", e);
    }
  }

  // Fonction pour afficher le formulaire de segment
  // (Le bouton fermer est maintenant en dur dans le HTML, on gère juste l'événement ici si besoin ou dans init)
  function showSegmentForm() {
    const container = document.getElementById('segmentFormContainer');
    container.style.display = 'block';
    document.getElementsByClassName('sidebar')[0].style.width = '300px';
    // Reset de la classe 'hidden' si elle était présente (pour l'animation)
    container.classList.remove('hidden');

    // Gestion de la fermeture via le bouton statique
    const closeBtn = document.getElementById('closeSegmentForm');
    // On s'assure de ne pas empiler les events listeners si on appelle la fonction plusieurs fois
    // Une façon simple est de cloner le bouton pour nettoyer les listeners, ou juste de vérifier.
    // Ici, le plus simple est de mettre le listener une seule fois au chargement (voir plus bas)
    // ou de laisser faire si c'est déjà géré.
  }

  // Initialisation event listener fermeture formulaire (une seule fois)
  const closeSegmentBtn = document.getElementById('closeSegmentForm');
  if(closeSegmentBtn){
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

  // --- Gestion des sous-étapes (REFATO AVEC TEMPLATE) ---
  const segmentFormContainer = document.getElementById('segmentFormContainer');
  const subEtapesContainer = document.getElementById('subEtapesContainer');
  const segmentDateInput = document.getElementById('segmentDate');
  let currentSegmentIndex = null;
  const templateSubEtape = document.getElementById('template-sub-etape');

  function addSousEtapeForm(data = {}) {
    // ID unique est CRITIQUE pour TinyMCE
    const uniqueId = 'editor-' + Date.now() + Math.random().toString(36).substring(2, 9);
    
    // Cloner le template
    const clone = templateSubEtape.content.cloneNode(true);
    const div = clone.querySelector('.subEtape'); // Le container principal du clone

    // Remplir les données
    const inputNom = clone.querySelector('.subEtapeNom');
    inputNom.value = data.nom || '';

    const textArea = clone.querySelector('.subEtapeRemarque');
    textArea.id = uniqueId; // Assigner l'ID unique
    textArea.value = data.remarque || ''; // Value pour textarea (avant init TinyMCE)

    const inputHeure = clone.querySelector('.subEtapeHeure');
    inputHeure.value = data.heure || '';

    // Ajouter au DOM
    subEtapesContainer.appendChild(div);

    // Initialiser Autocomplete sur l'input de nom
    initAutocomplete(inputNom);

    // NOUVEAU : INITIALISATION DE TINYMCE
    tinymce.init({
        selector: `#${uniqueId}`,
        plugins: 'table lists link code visualblocks autoresize fullscreen textcolor colorpicker',
        toolbar: 'bold italic underline | forecolor backcolor | bullist numlist | indent outdent | alignleft aligncenter alignright alignjustify | table | code | visualblocks | fullscreen',
        menubar: false,
        height: 1500, // Hauteur de l'éditeur
        branding: false, // Cache le logo TinyMCE
        statusbar: false
    });

    // Ajouter l'écouteur d'événement pour la suppression
    const removeBtn = div.querySelector('.removeSubEtapeBtn');
    removeBtn.addEventListener('click', () => {
        // DÉTRUIRE L'ÉDITEUR AVANT DE SUPPRIMER LE DIV
        const editor = tinymce.get(uniqueId);
        if (editor) editor.remove();
        div.remove(); 
    });
  }

  // --- Clic sur un segment pour modifier sous-étapes (MODIFIÉ) ---
  document.getElementById('legendList').addEventListener('click', e => {
    // On remonte jusqu'au LI car le bouton peut contenir du HTML
    const li = e.target.closest('li');
    if (!li) return;

    // Gestion du clic sur le bouton "Modifier"
    if (e.target.classList.contains('modifierSousEtapes')) {
        const index = parseInt(li.dataset.index);
        if (isNaN(index)) return;
        currentSegmentIndex = index;

        const seg = segments[index];
        
        // Utilisation des noms simples stockés pour le titre
        const start = seg.startNameSimple || getNomSimple(seg.startName); 
        const end = seg.endNameSimple || getNomSimple(seg.endName); 
        
        document.getElementById('segmentTitle').textContent = `Modifier le segment : ${start} → ${end}`;
        showSegmentForm();
        
        // Le contenu du subEtapesContainer est vidé ici avant d'être reconstruit
        subEtapesContainer.innerHTML = ''; 
        seg.sousEtapes.forEach(se => addSousEtapeForm(se));
        segmentDateInput.value = seg.date || '';
    }
  });

  document.getElementById('addSubEtape').addEventListener('click', () => addSousEtapeForm());

  // --- Sauvegarder sous-étapes + ajout markers (MODIFIÉ POUR TINYMCE) ---
document.getElementById('saveSegment').addEventListener('click', async () => {
    if (currentSegmentIndex === null) return;
    const seg = segments[currentSegmentIndex];
    seg.date = segmentDateInput.value;
    seg.sousEtapes = [];

    // --- Collecte des sous-étapes et création des objets ---
    const sousEtapeNoms = [];
    const subDivs = Array.from(document.querySelectorAll('.subEtape'));
    for (const div of subDivs) {
        const nom = div.querySelector('.subEtapeNom').value.trim();
        const heure = div.querySelector('.subEtapeHeure').value.trim();
        // Récupération de l'ID du textarea (maintenant TinyMCE)
        const textareaElement = div.querySelector('textarea.subEtapeRemarque');
        
        // CORRECTION CRITIQUE : Récupération du contenu HTML de l'éditeur TinyMCE
        // Si l'éditeur existe, on prend son contenu; sinon, on prend la valeur du textarea (utile pour les cas non initialisés)
        const remarque = tinymce.get(textareaElement.id) ? tinymce.get(textareaElement.id).getContent() : textareaElement.value;
        
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
        alert("Aucune sous-étape renseignée. Enregistrement simple.");
        segmentFormContainer.style.display = 'none';
        return;
    }

    // --- Calcul de l'itinéraire passant par les sous-étapes ---
    const allPlaces = [seg.startName, ...sousEtapeNoms, seg.endName];
    const coordsList = [];
    
    // VÉRIFICATION/SAUVEGARDE DE TOUTES LES VILLES (ÉTAPE ET SOUS-ÉTAPE) DANS lieux_geocodes
    for (const place of allPlaces) {
        const coords = await getCoordonnees(place);
        if (!coords) {
            alert(`Lieu introuvable ou hors Europe : ${place}. Annulation du segment.`);
            return;
        }

        // Sauvegarde/Vérification de la ville et de ses coordonnées dans la table lieux_geocodes
        const [lat, lon] = coords;
        const saved = await saveCoordsToDB(place, lat, lon);
        if (!saved) {
            alert(`Erreur lors de la sauvegarde/vérification des coordonnées de la ville ${place} en base de données. Annulation de la sauvegarde du segment.`);
            return;
        }

        coordsList.push(coords);
    }
    // FIN VÉRIFICATION/SAUVEGARDE

    const coordPairs = coordsList.map(c => `${c[1]},${c[0]}`).join(';');
    const strategy = strategies['Voiture']; // ou récupérer le mode existant
    const url = `https://router.project-osrm.org/route/v1/${strategy.profile}/${coordPairs}?overview=full&geometries=geojson`;

    try {
        const resp = await fetch(url);
        const data = await resp.json();
        if (data.code !== 'Ok') {
            alert("Erreur lors du recalcul du trajet.");
            return;
        }

        // --- Mise à jour de la ligne du segment ---
        if (seg.line) map.removeLayer(seg.line);
        const route = data.routes[0];
        seg.line = L.geoJSON(route.geometry, { color: seg.couleurSegment, weight: 5, opacity: 0.8 }).addTo(map);
        seg.distance = (route.distance / 1000).toFixed(1);
        seg.duration = Math.floor(route.duration / 60);

        // --- Ajout des marqueurs pour chaque sous-étape ---
        for (const se of seg.sousEtapes) {
            const coords = await getCoordonnees(se.nom); 
            if (!coords) continue;

            let popupText = `<b>${se.nom}</b>`;
            
            // Le HTML stocké dans 'remarque' doit être affiché ici
            if (se.remarque) popupText += `<br><em>${se.remarque}</em>`;

            if (se.heure) popupText += `<br>Heure : ${se.heure}`;

            // Gestion de plusieurs photos
            if (se.photos && se.photos.length > 0) {
                se.photos.forEach(f => {
                    const url = URL.createObjectURL(f);
                    popupText += `<br><img src="${url}" class="popup-photo">`;
                });
            }

            addMarker(se.nom, coords, "sous_etape", popupText);
        }

        alert(`Segment "${seg.startName} → ${seg.endName}" recalculé (${seg.distance} km, ${seg.duration} min).`);
        segmentFormContainer.style.display = 'none';
      } catch (err) {
          console.error(err);
          alert("Erreur lors du recalcul d’itinéraire.");
      }
      document.getElementsByClassName('sidebar')[0].style.width = '450px';
  });


  // --- Toggle sous-étapes dans la légende (MODIFIÉ) ---
  document.getElementById('legendList').addEventListener('click', e => {
    // Si c'est le bouton toggle ou un enfant
    const toggleBtn = e.target.closest('.toggleSousEtapes');
    if (toggleBtn) {
      const index = toggleBtn.dataset.index;
      const ul = document.querySelector(`.sousEtapesList[data-index="${index}"]`);
      if (!ul) return;

      if (ul.style.display === 'none') {
        const seg = segments[index];
        ul.innerHTML = '';
        
        // --- 1. Ajouter la ville de départ ---
        let liDepart = document.createElement('li');
        const startSimple = seg.startNameSimple || getNomSimple(seg.startName);
        liDepart.innerHTML = `<div><span style="font-weight: bold;">Départ: ${startSimple}</span></div>`; 
        ul.appendChild(liDepart);
        
        // --- 2. Ajouter les sous-étapes ---
        if (seg.sousEtapes.length > 0) {
          seg.sousEtapes.forEach(se => {
            let photoHTML = '';
            if (se.photos && se.photos.length > 0) {
              const url = URL.createObjectURL(se.photos[0]);
              // Utilisation de classe CSS .legend-thumb pour le style
              photoHTML = `<img src="${url}" class="sousetape-photo legend-thumb">`;
            }
            
            const li = document.createElement('li');
            // Utilisation de classes CSS
            li.innerHTML = `<div class="legend-sub-item-content">
                              <span class="legend-sub-text">
                                <strong>${getNomSimple(se.nom)}</strong>${se.heure ? ` (${se.heure})` : ''}<br>
                                ${se.remarque || ''} 
                              </span>
                              ${photoHTML}
                            </div>`;
            ul.appendChild(li);
          });
        } else {
          let liAucune = document.createElement('li');
          liAucune.innerHTML = '<em>Aucune sous-étape</em>';
          ul.appendChild(liAucune);
        }

        // --- 3. Ajouter la ville d'arrivée ---
        let liArrivee = document.createElement('li');
        const endSimple = seg.endNameSimple || getNomSimple(seg.endName);
        liArrivee.innerHTML = `<div><span style="font-weight: bold;">Arrivée: ${endSimple}</span></div>`;
        ul.appendChild(liArrivee);
        
        ul.style.display = 'block';
      } else ul.style.display = 'none';
    }
  });
  

  function saveEtapes() {
    const villes = Array.from(document.querySelectorAll('#etapesContainer input.etape'))
      .map(input => input.value.trim())
      .filter(ville => ville.length > 0);
    return villes;
  }

  document.getElementById('btnModifier').addEventListener('click', () => {
    // 1. Rendre visibles les éléments de création
    document.getElementById('etapesContainer').style.display = 'block';
    document.getElementById('addEtape').style.display = 'inline-block';
    document.getElementById('btnCalculer').style.display = 'none';
    if(document.getElementById('segmentFormContainer').style.display ==='block'){
      document.getElementById('segmentFormContainer').style.display ='none';
    }

    // 2. Cacher la légende et les boutons de navigation
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

  // --- Recalculer l'itinéraire ---
  document.getElementById('btnRecalculer').addEventListener('click', recalculerDerniersSegmentsMultiples);
  document.getElementById('btnRecalculer').addEventListener('click', () =>{
    document.getElementById('etapesContainer').style.display = 'none';
    document.getElementById('addEtape').style.display = 'none';
    document.getElementById('btnCalculer').style.display = 'none';
    // Afficher la légende
    document.getElementById('legend').style.display = 'block';
    // Afficher le bouton pour revenir en arrière
    document.getElementById('btnModifier').style.display = 'inline-block';
    document.getElementById('btnRecalculer').style.display = 'none';
  });

  // --- Lightbox images ---
  document.addEventListener('click', e => {
    if (e.target.classList.contains('sousetape-photo')) {
      const modal = document.getElementById('imageModal');
      const modalImg = document.getElementById('imageModalContent');
      // Les classes et styles sont maintenant gérés par CSS
      modal.style.display = 'block';
      modalImg.src = e.target.src;
    } else if (e.target.classList.contains('image-modal')) {
      e.target.style.display = 'none';
    }
  });

/*========================================================================
  FONCTION DE SAUVEGARDE/VÉRIFICATION DES COORDONNÉES EN BASE DE DONNÉES
========================================================================*/
async function saveCoordsToDB(ville, lat, lon) {
    try {
        const response = await fetch('../include/geocode.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            // On envoie le nom, la latitude et la longitude trouvées par Nominatim (via getCoordonnees)
            body: JSON.stringify({ nom: ville, lat: lat, lon: lon })
        });
        const result = await response.json();
        
        if (!result.success) {
            console.error(`Erreur DB pour ${ville}:`, result.message);
            // Si le PHP échoue à insérer ou vérifier, on le signale.
            return false;
        }
        // Si successful, cela signifie que la ville est maintenant dans lieux_geocodes.
        return true;
    } catch (e) {
        console.error("Erreur réseau ou serveur lors de la sauvegarde/vérification des coordonnées:", e);
        return false;
    }
}


/*========================================================================
SAUVEGARDE D'UN ROADTRIP DANS LA BASE DE DONNÉES
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

    // Récupérer toutes les villes des étapes
    const villesInputs = Array.from(document.querySelectorAll('#etapesContainer input.etape'))
        .map(input => input.value.trim())
        .filter(v => v.length > 0);

    if (villesInputs.length < 2) {
        alert("Veuillez saisir au moins deux villes.");
        return;
    }

    // Préparer les données des villes avec leurs coordonnées ET s'assurer qu'elles sont dans lieux_geocodes
    const villesGeo = [];
    for (const ville of villesInputs) {
        // 1. Obtenir les coordonnées via Nominatim (si elles n'étaient pas connues)
        const coords = await getCoordonnees(ville);
        if (!coords) {
            alert(`Ville introuvable ou hors Europe : ${ville}`);
            return;
        }
        
        const [lat, lon] = coords;
        
        // 2. Vérifier/Sauvegarder la ville et ses coordonnées dans la table lieux_geocodes
        const saved = await saveCoordsToDB(ville, lat, lon);
        if (!saved) {
            alert(`Erreur lors de la sauvegarde/vérification des coordonnées de la ville ${ville} en base de données. Annulation de la sauvegarde du RoadTrip.`);
            return;
        }
        
        // Ajouter la ville pour la sauvegarde du RoadTrip principal
        villesGeo.push({ 
            nom: ville, 
            lat: lat, 
            lon: lon 
        });
    }

    // Préparer les trajets avec leurs sous-étapes
    const trajets = segments.map((seg, sIdx) => ({
        depart: seg.startName,
        arrivee: seg.endName,
        mode: 'Voiture',
        sousEtapes: seg.sousEtapes.map((se, seIdx) => ({
            nom: se.nom,
            remarque: se.remarque,
            heure: se.heure,
            photos: se.photos ? se.photos.map((f, i) => `file_s${sIdx}_se${seIdx}_${i}`) : []
        }))
    }));

    // Créer le FormData pour l'envoi
    const formData = new FormData();
    formData.append('titre', titre);
    formData.append('description', description);
    formData.append('visibilite', visibilite);
    formData.append('villes', JSON.stringify(villesGeo));
    formData.append('trajets', JSON.stringify(trajets));

    // Ajouter la photo de couverture (compressée)
    if (photoCover) {
        photoCover = await compresserImage(photoCover, 0.6, 1200);
        formData.append('photo_cover', photoCover);
    }

    // Ajouter toutes les photos des sous-étapes
    segments.forEach((seg, sIdx) => {
        seg.sousEtapes.forEach((se, seIdx) => {
            if (se.photos && se.photos.length) {
                se.photos.forEach((f, i) => {
                    formData.append(`file_s${sIdx}_se${seIdx}_${i}`, f);
                });
            }
        });
    });

    // Afficher un indicateur de chargement
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
        console.log("Réponse brute du serveur :", text);

        let result;
        try {
            result = JSON.parse(text);
        } catch (e) {
            console.error("Erreur de parsing JSON:", e);
            throw new Error("Réponse invalide du serveur");
        }

        if (result.success) {
            alert('RoadTrip sauvegardé avec succès !');
            
            // Réinitialiser le formulaire
            document.getElementById('roadtripTitle').value = '';
            document.getElementById('roadtripDescription').value = '';
            document.getElementById('roadtripVisibilite').selectedIndex = 0;
            document.getElementById('roadtripPhoto').value = '';
            document.getElementById('etapesContainer').innerHTML = '';
            
            // Nettoyer la carte
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
        alert('Erreur réseau ou serveur. Consultez la console pour plus de détails.');
    } finally {
        // Réactiver le bouton
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

                // Redimensionner si trop large
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
                        console.log(`Image compressée: ${(file.size / 1024).toFixed(2)}KB → ${(blob.size / 1024).toFixed(2)}KB`);
                        resolve(compressedFile);
                    } else {
                        reject(new Error("Erreur de compression"));
                    }
                }, "image/jpeg", quality);
            };
        };
    });
}

// NOTE: Le code suivant (modal et toggle) est partiellement redondant si utilisé uniquement sur vuRoadTrip, 
// mais conservé ici pour compatibilité si map.js est utilisé ailleurs.
// J'ai retiré la création dynamique du modal ici car il est dans le PHP maintenant.

function toggleSousEtapes(trajetId) {
    const container = document.getElementById('sous-etapes-' + trajetId);
    const card = document.querySelector('[data-trajet-id="' + trajetId + '"]');
    
    if (container && card) {
        container.classList.toggle('active');
        card.classList.toggle('active');
    }
}

// Initialisation globale pour lightbox si non gérée ailleurs
document.addEventListener('DOMContentLoaded', function() {
    // Le modal est supposé être dans le HTML maintenant (voir template PHP)
    const modal = document.getElementById('imageModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            // Ferme si on clique sur le fond (la classe .image-modal)
            if (e.target === this) {
                this.style.display = 'none';
            }
        });
    }

    // Event delegation pour les images
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
Elements de gestion du formulaire d'inscription et de connexion
=======================================*/

function showLogin() {
    const loginForm = document.getElementById('loginForm');
    if(loginForm) {
        loginForm.style.display = 'block';
        document.getElementById('registerForm').style.display = 'none';
        document.getElementById('btnLogin').classList.add('active');
        document.getElementById('btnRegister').classList.remove('active');
    }
}

function showRegister() {
    const regForm = document.getElementById('registerForm');
    if(regForm) {
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
    // Si on est sur la page de profil/login
    if(document.querySelector('.formulaire')) {
        openModal();
        showLogin(); 
    }
});


/*=======================================
          Changement de thème
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
  FONCTION DE RÉCUPÉRATION ASYNCHRONE DES DISTANCES ET TEMPS ENTRE DEUX POINTS
========================================================================*/

document.addEventListener("DOMContentLoaded", function() {
    console.log("Démarrage du calcul des distances (mode progressif)...");

    const elements = document.querySelectorAll('.js-calculate-distance');
    
    if(elements.length === 0) {
        console.warn("Aucun élément '.js-calculate-distance' trouvé.");
    }
    elements.forEach(function(el, i) {
        
        setTimeout(function() {

            const latDep = el.dataset.latDep;
            const lonDep = el.dataset.lonDep;
            const latArr = el.dataset.latArr;
            const lonArr = el.dataset.lonArr;
            let mode = el.dataset.mode || 'voiture';

            const distEl = el.querySelector('.result-distance');
            const timeEl = el.querySelector('.result-time');

            if (!latDep || !lonDep || !latArr || !lonArr) {
                distEl.innerHTML = "N/A";
                timeEl.innerHTML = "N/A";
                return;
            }

            const profiles = {
                'voiture': 'car',
                'velo': 'bike',
                'vélo': 'bike',
                'marche': 'foot',
                'à pied': 'foot',
                'a pied': 'foot'
            };
            const profile = profiles[mode.toLowerCase()] || 'car';

            const url = `https://router.project-osrm.org/route/v1/${profile}/${lonDep},${latDep};${lonArr},${latArr}?overview=false`;

            fetch(url)
                .then(response => {
                    if (response.status === 429) {
                        throw new Error("Trop de requêtes (429)");
                    }
                    if (!response.ok) throw new Error("Erreur réseau");
                    return response.json();
                })
                .then(data => {
                    if (data.code === 'Ok' && data.routes && data.routes.length > 0) {
                        const route = data.routes[0];
                        
                        const distKm = (route.distance / 1000).toFixed(1).replace('.', ',');
                        distEl.innerHTML = `<strong>${distKm} km</strong>`;

                        const duration = route.duration;
                        const h = Math.floor(duration / 3600);
                        const m = Math.floor((duration % 3600) / 60);
                        let timeText = "";
                        if (h > 0) timeText += `${h}h `;
                        timeText += `${m}min`;

                        timeEl.innerHTML = timeText;
                    } else {
                        distEl.innerHTML = "-";
                        timeEl.innerHTML = "-";
                    }
                })
                .catch(error => {
                    console.error("Erreur calcul:", error);
                    distEl.innerHTML = "<span class='error-data'>Busy</span>";
                    timeEl.innerHTML = "<span class='error-data'>Busy</span>";
                });

        }, i * 1500); 
    });
});