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
  });

  // --- Calcul de l'itinéraire principal ---
  async function calculItineraire() {
    const villeDepart = document.getElementById('depart').value.trim();
    const villeArrivee = document.getElementById('arrivee').value.trim();
    const mode = document.getElementById('mode').value;
    const strategy = strategies[mode] || strategies['Voiture'];

    if (!villeDepart || !villeArrivee) {
      alert("Remplis départ et arrivée.");
      return;
    }

    const villesEtapes = Array.from(document.querySelectorAll('.etape'))
      .map(i => i.value.trim())
      .filter(v => v.length > 0);

    const toutesVilles = [villeDepart, villeArrivee, ...villesEtapes];

    const coordsVilles = [];
    for (const ville of toutesVilles) {
      const coords = await getCoordonnees(ville);
      if (!coords) {
        alert(`Ville introuvable ou hors Europe : ${ville}`);
        return;
      }
      coordsVilles.push(coords);
    }

    // Supprimer anciens segments, légende et marqueurs
    segments.forEach(s => map.removeLayer(s.line));
    segments = [];
    document.getElementById('legendList').innerHTML = '';
    sousEtapesMarkers.forEach(m => map.removeLayer(m));
    sousEtapesMarkers = [];

    // Marqueurs de base
    if (markerDepart) map.removeLayer(markerDepart);
    if (markerArrivee) map.removeLayer(markerArrivee);
    markerDepart = L.marker(coordsVilles[0]).addTo(map).bindPopup(`Départ : ${villeDepart}`).openPopup();
    markerArrivee = L.marker(coordsVilles[1]).addTo(map).bindPopup(`Arrivée : ${villeArrivee}`);
    for (let i = 2; i < coordsVilles.length; i++) {
      L.marker(coordsVilles[i]).addTo(map).bindPopup(`Étape : ${toutesVilles[i]}`);
    }

    // Construction des segments
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
          startName: toutesVilles[i],
          endName: toutesVilles[i + 1],
          distance: (route.distance / 1000).toFixed(1),
          duration: Math.floor(route.duration / 60),
          sousEtapes: []
        });

        // --- Légende avec bouton déroulant ---
        const li = document.createElement('li');
        li.innerHTML = `
          <div class="segment-header" style="display:flex;align-items:center;gap:5px;cursor:pointer;">
            <span style="display:inline-block;width:15px;height:15px;background:${color};border-radius:3px;"></span>
            <span>${toutesVilles[i]} → ${toutesVilles[i + 1]}</span>
            <button class="toggleSousEtapes" data-index="${i}" style="margin-left:auto;">▼</button>
          </div>
          <ul class="sousEtapesList" data-index="${i}" style="display:none;list-style:none;padding-left:20px;"></ul>
        `;
        li.dataset.index = i;
        document.getElementById('legendList').appendChild(li);

      } catch (e) {
        console.error("Erreur segment :", e);
      }
    }

    map.fitBounds(coordsVilles.map(c => [c[0], c[1]]));
  }

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
    if (!li || e.target.classList.contains('toggleSousEtapes')) return;

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
    const mode = document.getElementById('mode').value;
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

      // --- Marqueurs sous-étapes avec images ---
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

    } catch (err) {
      console.error(err);
      alert("Erreur de recalcul d’itinéraire.");
    }
  });

  // --- Menu déroulant des sous-étapes ---
  document.getElementById('legendList').addEventListener('click', e => {
    if (e.target.classList.contains('toggleSousEtapes')) {
      const index = e.target.dataset.index;
      const ul = document.querySelector(`.sousEtapesList[data-index="${index}"]`);
      if (!ul) return;

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

      ul.style.display = ul.style.display === 'none' ? 'block' : 'none';
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


});

/*=======================================
Elements de gestion du formulaire d'inscription et de connexion
=======================================*/

document.getElementById('show-register').addEventListener('click', function() {
    document.getElementById('login-form').classList.remove('active');
    document.getElementById('register-form').classList.add('active');
    document.getElementById('login-title').textContent = 'Inscription';
});

document.getElementById('show-login').addEventListener('click', function() {
    document.getElementById('register-form').classList.remove('active');
    document.getElementById('login-form').classList.add('active');
    document.getElementById('register-title').textContent = 'Connexion';
});
