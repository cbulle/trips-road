
//<?php
//require_once __DIR__ . '/modules/init.php' ;
//if (!isset($_SESSION['user_id'])) {
    //header('Location: connexion.php');
   // exit();
//}
//?>
*/
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Créer un Road Trip</title>
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
  <link rel= "stylesheet" href= "css/style.css">
  <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
  <style>
    #map { height: 600px; margin: 20px 0; border-radius: 10px; }
    body { font-family: Arial, sans-serif; margin: 30px; background: #f4f4f9; }
    input, textarea, button { margin: 10px 0; padding: 8px; width: 100%; border-radius: 5px; border: 1px solid #ccc; }
    button { background-color: #007BFF; color: white; cursor: pointer; }
    button:hover { background-color: #0056b3; }
  </style>
</head>
<body>

<h2>Créer un nouveau Road Trip</h2>

<form id="roadtripForm">
  <label>Titre :</label>
  <input type="text" id="titre" name="titre" required>

  <label>Description :</label>
  <textarea id="description" name="description"></textarea>

  <label>Visibilité :</label>
  <select id="visibilite" name="visibilite">
    <option value="public">Public</option>
    <option value="amis">Amis uniquement</option>
  </select>

  <div id="map"></div>

  <button type="button" id="saveBtn">Enregistrer le Road Trip</button>
</form>

<script>
// Initialisation de la carte
const map = L.map('map').setView([46.5, 2.5], 6);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
  attribution: '© OpenStreetMap contributors'
}).addTo(map);

let etapes = [];

map.on('click', function(e) {
  const { lat, lng } = e.latlng;
  const marker = L.marker([lat, lng]).addTo(map)
    .bindPopup("Étape " + (etapes.length + 1)).openPopup();
  etapes.push({ lat, lng });
});

// Envoi du formulaire en AJAX vers le backend
document.getElementById('saveBtn').addEventListener('click', () => {
  const titre = document.getElementById('titre').value.trim();
  const description = document.getElementById('description').value.trim();
  const visibilite = document.getElementById('visibilite').value;

  if (!titre || etapes.length === 0) {
    alert("Veuillez ajouter un titre et au moins une étape !");
    return;
  }
// Si au moins 2 points existent, on trace une route
if (etapes.length > 1) {
  const coords = etapes.map(e => `${e.lng},${e.lat}`).join(';');
  fetch(`https://router.project-osrm.org/route/v1/driving/${coords}?overview=full&geometries=geojson`)
    .then(r => r.json())
    .then(data => {
      const route = data.routes[0].geometry;
      L.geoJSON(route, { color: 'blue' }).addTo(map);
    })
    .catch(() => alert("Impossible de calculer l’itinéraire"));
}

  fetch('saveRoad.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({ titre, description, visibilite, etapes })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      alert("Road Trip enregistré avec succès !");
      window.location.href = "profil.php";
    } else {
      alert("Erreur : " + data.message);
    }
  });
});
</script>

</body>
</html>
