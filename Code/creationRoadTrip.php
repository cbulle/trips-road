<?php
require_once __DIR__ . '/modules/init.php';
include_once __DIR__ . '/bd/lec_bd.php'; // Pour charger les données si mode édition

// Vérification connexion
if (!isset($_SESSION['utilisateur']['id'])) {
    header('Location: /connexion.php');
    exit;
}

$defaultCity = isset($_SESSION['user']['ville']) ? $_SESSION['user']['ville'] : ""; 

// --- MODE ÉDITION : Si un ID est fourni ---
$modeEdition = false;
$roadTripData = null;
$existingTrajets = [];
$existingVilles = [];
$isPublished = false;

if (isset($_GET['id'])) {
    $id_rt = $_GET['id'];
    $id_user = $_SESSION['utilisateur']['id'];

    // Récupérer le RoadTrip
    $stmt = $pdo->prepare("SELECT * FROM roadtrip WHERE id = ? AND id_utilisateur = ?");
    $stmt->execute([$id_rt, $id_user]);
    $roadTripData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($roadTripData) {
      // **VÉRIFIER SI LE ROADTRIP EST DÉJÀ PUBLIÉ**
      if ($roadTripData['statut'] === 'termine') {
        $isPublished = true;
        // Rediriger vers la page de visualisation
        header('Location: /vuRoadTrip.php?id=' . $id_rt);
        exit;
      }
      
      $modeEdition = true;

      // Récupérer Trajets
      $stmtT = $pdo->prepare("SELECT * FROM trajet WHERE road_trip_id = ? ORDER BY numero ASC");
      $stmtT->execute([$id_rt]);
      $rawTrajets = $stmtT->fetchAll(PDO::FETCH_ASSOC);

      // Récupérer Sous-étapes pour chaque trajet
      foreach ($rawTrajets as $t) {
          $stmtS = $pdo->prepare("SELECT * FROM sous_etape WHERE trajet_id = ? ORDER BY numero ASC");
          $stmtS->execute([$t['id']]);
          $sousEtapes = $stmtS->fetchAll(PDO::FETCH_ASSOC);
          
          // Construction de l'objet Trajet complet pour JS
          $trajetComplet = $t;
          
          // S'assurer que la date est au format YYYY-MM-DD pour l'input HTML date
          if(isset($t['date']) && !isset($t['date_trajet'])) {
              $trajetComplet['date_trajet'] = date('Y-m-d', strtotime($t['date']));
          } elseif(isset($t['date_trajet'])) {
              $trajetComplet['date_trajet'] = date('Y-m-d', strtotime($t['date_trajet']));
          }

          $trajetComplet['sousEtapes'] = array_map(function($se) {
              return [
                  'nom' => $se['ville'],
                  'heure' => $se['heure'], // Format HH:MM
                  'remarque' => $se['description'] // HTML TinyMCE
              ];
          }, $sousEtapes);

          $existingTrajets[] = $trajetComplet;
      }
  }
}
?>

