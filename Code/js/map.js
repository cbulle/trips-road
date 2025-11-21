// --- map.js ---
// Initialisation globale une fois le DOM chargé
document.addEventListener('DOMContentLoaded', () => {

  // --- Initialisation de la carte ---
  let map = L.map('map').setView([46.5, 2.5], 6);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '© OpenStreetMap'
  }).addTo(map);

  let markerDepart = null;
  let markerArrivee = null;
  let segments = [];
  let sousEtapesMarkers = [];

  const strategies = {
    Voiture: { profile: 'driving' },
    Velo: { profile: 'bike' },
    Marche: { profile: 'foot' }
  };

  const segmentColors = ['blue', 'green', 'orange', 'red', 'purple', 'brown', 'pink'];
  const europeViewbox = [-25.0, 35.0, 30.0, 71.0]; //Defini la zone possible de recherche (Islande à Europe de l'est)


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

  // --- Ajout dynamique d'étapes ---
  const etapesContainer = document.getElementById('etapesContainer');
  document.getElementById('addEtape').addEventListener('click', () => {

    const input = document.createElement('input');
    input.type = 'text';
    input.placeholder = 'Étape supplémentaire';
    input.classList.add('etape');
    
    etapesContainer.appendChild(input);

    if(document.getElementById('btnCalculer').style.display === 'none'){
      document.getElementById('btnRecalculer').style.display = 'inline-block';
      document.getElementById('btnLegende').style.display = 'none';
      console.log("Entre dans le if de addEtape");
    }
  });


  // --- Calcul de l'itinéraire principal ---
  async function calculItineraire() {
    // Masquer les éléments de création
    document.getElementById('etapesContainer').style.display = 'none';
    document.getElementById('addEtape').style.display = 'none';
    document.getElementById('btnCalculer').style.display = 'none';

    // Afficher la légende
    document.getElementById('legend').style.display = 'block';

    // Afficher le bouton pour revenir en arrière
    document.getElementById('btnModifier').style.display = 'inline-block';

    // Récupérer toutes les villes dans le conteneur
    const villes = Array.from(document.querySelectorAll('#etapesContainer input'))
      .map(input => input.value.trim())
      .filter(ville => ville.length > 0); // Filtrer les champs vides

    // Vérifier si nous avons au moins deux villes (départ et arrivée)
    if (villes.length < 2) {
      alert("Veuillez renseigner au moins deux villes (départ et arrivée).");
      return;
    }

    const mode = 'Voiture'; // Valeur par défaut
    const strategy = strategies[mode] || strategies['Voiture'];

    // Récupérer les coordonnées des villes
    const coordsVilles = [];
    for (const ville of villes) {
      const coords = await getCoordonnees(ville);
      if (!coords) {
        alert(`Ville introuvable ou hors Europe : ${ville}`);
        return;
      }
      coordsVilles.push(coords);
    }

    // Supprimer les anciens segments, légende et marqueurs
    segments.forEach(s => map.removeLayer(s.line));
    segments = [];
    document.getElementById('legendList').innerHTML = '';
    sousEtapesMarkers.forEach(m => map.removeLayer(m));
    sousEtapesMarkers = [];

    // Marqueurs de départ et d'arrivée
    if (markerDepart) map.removeLayer(markerDepart);
    if (markerArrivee) map.removeLayer(markerArrivee);
    markerDepart = L.marker(coordsVilles[0]).addTo(map).bindPopup(`Départ : ${villes[0]}`).openPopup();
    markerArrivee = L.marker(coordsVilles[coordsVilles.length - 1]).addTo(map).bindPopup(`Arrivée : ${villes[villes.length - 1]}`);

    // Marqueurs pour chaque étape intermédiaire
    for (let i = 1; i < coordsVilles.length - 1; i++) {
      L.marker(coordsVilles[i]).addTo(map).bindPopup(`Étape : ${villes[i]}`);
    }

    // Construction des segments entre les villes
    for (let i = 0; i < coordsVilles.length - 1; i++) {
      const start = coordsVilles[i];
      const end = coordsVilles[i + 1];
      const color = segmentColors[i % segmentColors.length];
      const coordString = `${start[1]},${start[0]};${end[1]},${end[0]}`;
      const url = `https://router.project-osrm.org/route/v1/${strategy.profile}/${coordString}?overview=full&geometries=geojson`;

      try {
        const resp = await fetch(url);
        const data = await resp.json();
        if (data.code !== 'Ok') continue;

        const route = data.routes[0];
        const line = L.geoJSON(route.geometry, { color: color, weight: 5, opacity: 0.8 }).addTo(map);

        segments.push({
          line,
          startCoord: start,
          endCoord: end,
          startName: villes[i],
          endName: villes[i + 1],
          distance: (route.distance / 1000).toFixed(1),
          duration: Math.floor(route.duration / 60),
          sousEtapes: [] // On initialise avec un tableau vide, cela pourra être rempli plus tard
        });

        // Légende avec triangle inversé, numéro d'étape, et bouton modifier
        const li = document.createElement('li');
        li.innerHTML = `
          <div class="segment-header" style="display:flex;align-items:center;gap:5px;cursor:pointer;"> 
          <span style="display:inline-block;width:15px;height:15px;background:${color};border-radius:3px;"></span> 
          <button class="toggleSousEtapes" data-index="${i}" style="margin-left:auto;">${villes[i]} → ${villes[i + 1]}</button> </div>
          <button class="modifierSousEtapes" data-index="${i}">Modifier</button>
          <ul class="sousEtapesList" data-index="${i}" style="display:none;list-style:none;padding-left:20px;"></ul>
        `;
        li.dataset.index = i;
        document.getElementById('legendList').appendChild(li);

      } catch (e) {
        console.error("Erreur segment :", e);
      }
    }


    // Ajuster la vue de la carte pour afficher toutes les villes
    map.fitBounds(coordsVilles.map(c => [c[0], c[1]]));
  }

  // Sauvegarder les étapes existantes avant de cacher
