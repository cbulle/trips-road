<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Calcul d'itinéraire OpenStreetMap</title>
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
  <link rel="stylesheet" href="/css/style.css">
</head>
<body>
  <?php     
    include_once __DIR__ . "/modules/header.php"
  ?>
  <h1>Calcul d'itinéraire OpenStreetMap</h1>

  <div class="main-container">
    <!-- Sidebar gauche -->
    <div class="sidebar">
      <div id="etapesContainer">
        <input type="text" class="etape" placeholder="Choisir une ville">
      </div>
      
      <button type="button" id="addEtape">+ Ajouter une étape</button>
      <button type="button" id="btnCalculer">Calculer</button>
      <button type="button" id="btnModifier" style="display: none;">Modifier l'itinéraire</button>

      <div id="legend" style="display: none;">
        <h3>Légende des étapes :</h3>
        <ul id="legendList" style="list-style:none; padding:0;"></ul>
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
