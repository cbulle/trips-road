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
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.tiny.cloud/1/3zxeqmyft1tkl3r8uell7b17xff2iqfx6kmw6nsx9rix4ut4/tinymce/8/tinymce.min.js" referrerpolicy="origin" crossorigin="anonymous"></script>
    
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.3/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.13.3/jquery-ui.min.js"></script>

  </head>
  <body>
    <?php     
      include_once __DIR__ . "/modules/header.php"
    ?>
    <h1>Calcul d'itinéraire OpenStreetMap</h1>

    <div class="main-container">
      <div class="sidebar">
        <div id="etapesContainer">
            <input type="text" class="etape" placeholder="Choisir une ville..." style="width: 100%;">
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
          <span id="closeSegmentForm" class="close-segment-btn" title="Fermer">✖</span>
          <h3 id="segmentTitle">Planifier le segment</h3>
          <label>Date du segment : <input type="date" id="segmentDate"></label>
          <div id="subEtapesContainer"></div>
          <div class="subEtape-buttons">
            <button id="addSubEtape">+ Ajouter une sous-étape</button>
            <button id="saveSegment">Enregistrer</button>
          </div>
      </div>

      <div class="map-container">
        <div id="map"></div>
      </div>
    </div>

    <template id="template-etape">
        <div class="etape-row">
            <input type="text" class="etape etape-input" placeholder="Choisir une ville...">
            <button class="btn-remove-etape">✖</button>
        </div>
    </template>

    <template id="template-legend-item">
      <li class="legend-li">
          <div class="segment-header legend-segment-item">
            <span class="legend-color-indicator"></span>
            <button class="toggleSousEtapes legend-toggle-btn"></button>
          </div>
          <button class="modifierSousEtapes">Modifier</button>
          <ul class="sousEtapesList" style="display:none;"></ul>
      </li>
    </template>

    <template id="template-sub-etape">
        <div class="subEtape sub-etape-form">
            <input type="text" placeholder="Nom du lieu ou ville" class="subEtapeNom">
            <textarea class="subEtapeRemarque" placeholder="Remarque (facultatif)"></textarea>
            <input type="time" class="subEtapeHeure">
            <input type="file" class="subEtapePhoto" multiple accept="image/*">
            <button class="removeSubEtapeBtn sub-etape-remove-btn">✖</button>
        </div>
    </template>

    <div id="imageModal" class="image-modal">
      <img id="imageModalContent" class="image-modal-content" src="" alt="photo en grand">
    </div>

    <?php     
      include_once __DIR__ . "/modules/footer.php"
    ?>

    <script src="js/map.js"></script>
  </body>
</html>