function saveEtapes() {
  const villes = Array.from(document.querySelectorAll('#etapesContainer input'))
    .map(input => input.value.trim())
    .filter(ville => ville.length > 0);
  return villes;
}


// Fonction pour revenir en arrière et permettre l'ajout d'étapes
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

  // Réinitialiser la carte et les segments
  //segments.forEach(s => map.removeLayer(s.line));
  //segments = [];
  //sousEtapesMarkers.forEach(m => map.removeLayer(m));
  //sousEtapesMarkers = [];
  //markerDepart && map.removeLayer(markerDepart);
  //markerArrivee && map.removeLayer(markerArrivee);
  // Sauvegarder les étapes existantes avant de tout cacher
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

async function recalculerDerniersSegmentsMultiples() {
  const inputs = Array.from(document.querySelectorAll('#etapesContainer input.etape'))
    .map(i => i.value.trim())
    .filter(v => v.length > 0);

  if (inputs.length < 2) {
    alert("Veuillez saisir au moins deux villes.");
    return;
  }

  // --- Construire la séquence de villes déjà connues à partir de `segments` ---
  let existingSequence = [];
  if (segments.length > 0) {
    existingSequence.push(segments[0].startName);
    for (const s of segments) existingSequence.push(s.endName);
  }

  // Si on n'a aucun segment existant -> on crée tous les segments entre inputs
  if (existingSequence.length === 0) {
    // fallback : construire segments séquentiels pour tous les inputs
    // (équivalent à calculer tout mais sans écraser l'état existant)
    let startIdx = 0;
    try {
      for (let i = 0; i < inputs.length - 1; i++) {
        const startName = inputs[i];
        const endName = inputs[i + 1];
        // calcule et ajoute le segment startName -> endName
        await _ajouterSegmentEntre(startName, endName);
      }
    } catch (err) {
      console.error(err);
      alert("Erreur lors de la création des segments initiaux.");
    }
    // Ajuster la vue finale (tous les coords des segments)
    const allCoords = segments.flatMap(s => [s.startCoord, s.endCoord]);
    if (allCoords.length) map.fitBounds(allCoords);
    return;
  }

  // --- Trouver la dernière ville connue dans les inputs ---
  const lastKnownCity = existingSequence[existingSequence.length - 1];
  const lastKnownIndexInInputs = inputs.lastIndexOf(lastKnownCity);

  if (lastKnownIndexInInputs === -1) {
    // On n'a pas retrouvé la dernière ville connue : fallback sûr -> recalcul global
    console.warn("Dernière ville connue introuvable dans les inputs. Recalcul global.");
    await calculItineraire();
    return;
  }

  // Si aucune nouvelle ville n'a été ajoutée
  if (lastKnownIndexInInputs === inputs.length - 1) {
    alert("Aucune nouvelle ville à ajouter.");
    return;
  }

  // --- Liste des nouvelles villes à ajouter, dans l'ordre ---
  const nouvelles = inputs.slice(lastKnownIndexInInputs + 1);
  let depart = lastKnownCity;

  try {
    for (const villeArr of nouvelles) {
      await _ajouterSegmentEntre(depart, villeArr);
      depart = villeArr; // avancer la tête
    }

    // Mettre à jour markerArrivee pour la toute dernière ville
    const derniereSeg = segments[segments.length - 1];
    if (markerArrivee) map.removeLayer(markerArrivee);
    markerArrivee = L.marker(derniereSeg.endCoord)
      .addTo(map)
      .bindPopup(`Arrivée : ${derniereSeg.endName}`);

    // Ajuster la vue : étendre pour inclure le nouveau(s) segment(s)
    const allCoords = segments.flatMap(s => [s.startCoord, s.endCoord]);
    if (allCoords.length) map.fitBounds(allCoords);

    alert(`${nouvelles.length} segment(s) ajoutés.`);
  } catch (err) {
    console.error("Erreur lors de l'ajout des nouveaux segments :", err);
    alert("Erreur lors de l'ajout des nouveaux segments. Voir console.");
  }
}

