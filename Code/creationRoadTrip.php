<?php
require_once __DIR__ . '/modules/init.php';

?>

<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Calcul d'itinéraire OpenStreetMap</title>
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
  <link rel="stylesheet" href="/css/style.css">
  
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/fr.js"></script>
  </head>
<body>
  <?php     
    include_once __DIR__ . "/modules/header.php"
  ?>
  <h1>Calcul d'itinéraire OpenStreetMap</h1>

  <div class="main-container">
    <div class="sidebar">
      <div id="etapesContainer">
      <select class="etape" style="width: 100%;"></select>
      </div>
      
      <button type="button" id="addEtape">+ Ajouter une étape</button>
      <button type="button" id="btnCalculer">Calculer</button>
      <button type="button" id="btnModifier" style="display: none;">Modifier l'itinéraire</button>
      <button type="button" id="btnLegende" style="display: none;">Afficher la légende</button>
      <button type="button" id="btnRecalculer" style="display: none;">Recalculer</button>

      <div id="legend" style="display: none;">
        <h3>Légende des étapes :</h3>
        <ul id="legendList" style="list-style:none; padding:0;"></ul>
      </div>
      <div id="saveContainer">
        <input type="text" id="roadtripTitle" placeholder="Titre du RoadTrip" style="width:100%;box-sizing:border-box;margin-bottom:6px;">
        <textarea id="roadtripDescription" placeholder="Description (optionnelle)" style="width:100%;box-sizing:border-box;margin-bottom:6px;"></textarea>
        <select id="roadtripVisibilite" style="width:100%;box-sizing:border-box;margin-bottom:6px;">
          <option value="public">Public</option>
          <option value="amis">Amis</option>
          <option value="prive">Privé</option>
        </select>
        <label>Couverture du Road Trip (Optionnelle) :</label>
        <input type="file" id="roadtripPhoto" accept="image/*">
        <button id="saveRoadtrip" type="button">Sauvegarder le RoadTrip</button>
      </div>
    </div>

    <div class="segment-form-container" id="segmentFormContainer" style="display:none;">
        <h3 id="segmentTitle">Planifier le segment</h3>
        <label>Date du segment : <input type="date" id="segmentDate"></label>
        <div id="subEtapesContainer"></div>
        <div class="subEtape-buttons">
          <button id="addSubEtape">+ Ajouter une sous-étape</button>
          <button id="saveSegment">Enregistrer</button>
        </div>
    </div>



    <!-- Carte à droite -->
    <div class="map-container">
      <div id="map"></div>
    </div>
  </div>

  <script src="js/map.js"></script>
  <div id="imageModal" class="image-modal">
    <img id="imageModalContent" class="image-modal-content" src="" alt="photo en grand">
  </div>
  <?php     
    include_once __DIR__ . "/modules/footer.php"
  ?>
</body>
</html>
