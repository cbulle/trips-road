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

  const segmentColors = ['blue', 'green', 'orange', 'red', 'purple', 'brown', 'pink'];
  const europeViewbox = [-25.0, 35.0, 30.0, 71.0]; // Zone Europe

  // --- Fonction de géocodage ---
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

  // --- Ajout dynamique d'étapes ---
  const etapesContainer = document.getElementById('etapesContainer');
  document.getElementById('addEtape').addEventListener('click', () => {
    const container = document.createElement('div');
    container.style.display = 'flex';
    container.style.alignItems = 'center';
    container.style.marginBottom = '5px';

    const input = document.createElement('input');
    input.type = 'text';
    input.placeholder = 'Étape supplémentaire';
    input.classList.add('etape');
    input.style.flex = '1';

    const removeBtn = document.createElement('button');
    removeBtn.type = 'button';
    removeBtn.textContent = '✖';
    removeBtn.style.marginLeft = '5px';
    removeBtn.style.cursor = 'pointer';
    removeBtn.title = 'Supprimer cette étape';

    removeBtn.addEventListener('click', () => {
      container.remove();
    });

    container.appendChild(input);
    container.appendChild(removeBtn);
    etapesContainer.appendChild(container);

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

    const villes = Array.from(document.querySelectorAll('#etapesContainer input'))
      .map(input => input.value.trim())
      .filter(ville => ville.length > 0);

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
      .map(i => i.value.trim())
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


  // --- Fonction pour ajouter un segment ---
  async function _ajouterSegmentEntre(startName, startCoords, endName, endCoords, index, strategy) {
    const coordString = `${startCoords[1]},${startCoords[0]};${endCoords[1]},${endCoords[0]}`;
    const url = `https://router.project-osrm.org/route/v1/${strategy.profile}/${coordString}?overview=full&geometries=geojson`;

    try {
      const resp = await fetch(url);
      const data = await resp.json();
      if (data.code !== 'Ok') return;

      const route = data.routes[0];
      const line = L.geoJSON(route.geometry, { color: segmentColors[index % segmentColors.length], weight: 5, opacity: 0.8 }).addTo(map);

      segments.push({
        line,
        startName,
        startCoord: startCoords,
        endName,
        endCoord: endCoords,
        distance: (route.distance / 1000).toFixed(1),
        duration: Math.floor(route.duration / 60),
        sousEtapes: []
      });

      // Ajouter au DOM légende
      const li = document.createElement('li');
      li.dataset.index = index;
      li.innerHTML = `
        <div class="segment-header" style="display:flex;align-items:center;gap:5px;cursor:pointer;">
          <span style="display:inline-block;width:15px;height:15px;background:${segmentColors[index % segmentColors.length]};border-radius:3px;"></span>
          <button class="toggleSousEtapes" data-index="${index}" style="margin-left:auto;">
            ${startName} → ${endName}
          </button>
        </div>
        <button class="modifierSousEtapes" data-index="${index}">Modifier</button>
        <ul class="sousEtapesList" data-index="${index}" style="display:none;list-style:none;padding-left:20px;"></ul>
      `;
      document.getElementById('legendList').appendChild(li);

    } catch (e) {
      console.error("Erreur segment :", e);
    }
  }

  // Fonction pour afficher le formulaire de segment avec croix de fermeture
  function showSegmentForm() {
    const container = document.getElementById('segmentFormContainer');
    container.style.display = 'block';

    if (!document.getElementById('closeSegmentForm')) {
      const closeBtn = document.createElement('span');
      closeBtn.id = 'closeSegmentForm';
      closeBtn.textContent = '✖';
      closeBtn.title = 'Fermer';
      closeBtn.style.position = 'absolute';
      closeBtn.style.top = '5px';
      closeBtn.style.right = '5px';
      closeBtn.style.cursor = 'pointer';
      closeBtn.style.fontSize = '18px';
      closeBtn.style.fontWeight = 'bold';
      closeBtn.style.color = 'red';

      closeBtn.addEventListener('click', () => {
        container.classList.add('hidden');
        // Après la durée de transition, on met display:none
        setTimeout(() => {
          container.style.display = 'none';
          container.classList.remove('hidden'); // reset pour prochaine ouverture
        }, 300); // 300ms = même durée que la transition CSS
      });

      container.style.position = 'relative';
      container.appendChild(closeBtn);
    }
  }

  // --- Gestion des sous-étapes ---
  const segmentFormContainer = document.getElementById('segmentFormContainer');
  const subEtapesContainer = document.getElementById('subEtapesContainer');
  const segmentDateInput = document.getElementById('segmentDate');
  let currentSegmentIndex = null;

  function addSousEtapeForm(data = {}) {
    const div = document.createElement('div');
    div.classList.add('subEtape');
    div.style.marginBottom = '10px';
    div.innerHTML = `
      <input type="text" placeholder="Nom du lieu ou ville" class="subEtapeNom" value="${data.nom || ''}">
      <textarea class="subEtapeRemarque" placeholder="Remarque (facultatif)">${data.remarque || ''}</textarea>
      <input type="time" class="subEtapeHeure" value="${data.heure || ''}">
      <input type="file" class="subEtapePhoto" accept="image/*">
    `;
    subEtapesContainer.appendChild(div);
  }

  // --- Clic sur un segment pour modifier sous-étapes ---
  document.getElementById('legendList').addEventListener('click', e => {
    const li = e.target.closest('li');
    if (!li) return;

    const index = parseInt(li.dataset.index);
    if (isNaN(index)) return;
    currentSegmentIndex = index;

    if (e.target.classList.contains('modifierSousEtapes')) {
      const seg = segments[index];
      document.getElementById('segmentTitle').textContent = `Modifier le segment : ${seg.startName} → ${seg.endName}`;
      showSegmentForm();
      subEtapesContainer.innerHTML = '';
      seg.sousEtapes.forEach(se => addSousEtapeForm(se));
      segmentDateInput.value = seg.date || '';
    }
  });

  document.getElementById('addSubEtape').addEventListener('click', () => addSousEtapeForm());

  // --- Sauvegarder sous-étapes + ajout markers ---
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
      const remarque = div.querySelector('.subEtapeRemarque').value.trim();
      const heure = div.querySelector('.subEtapeHeure').value.trim();
      const photo = div.querySelector('.subEtapePhoto').files[0] || null;
      if (!nom) continue;

      const se = { nom, remarque, heure, photo };
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
    for (const place of allPlaces) {
      const coords = await getCoordonnees(place);
      if (!coords) {
        alert(`Lieu introuvable : ${place}`);
        return;
      }
      coordsList.push(coords);
    }

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
      seg.line = L.geoJSON(route.geometry, { color: 'blue', weight: 5, opacity: 0.8 }).addTo(map);
      seg.distance = (route.distance / 1000).toFixed(1);
      seg.duration = Math.floor(route.duration / 60);

      // --- Ajout des marqueurs pour chaque sous-étape ---
      for (const se of seg.sousEtapes) {
        const coords = await getCoordonnees(se.nom);
        if (!coords) continue;

        let popupText = `<b>${se.nom}</b>`;
        if (se.remarque) popupText += `<br><em>${se.remarque}</em>`;
        if (se.heure) popupText += `<br>Heure : ${se.heure}`;
        if (se.photo) {
          const url = URL.createObjectURL(se.photo);
          popupText += `<br><img src="${url}" class="popup-photo">`;
        }

        addMarker(se.nom, coords, "sous_etape", popupText);
      }

      alert(`Segment "${seg.startName} → ${seg.endName}" recalculé (${seg.distance} km, ${seg.duration} min).`);
      segmentFormContainer.style.display = 'none';
    } catch (err) {
      console.error(err);
      alert("Erreur lors du recalcul d’itinéraire.");
    }
  });

  // --- Toggle sous-étapes dans la légende ---
  document.getElementById('legendList').addEventListener('click', e => {
    if (e.target.classList.contains('toggleSousEtapes')) {
      const index = e.target.dataset.index;
      const ul = document.querySelector(`.sousEtapesList[data-index="${index}"]`);
      if (!ul) return;

      if (ul.style.display === 'none') {
        const seg = segments[index];
        ul.innerHTML = '';
        if (seg.sousEtapes.length > 0) {
          seg.sousEtapes.forEach(se => {
            let photoHTML = se.photo ? `<img src="${URL.createObjectURL(se.photo)}" class="sousetape-photo">` : '';
            const li = document.createElement('li');
            li.innerHTML = `<div><strong>${se.nom}</strong>${se.heure ? ` (${se.heure})` : ''}<br>${se.remarque || ''}${photoHTML}</div>`;
            ul.appendChild(li);
          });
        } else {
          ul.innerHTML = '<li><em>Aucune sous-étape</em></li>';
        }
        ul.style.display = 'block';
      } else ul.style.display = 'none';
    }
  });

  function saveEtapes() {
    const villes = Array.from(document.querySelectorAll('#etapesContainer input'))
      .map(input => input.value.trim())
      .filter(ville => ville.length > 0);
    return villes;
  }

  document.getElementById('btnModifier').addEventListener('click', () => {
    // Réafficher les éléments de création
    document.getElementById('etapesContainer').style.display = 'block';
    document.getElementById('addEtape').style.display = 'inline-block';
    document.getElementById('btnCalculer').style.display = 'inline-block';

    // Cacher la légende
    document.getElementById('legend').style.display = 'none';

    // Cacher le bouton "Modifier l'itinéraire"
    document.getElementById('btnModifier').style.display = 'none';
    document.getElementById('btnCalculer').style.display = 'none';
    document.getElementById('btnLegende').style.display = 'inline-block';

    const savedEtapes = saveEtapes();
    console.log(savedEtapes);
    
    // Réafficher les étapes sauvegardées
    const etapesContainer = document.getElementById('etapesContainer');
    etapesContainer.innerHTML = ''; // Réinitialise les étapes pour éviter les doublons
    savedEtapes.forEach(ville => {
      const input = document.createElement('input');
      input.type = 'text';
      input.placeholder = 'Étape supplémentaire';
      input.classList.add('etape');
      input.value = ville; // Remplir avec la ville sauvegardée
      etapesContainer.appendChild(input);
    });
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
      modal.style.display = 'block';
      modalImg.src = e.target.src;
    } else if (e.target.classList.contains('image-modal')) {
      e.target.style.display = 'none';
    }
  });

  /*=========================================================================================
  COLLECTE DES ELEMENTS DU ROAD TRIP A SAUVEGARDER EN FICHIER JSON EN VUE DE LA SAUVEGARDE BD
  =========================================================================================*/

  function collectEtapes() {
    return Array.from(document.querySelectorAll('#etapesContainer input.etape'))
      .map(i => i.value.trim())
      .filter(v => v.length > 0);
  }


  function buildRoadtripPayload() {
    const title = document.getElementById('roadtripTitle').value.trim();
    const description = document.getElementById('roadtripDescription').value.trim();
    const visibilite = document.getElementById('roadtripVisibilite').value || 'public';

    const etapes = collectEtapes();
    const payload = {
      roadtrip: {
        titre: title,
        description: description,
        visibilite: visibilite
        // id_utilisateur: id_utilisateur
      },
      trajets: []
    };

    const filesToUpload = {};

    segments.forEach((seg, iSeg) => {
      const trajetObj = {
        numero: iSeg + 1,
        titre: seg.startName + ' → ' + seg.endName,
        depart: seg.startName,
        arrivee: seg.endName,
        date_trajet: seg.date || null,
        mode_transport: seg.mode_transport || 'Voiture',
        distance: seg.distance || null,
        duree: seg.duration || null,
        sous_etapes: []
      };

      if (Array.isArray(seg.sousEtapes)) {
        seg.sousEtapes.forEach((se, iSe) => {
          const sousObj = {
            numero: iSe + 1,
            ville: se.nom || se.ville || '',
            description: se.remarque || se.description || '',
            heure: se.heure || null,
            transport: se.transport || trajetObj.mode_transport || 'Voiture',
            photos_keys: []
          };

          if (se.photo) {
            if (se.photo instanceof FileList || Array.isArray(se.photo)) {
              const fileList = Array.from(se.photo);
              fileList.forEach((file, idxFile) => {
                const key = `file_s_${iSeg}_${iSe}_${idxFile}`;
                filesToUpload[key] = file;
                sousObj.photos_keys.push(key);
              });
            } else if (se.photo instanceof File) {
              const key = `file_s_${iSeg}_${iSe}_0`;
              filesToUpload[key] = se.photo;
              sousObj.photos_keys.push(key);
            }
          } else {
            const selector = `#subEtapesContainer .subEtape:nth-child(${iSe + 1})`;
            const subDiv = document.querySelector(selector);
            if (subDiv) {
              const inputFile = subDiv.querySelector('input[type="file"]');
              if (inputFile && inputFile.files && inputFile.files.length > 0) {
                const fileList = Array.from(inputFile.files);
                fileList.forEach((file, idxFile) => {
                  const key = `file_s_${iSeg}_${iSe}_${idxFile}`;
                  filesToUpload[key] = file;
                  sousObj.photos_keys.push(key);
                });
              }
            }
          }
          trajetObj.sous_etapes.push ? trajetObj.sous_etapes.push(sousObj) : trajetObj.sous_etapes = [sousObj];
        });
      }
      payload.trajets.push(trajetObj);
    });

    return { payload, filesToUpload };
  }

  function validateBeforeSave(payload) {
    const errors = [];
    if (!payload.roadtrip.titre || payload.roadtrip.titre.trim().length === 0) {
      errors.push('Le titre du RoadTrip est requis.');
    }
    const etapesCount = collectEtapes().length;
    if (etapesCount < 2) {
      errors.push('Veuillez saisir au moins deux étapes (départ et arrivée).');
    }
    return errors;
  }

  async function sendRoadtripToServer() {
    const { payload, filesToUpload } = buildRoadtripPayload();

    const validationErrors = validateBeforeSave(payload);
    if (validationErrors.length > 0) {
      alert('Erreur :\n' + validationErrors.join('\n'));
      return;
    }

    const formData = new FormData();
    formData.append('roadtrip', JSON.stringify(payload));

    Object.entries(filesToUpload).forEach(([key, file]) => {
      formData.append(key, file, file.name);
    });

    try {
      const resp = await fetch('../formulaire/saveRoadtrip.php', {
        method: 'POST',
        body: formData
      });

      if (!resp.ok) {
        const text = await resp.text();
        throw new Error('Erreur serveur: ' + resp.status + ' — ' + text);
      }

      const json = await resp.json();
      if (json.success) {
        alert('RoadTrip sauvegardé avec succès !');
        console.log('Server response:', json);
      } else {
        alert('Erreur lors de la sauvegarde :\n' + (json.message || 'Erreur inconnue'));
        console.error('Erreur save:', json);
      }
    } catch (err) {
      console.error('Erreur fetch/save:', err);
      alert('Erreur lors de la sauvegarde : ' + err.message);
    }
  }

  document.getElementById('saveRoadtrip').addEventListener('click', (e) => {
    e.preventDefault();
    document.getElementById('saveRoadtrip').disabled = true;
    document.getElementById('saveRoadtrip').textContent = 'Enregistrement...';
    sendRoadtripToServer().finally(() => {
      document.getElementById('saveRoadtrip').disabled = false;
      document.getElementById('saveRoadtrip').textContent = 'Sauvegarder le RoadTrip';
    });
  });


});