<!doctype html>
<html lang="fr">
  <head>
    <meta charset="utf-8">
    <title><?php echo $modeEdition ? "Modifier le RoadTrip" : "Création de RoadTrip"; ?></title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <link rel="stylesheet" href="/css/style.css">
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- TinyMCE Open Source -->
    <script src="/js/tinymce/tinymce.min.js"></script>
    
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.3/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.13.3/jquery-ui.min.js"></script>

    <script>
        const USER_DEFAULT_CITY = "<?php echo htmlspecialchars($defaultCity); ?>";
        // Injection des données PHP vers JS pour l'édition
        const MODE_EDITION = <?php echo $modeEdition ? 'true' : 'false'; ?>;
        const EXISTING_ROADTRIP = <?php echo $modeEdition ? json_encode($roadTripData) : 'null'; ?>;
        const EXISTING_TRAJETS = <?php echo $modeEdition ? json_encode($existingTrajets) : '[]'; ?>;
    </script>

  </head>
  <body>
    <?php include_once __DIR__ . "/modules/header.php" ?>
    
    <h1 class="TitreRT"><?php echo $modeEdition ? "Modifier mon RoadTrip" : "Créer un RoadTrip"; ?></h1>

    <div class="main-container">
      <div class="sidebar">
        
        <div id="legend" style="display: block;">
          <h3>Itinéraire :</h3>
          <ul id="legendList" style="list-style:none; padding:0;"></ul>
          
          <div id="newBlockForm"></div>
        </div>

        <div id="actionsContainer" style="margin-top:10px;">
            <button type="button" id="btnAddSegment" style="width:100%;">+ Ajouter un trajet</button>
        </div>

        <hr>

        <div id="saveContainer">
          <h3>Sauvegarde</h3>
          
          <input type="text" id="roadtripTitle" placeholder="Titre du RoadTrip" 
                 value="<?php echo $modeEdition ? htmlspecialchars($roadTripData['titre']) : ''; ?>"
                 style="width:100%;box-sizing:border-box;margin-bottom:6px;">
          
          <textarea id="roadtripDescription" placeholder="Description (optionnelle)" 
                    style="width:100%;box-sizing:border-box;margin-bottom:6px;"><?php echo $modeEdition ? htmlspecialchars($roadTripData['description']) : ''; ?></textarea>
          
          <div class="status-selector-container">
            <label for="roadtripStatut">État du projet :</label>
            <select id="roadtripStatut">
                <option value="brouillon" <?php echo ($modeEdition && $roadTripData['statut'] == 'brouillon') ? 'selected' : ''; ?>>📝 Brouillon (Non fini)</option>
                <option value="termine" <?php echo ($modeEdition && $roadTripData['statut'] == 'termine') ? 'selected' : ''; ?>>✅ Terminé (Prêt à publier)</option>
            </select>
          </div>

          <label style="font-size:0.9em;">Visibilité (si terminé) :</label>
          <select id="roadtripVisibilite" style="width:100%;box-sizing:border-box;margin-bottom:6px;">
            <option value="prive" <?php echo ($modeEdition && $roadTripData['visibilite'] == 'prive') ? 'selected' : ''; ?>>🔒 Privé (Moi seul)</option>
            <option value="amis" <?php echo ($modeEdition && $roadTripData['visibilite'] == 'amis') ? 'selected' : ''; ?>>👥 Amis</option>
            <option value="public" <?php echo ($modeEdition && $roadTripData['visibilite'] == 'public') ? 'selected' : ''; ?>>🌍 Public</option>
          </select>

          <label>Couverture du Road Trip :</label>
          <?php if($modeEdition && !empty($roadTripData['photo'])): ?>
            <div style="margin-bottom:5px;">
                <img src="/uploads/roadtrips/<?php echo $roadTripData['photo']; ?>" style="width:100px; height:auto; border-radius:5px;">
                <br><small>Image actuelle</small>
            </div>
          <?php endif; ?>
          <input type="file" id="roadtripPhoto" accept="image/*">
          
          <button id="saveRoadtrip" type="button" data-id="<?php echo $modeEdition ? $id_rt : ''; ?>">
              <?php echo $modeEdition ? "Mettre à jour" : "Sauvegarder"; ?>
          </button>
        </div>
      </div>

      <div class="segment-form-container" id="segmentFormContainer" style="display:none;">
          <span id="closeSegmentForm" class="close-segment-btn" title="Fermer">✖</span>
          <h3 id="segmentTitle">Planifier les étapes</h3>
          <div id="subEtapesContainer"></div>
          
          <div class="subEtape-buttons">
            <button id="addSubEtape">+ Ajouter une sous-étape</button>
            <button id="saveSegment">Valider les sous-étapes</button>
          </div>
      </div>

      <div class="map-container">
        <div id="map"></div>
      </div>
    </div>

    <template id="template-legend-item">
      <li class="legend-li">
          <div class="segment-header legend-segment-item">
            <span class="legend-color-indicator"></span>
            
            <div class="transport-options">
                <button type="button" class="transport-btn active" data-mode="Voiture" title="En Voiture">🚗</button>
                <button type="button" class="transport-btn" data-mode="Velo" title="À Vélo">🚲</button>
                <button type="button" class="transport-btn" data-mode="Marche" title="À Pied">🚶</button>
            </div>
            <button type="button" class="settings-btn" title="Options de trajet">⚙️</button>
            <button class="toggleSousEtapes legend-toggle-btn"></button>
          </div>
          
          <div class="legend-date-container">
              <label>Départ le :</label>
              <input type="date" class="legend-date-input" required>
              <label>à :</label>
              <input type="time" class="legend-time-input" value="08:00" required>
          </div>

          <div class="route-preferences" style="display: none;">
              <label class="pref-item">
                  <input type="checkbox" class="pref-checkbox" data-pref="exclude-tolls">
                  <span>Sans péages</span>
              </label>
              <label class="pref-item">
                  <input type="checkbox" class="pref-checkbox" data-pref="exclude-motorways">
                  <span>Sans autoroutes</span>
              </label>
          </div>
          
          <button class="modifierSousEtapes">Ajouter/Modifier Sous-étapes</button>
          <ul class="sousEtapesList" style="display:block;"></ul>
      </li>
    </template>

    <template id="template-sub-etape">
        <div class="subEtape sub-etape-form">
            <input type="text" placeholder="Nom du lieu ou ville" class="subEtapeNom">
            <textarea class="subEtapeRemarque" placeholder="Remarque (facultatif)"></textarea>
            
            <label style="font-size:0.8em; font-weight:bold;">Temps passé sur place (estimation)</label>
            <input type="time" class="subEtapeHeure" required>
            
            <button class="removeSubEtapeBtn sub-etape-remove-btn">✖</button>
        </div>
    </template>

    <div id="imageModal" class="image-modal">
      <img id="imageModalContent" class="image-modal-content" src="" alt="photo en grand">
    </div>

    <?php include_once __DIR__ . "/modules/footer.php" ?>

    <script src="/js/map.js"></script>
  </body>
</html>