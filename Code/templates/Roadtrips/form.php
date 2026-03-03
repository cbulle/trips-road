<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Roadtrip $roadtrip
 * @var bool $modeEdition
 * @var array $existingTrajets
 * @var string $userDefaultCity
 */

$this->assign('title', $modeEdition ? 'Modifier le RoadTrip' : 'Création de RoadTrip');
$this->assign('mainClass', '');
?>

<script>
    const USER_DEFAULT_CITY = "<?= h($userDefaultCity) ?>";
    const MODE_EDITION = <?= json_encode($modeEdition) ?>;
    const URL_GET_FAVORIS = "<?= $this->Url->build(['controller' => 'Roadtrips', 'action' => 'getLieuxFavoris']) ?>";
    const UPLOAD_IMAGE_URL = "<?= $this->Url->build(['controller' => 'Roadtrips', 'action' => 'uploadStepImage']) ?>";

    const EXISTING_ROADTRIP = <?= json_encode([
        'id' => $roadtrip->id,
        'titre' => $roadtrip->title,
        'description' => $roadtrip->description,
        'statut' => $roadtrip->status ?? 'brouillon',
        'visibilite' => $roadtrip->visibility,
        'photo' => $roadtrip->photo_url
    ]) ?>;

    const EXISTING_TRAJETS = <?= json_encode($existingTrajets) ?>;

    const SAVE_URL = "<?= $this->Url->build(['action' => $modeEdition ? 'edit' : 'add', $modeEdition ? $roadtrip->id : null]) ?>";
    const CSRF_TOKEN = "<?= $this->request->getAttribute('csrfToken') ?>";
</script>

<h1 class="TitreRT"><?= $modeEdition ? "Modifier mon RoadTrip" : "Créer un RoadTrip" ?></h1>

<div class="main-container">
    <div class="sidebar">

        <div id="aiAssistantContainer" class="ai-container">
            <h3 class="ai-header">✨ Assistant IA</h3>
            <p class="ai-desc">Laissez l'IA pré-remplir votre voyage !</p>

            <input type="text" id="aiDepart" class="ai-input" placeholder="Départ (ex: Paris)">
            <input type="text" id="aiDestination" class="ai-input" placeholder="Destination (ex: Rome)">

            <div class="ai-row">
                <input type="text" id="aiDuree" class="ai-input" style="width: 50%;" placeholder="Durée (ex: 7j)">
                <input type="text" id="aiTheme" class="ai-input" style="width: 50%;" placeholder="Thème (ex: Nature)">
            </div>

            <button type="button" id="btnGenerateAI" class="ai-btn">
                🚀 Générer des idées
            </button>

            <div id="aiLoading">⏳ L'IA réfléchit...</div>
        </div>

        <div id="aiResultBox" class="ai-result-box">
            <h4 style="margin-top:0; color: var(--bleu_fonce); font-size:1em;">📍 Suggestions d'étapes :</h4>
            <div id="aiResultContent" class="ai-result-content"></div>

            <div style="background: var(--white); color: var(--bleu_fonce_ecriture); padding: 8px; border-radius: 4px; font-size: 0.8em; margin-bottom: 10px; border: 1px solid var(--bleu_clair);">
                💡 <strong>Conseil :</strong> Utilisez le bouton <em>"+ Ajouter un trajet"</em> ci-dessous pour créer ces étapes sur la carte.
            </div>

            <button type="button" class="ai-close-btn" onclick="document.getElementById('aiResultBox').style.display='none'">Fermer</button>
        </div>
        <div class="region-selector-container" style="margin-bottom: 15px; background: #f9f9f9; padding: 10px; border-radius: 8px; border: 1px solid #ddd;">
            <label for="regionSelect">🌍 Zone de recherche :</label>
            <select id="regionSelect">
                <option value="europe">Europe</option>
                <option value="north_america">Amérique du Nord (USA, Canada, Mexique)</option>
            </select>
            <small style="color: #666; font-size: 0.8em; margin-top: 5px; display: block;">Centre la carte et filtre les villes suggérées.</small>
        </div>
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
                   value="<?= h($roadtrip->title) ?>"
                   style="width:100%;box-sizing:border-box;margin-bottom:6px;">

            <textarea id="roadtripDescription" placeholder="Description (optionnelle)"
                      style="width:100%;box-sizing:border-box;margin-bottom:6px;"><?= h($roadtrip->description) ?></textarea>

            <div class="status-selector-container">
                <label for="roadtripStatut" style="font-weight:bold;">Avancement du projet :</label>
                <select id="roadtripStatut" style="width:100%; margin-bottom:10px;">
                    <option
                        value="draft" <?= (isset($roadtrip->status) && $roadtrip->status == 'brouillon') ? 'selected' : '' ?>>
                        📝 En cours de création (Brouillon)
                    </option>
                    <option
                        value="completed" <?= (isset($roadtrip->status) && $roadtrip->status == 'termine') ? 'selected' : '' ?>>
                        ✅ Projet terminé
                    </option>
                </select>
            </div>

            <label for="roadtripVisibilite" style="font-weight:bold;">Qui peut voir ce RoadTrip ?</label>
            <select id="roadtripVisibilite" style="width:100%;box-sizing:border-box;margin-bottom:6px;">
                <option value="private" <?= ($roadtrip->visibility == 'prive') ? 'selected' : '' ?>>🔒 Privé (Moi seul)
                </option>
                <option value="friends" <?= ($roadtrip->visibility == 'amis') ? 'selected' : '' ?>>👥 Amis</option>
                <option value="public" <?= ($roadtrip->visibility == 'public') ? 'selected' : '' ?>>🌍 Public (Tout le
                    monde)
                </option>
            </select>
            <small style="display:block; margin-bottom:10px; color:#666;">
                * Vous pouvez partager un brouillon en mode "Amis" ou "Public".
            </small>

            <label>Couverture du Road Trip :</label>
            <?php if ($modeEdition && !empty($roadtrip->photo_url)): ?>
                <div style="margin-bottom:5px;">
                    <?= $this->Html->image('roadtrips/' . $roadtrip->photo_url, ['style' => 'width:100px; height:auto; border-radius:5px;']) ?>
                    <br><small>Image actuelle</small>
                </div>
            <?php endif; ?>
            <input type="file" id="roadtripPhoto" accept="image/*">

            <button id="saveRoadtrip" type="button"
                    data-id="<?= $modeEdition ? $roadtrip->id : '' ?>"
                    data-url="<?= $this->Url->build(['action' => $modeEdition ? 'edit' : 'add', $modeEdition ? $roadtrip->id : null]) ?>">
                <?= $modeEdition ? "Mettre à jour" : "Sauvegarder" ?>
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
            <button class="toggleSousEtapes legend-toggle-btn">▼</button>

            <button type="button" class="remove-segment-btn" title="Supprimer ce trajet">✖</button>
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

        <div class="subEtapeEditorContainer"></div>

        <label style="font-size:0.8em; font-weight:bold;">Temps passé sur place (estimation)</label>
        <input type="time" class="subEtapeHeure" required>

        <button class="removeSubEtapeBtn sub-etape-remove-btn">✖</button>
    </div>
</template>

<div id="imageModal" class="image-modal">
    <img id="imageModalContent" class="image-modal-content" src="" alt="photo en grand">
</div>