/*=======================================
Elements de gestion du formulaire d'inscription et de connexion
=======================================*/

function showLogin() {
    document.getElementById('loginForm').style.display = 'block';
    document.getElementById('registerForm').style.display = 'none';
    document.getElementById('btnLogin').classList.add('active');
    document.getElementById('btnRegister').classList.remove('active');
}

function showRegister() {
    document.getElementById('loginForm').style.display = 'none';
    document.getElementById('registerForm').style.display = 'block';
    document.getElementById('btnLogin').classList.remove('active');
    document.getElementById('btnRegister').classList.add('active');
}

// Fonction à appeler pour afficher ta modale (si tu as un conteneur modale, sinon adapter)
function openModal() {
    // Par exemple, si tu as un div modale, tu peux le passer en display:flex ou block
    const modal = document.querySelector('.formulaire'); // adapte selon ta structure
    if (modal) {
        modal.style.display = 'block';
    }
}

// Appel automatique de la modale au chargement de la page profil
document.addEventListener('DOMContentLoaded', function() {
    openModal();
    // Initialisation sur le formulaire que tu veux afficher par défaut :
    showRegister(); // ou showLogin() selon souhait
});


/*=======================================
          Changement de thème
=======================================*/


const savedTheme = localStorage.getItem("theme");
const toggle = document.getElementById("checkboxSombre");


if (savedTheme === "dark") {
    document.documentElement.classList.add("dark");
}

if (toggle) {
    toggle.checked = savedTheme === "dark";

    toggle.addEventListener("change", () => {
        if (toggle.checked) {
            document.documentElement.classList.add("dark");
            localStorage.setItem("theme", "dark");
        } else {
            document.documentElement.classList.remove("dark");
            localStorage.setItem("theme", "light");
        }
    });
}