async function _ajouterSegmentEntre(startName, endName) {
  const startCoords = await getCoordonnees(startName);
  const endCoords = await getCoordonnees(endName);
  if (!startCoords || !endCoords) {
    throw new Error(`Ville introuvable : ${!startCoords ? startName : endName}`);
  }

  const mode = 'Voiture';
  const strategy = strategies[mode] || strategies['Voiture'];
  const coordString = `${startCoords[1]},${startCoords[0]};${endCoords[1]},${endCoords[0]}`;
  const url = `https://router.project-osrm.org/route/v1/${strategy.profile}/${coordString}?overview=full&geometries=geojson`;

  const resp = await fetch(url);
  const data = await resp.json();
  if (!data || data.code !== 'Ok') {
    throw new Error('Erreur OSRM pour ' + startName + ' → ' + endName);
  }

  const route = data.routes[0];
  const color = segmentColors[segments.length % segmentColors.length];

  // Créer la polyline et l'ajouter
  const line = L.geoJSON(route.geometry, { color: color, weight: 5, opacity: 0.8 }).addTo(map);

  // Créer l'objet segment
  const newSegment = {
    line,
    startCoord: startCoords,
    endCoord: endCoords,
    startName: startName,
    endName: endName,
    distance: (route.distance / 1000).toFixed(1),
    duration: Math.floor(route.duration / 60),
    sousEtapes: []
  };

  // Ajouter au tableau segments
  segments.push(newSegment);

  // Ajouter à la légende (comme dans ton calcul principal)
  const i = segments.length - 1;
  const li = document.createElement('li');
  li.dataset.index = i;
  li.innerHTML = `
    <div class="segment-header" style="display:flex;align-items:center;gap:5px;cursor:pointer;">
      <span style="display:inline-block;width:15px;height:15px;background:${color};border-radius:3px;"></span>
      <button class="toggleSousEtapes" data-index="${i}" style="margin-left:auto;">
        ${startName} → ${endName}
      </button>
    </div>
    <button class="modifierSousEtapes" data-index="${i}">Modifier</button>
    <ul class="sousEtapesList" data-index="${i}" style="display:none;list-style:none;padding-left:20px;"></ul>
  `;
  document.getElementById('legendList').appendChild(li);

  // remplacer le marqueur d'arrivée (on ne supprime pas l'ancien départ)
  if (markerArrivee) map.removeLayer(markerArrivee);
  markerArrivee = L.marker(endCoords).addTo(map).bindPopup(`Arrivée : ${endName}`);
}


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


  // --- Gestion du formulaire de sous-étapes ---
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

  // --- Clic sur un segment de la légende ---
  document.getElementById('legendList').addEventListener('click', e => {
    const li = e.target.closest('li');
    if (!li) return;

    const index = parseInt(li.dataset.index);
    if (isNaN(index)) return;
    currentSegmentIndex = index;

    const seg = segments[index];
    segments.forEach((s, i) => s.line.setStyle({ opacity: i === index ? 1 : 0.2, weight: i === index ? 6 : 4 }));
    map.eachLayer(layer => { if (layer.options && layer.options.tempMarker) map.removeLayer(layer); });

    L.marker(seg.startCoord, { tempMarker: true }).addTo(map).bindPopup(`Départ : ${seg.startName}`).openPopup();
    L.marker(seg.endCoord, { tempMarker: true }).addTo(map).bindPopup(`Arrivée : ${seg.endName}`);
    map.fitBounds([seg.startCoord, seg.endCoord]);

    segmentFormContainer.style.display = 'block';
    document.getElementById('segmentTitle').textContent = `Planifier le segment : ${seg.startName} → ${seg.endName}`;

    subEtapesContainer.innerHTML = '';
    seg.sousEtapes.forEach(se => addSousEtapeForm(se));
    segmentDateInput.value = seg.date || '';
  });

  // Ajouter une sous-étape
  document.getElementById('addSubEtape').addEventListener('click', () => addSousEtapeForm());

  // --- Enregistrer le segment + recalculer ---
  document.getElementById('saveSegment').addEventListener('click', async () => {

    if (currentSegmentIndex === null) return;

    const seg = segments[currentSegmentIndex];
    seg.date = segmentDateInput.value;
    seg.sousEtapes = [];

    const sousEtapeNoms = [];
    document.querySelectorAll('.subEtape').forEach(div => {
      const nom = div.querySelector('.subEtapeNom').value.trim();
      const remarque = div.querySelector('.subEtapeRemarque').value.trim();
      const heure = div.querySelector('.subEtapeHeure').value.trim();
      const photo = div.querySelector('.subEtapePhoto').files[0] || null;

      if (nom) {
        seg.sousEtapes.push({ nom, remarque, heure, photo });
        sousEtapeNoms.push(nom);
      }
    });

    if (sousEtapeNoms.length === 0) {
      alert("Aucune sous-étape renseignée. Enregistrement simple.");
      segmentFormContainer.style.display = 'none';
      return;
    }

    const allPlaces = [seg.startName, ...sousEtapeNoms, seg.endName];
    const coordsList = [];
    for (const place of allPlaces) {
      const coords = await getCoordonnees(place);
      if (!coords) {
        alert(`Sous-étape introuvable : ${place}`);
        return;
      }
      coordsList.push(coords);
    }

    const coordPairs = coordsList.map(c => `${c[1]},${c[0]}`).join(';');
    const mode = 'Voiture'
    const strategy = strategies[mode] || strategies['Voiture'];
    const url = `https://router.project-osrm.org/route/v1/${strategy.profile}/${coordPairs}?overview=full&geometries=geojson`;

    try {
      const resp = await fetch(url);
      const data = await resp.json();
      if (data.code !== 'Ok') {
        alert("Erreur lors du recalcul du trajet.");
        return;
      }

      map.removeLayer(seg.line);
      const route = data.routes[0];
      seg.line = L.geoJSON(route.geometry, { color: 'blue', weight: 5, opacity: 0.8 }).addTo(map);
      seg.distance = (route.distance / 1000).toFixed(1);
      seg.duration = Math.floor(route.duration / 60);

      // --- Ajout des marqueurs pour les sous-étapes ---
      seg.sousEtapes.forEach(se => {
        getCoordonnees(se.nom).then(coords => {
          if (coords) {
            let photoHTML = '';
            if (se.photo) {
              const photoURL = URL.createObjectURL(se.photo);
              photoHTML = `<br><img src="${photoURL}" alt="photo" class="popup-photo">`;
            }

            const marker = L.marker(coords, { tempMarker: true })
              .addTo(map)
              .bindPopup(`
                <div style="max-width:200px;">
                  <b>${se.nom}</b><br>
                  ${se.remarque ? `<em>${se.remarque}</em><br>` : ''}
                  ${se.heure ? `<small>Heure : ${se.heure}</small><br>` : ''}
                  ${photoHTML}
                </div>
              `);
            sousEtapesMarkers.push(marker);
          }
        });
      });

      alert(`Segment "${seg.startName} → ${seg.endName}" recalculé (${seg.distance} km, ${seg.duration} min).`);
      segmentFormContainer.style.display = 'none';
      console.log(seg);
    } catch (err) {
      console.error(err);
      alert("Erreur de recalcul d’itinéraire.");
    }
  });

  // --- Menu déroulant des sous-étapes ---
  document.getElementById('legendList').addEventListener('click', e => {
      if (e.target.classList.contains('toggleSousEtapes')) {
        console.log("Bouton cliqué");
        const index = e.target.dataset.index;
        const ul = document.querySelector(`.sousEtapesList[data-index="${index}"]`);
        if (!ul) return;
        console.log(ul.style.display);
        if(ul.style.display === 'none'){
          console.log("Jentre dans le ul.style.display !== block");
        const seg = segments[index];
        ul.innerHTML = '';
        if (seg.sousEtapes.length > 0) {
          seg.sousEtapes.forEach(se => {
            const li = document.createElement('li');
            let photoHTML = '';
            if (se.photo) {
              const photoURL = URL.createObjectURL(se.photo);
              photoHTML = `<img src="${photoURL}" class="sousetape-photo" alt="photo">`;
            }

            li.innerHTML = `
              <div class="sousetape-item">
                <strong>${se.nom}</strong>${se.heure ? ` <span class="sousetape-heure">(${se.heure})</span>` : ''}<br>
                ${se.remarque ? `<em>${se.remarque}</em><br>` : ''}
                ${photoHTML}
              </div>
            `;
            ul.appendChild(li);
          });
        } else {
          ul.innerHTML = '<li><em>Aucune sous-étape enregistrée</em></li>';
        }

        ul.style.display = 'block';
      }else{
        console.log("Je rentre direcement dedans le none");
        ul.style.display = 'none';
      }
    }
  });

  // --- Affichage du résumé des sous-étapes ou du formulaire Modifier ---
  document.getElementById('legendList').addEventListener('click', e => {
    if (e.target.classList.contains('modifierSousEtapes')) {
      const index = parseInt(e.target.dataset.index);
      const seg = segments[index];

      // Affichage du formulaire pour modifier le segment
      segmentFormContainer.style.display = 'block';
      document.getElementById('segmentTitle').textContent = `Modifier le segment : ${seg.startName} → ${seg.endName}`;
      subEtapesContainer.innerHTML = '';
      seg.sousEtapes.forEach(se => addSousEtapeForm(se));
      segmentDateInput.value = seg.date || '';
    } else if (!e.target.closest('.segment-form-container') && segmentFormContainer.style.display === 'block') {
      // Afficher le résumé des sous-étapes ou un message si aucune sous-étape
      segmentFormContainer.style.display = 'none';
    }
  });

  // --- Lancer le calcul principal ---
  document.getElementById('btnCalculer').addEventListener('click', calculItineraire);
  // --- Affichage des images en grand (lightbox) ---
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
const checkbox = document.getElementById("checkboxSombre");
checkbox.addEventListener("change", () => {
  document.documentElement.classList.toggle("dark", checkbox.checked);
});



