<?php
$this->assign('mainClass', '');
?>

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
            <h3>Sauvegarde & Paramètres</h3>

            <input type="text" id="roadtripTitle" placeholder="Titre du RoadTrip"
                   value="<?php //echo $modeEdition ? htmlspecialchars($roadTripData['titre']) : ''; ?>"
                   style="width:100%;box-sizing:border-box;margin-bottom:6px;">

            <textarea id="roadtripDescription" placeholder="Description (optionnelle)"
                      style="width:100%;box-sizing:border-box;margin-bottom:6px;"><?php //echo $modeEdition ? htmlspecialchars($roadTripData['description']) : ''; ?></textarea>

            <div class="status-selector-container">
                <label for="roadtripStatut" style="font-weight:bold;">Avancement du projet :</label>
                <select id="roadtripStatut" style="width:100%; margin-bottom:10px;">
                    <option value="brouillon" <?php// echo ($modeEdition && $roadTripData['statut'] == 'brouillon') ? 'selected' : ''; ?>>📝 En cours de création (Brouillon)</option>
                    <option value="termine" <?php //echo ($modeEdition && $roadTripData['statut'] == 'termine') ? 'selected' : ''; ?>>✅ Projet terminé</option>
                </select>
            </div>

            <label for="roadtripVisibilite" style="font-weight:bold;">Qui peut voir ce RoadTrip ?</label>
            <select id="roadtripVisibilite" style="width:100%;box-sizing:border-box;margin-bottom:6px;">
                <option value="prive" <?php //echo ($modeEdition && $roadTripData['visibilite'] == 'prive') ? 'selected' : ''; ?>>🔒 Privé (Moi seul)</option>
                <option value="amis" <?php //echo ($modeEdition && $roadTripData['visibilite'] == 'amis') ? 'selected' : ''; ?>>👥 Amis</option>
                <option value="public" <?php //echo ($modeEdition && $roadTripData['visibilite'] == 'public') ? 'selected' : ''; ?>>🌍 Public (Tout le monde)</option>
            </select>
            <small style="display:block; margin-bottom:10px; color:#666;">
                * Vous pouvez partager un brouillon en mode "Amis" ou "Public".
            </small>

            <label>Couverture du Road Trip :</label>
            <?php //if($modeEdition && !empty($roadTripData['photo'])): ?>
            <div style="margin-bottom:5px;">
                <img src="/uploads/roadtrips/<?php //echo $roadTripData['photo']; ?>" style="width:100px; height:auto; border-radius:5px;">
                <br><small>Image actuelle</small>
            </div>
            <?php //endif; ?>
            <input type="file" id="roadtripPhoto" accept="image/*">

            <button id="saveRoadtrip" type="button" data-id="<?php //echo $modeEdition ? $id_rt : ''; ?>">
                <?php //echo $modeEdition ? "Mettre à jour" : "Sauvegarder"; ?>
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
