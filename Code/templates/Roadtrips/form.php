<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Roadtrip $roadtrip
 * @var bool $isEditMode
 * @var array $existingTrips
 * @var string $userDefaultCity
 */

$this->assign('title', $isEditMode ? 'Modifier le RoadTrip' : 'Création de RoadTrip');
$this->assign('mainClass', '');
?>

<script>
    const USER_DEFAULT_CITY = "<?= h($userDefaultCity) ?>";
    const MODE_EDITION = <?= json_encode($isEditMode) ?>;
    const URL_GET_FAVORIS = "<?= $this->Url->build(['controller' => 'Roadtrips', 'action' => 'getFavoritePlaces']) ?>";
    const UPLOAD_IMAGE_URL = "<?= $this->Url->build(['controller' => 'Roadtrips', 'action' => 'uploadStepImage']) ?>";

    const EXISTING_ROADTRIP = <?= json_encode([
        'id' => $roadtrip->id,
        'titre' => $roadtrip->title,
        'description' => $roadtrip->description,
        'statut' => $roadtrip->status ?? 'draft',
        'visibilite' => $roadtrip->visibility,
        'photo' => $roadtrip->photo_url
    ]) ?>;

    const EXISTING_TRAJETS = <?= json_encode($existingTrips) ?>;

    const SAVE_URL = "<?= $this->Url->build(['action' => $isEditMode ? 'edit' : 'add', $isEditMode ? $roadtrip->id : null]) ?>";
    const CSRF_TOKEN = "<?= $this->request->getAttribute('csrfToken') ?>";
</script>

<h1 class="roadtrip-main-title"><?= $isEditMode ? "Modifier mon RoadTrip" : "Créer un RoadTrip" ?></h1>

<div class="main-container">
    <div class="sidebar">
        <div class="search-container">
            <div class="region-selector-container">
                <label for="regionSelect">🌍 Zone de recherche :</label>
                <small class="help-text-small">Centre la carte et filtre les villes suggérées.</small>
                <select id="regionSelect" class="full-width-input margin-bottom">
                    <option value="europe" <?= (isset($roadtrip->place) && $roadtrip->place === 'europe') ? 'selected' : '' ?>>Europe</option>
                    <option value="america" <?= (isset($roadtrip->place) && $roadtrip->place === 'america') ? 'selected' : '' ?>>Amérique du Nord (USA, Canada, Mexique)</option>
                </select>
            </div>

            <div id="legend" class="block-display">
                <h3>Itinéraire :</h3>
                <ul id="legendList" class="no-bullets padding-zero"></ul>
                <div id="newBlockForm" class="hidden"></div>
            </div>

            <div id="actionsContainer" class="margin-top-small">
                <button type="button" id="btnAddSegment" class="full-width-btn">+ Ajouter un trajet</button>
            </div>

            <hr>

            <button type="button" id="triggerSaveModal" onclick="openRoadtripModal('modalSaveRoadtrip')">
                <i class="material-icons" style="vertical-align: middle;">save</i> <?= $isEditMode ? "Paramètres & Mise à jour" : "Paramètres & Sauvegarde" ?>
            </button>
        </div>
    </div>

    <div class="segment-form-container hidden" id="segmentFormContainer">
        <span id="closeSegmentForm" class="close-segment-btn" title="Fermer">✖</span>
        <h3 id="segmentTitle">Planifier les étapes</h3>
        <div id="subEtapesContainer"></div>

        <div class="subEtape-buttons">
            <button type="button" id="addSubEtape">+ Ajouter une sous-étape</button>
            <button type="button" id="saveSegment">Valider les sous-étapes</button>
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
            <button type="button" class="toggleSousEtapes legend-toggle-btn">▼</button>
            <button type="button" class="remove-segment-btn" title="Supprimer ce trajet">✖</button>
        </div>

        <div class="legend-date-container">
            <label>Départ le :</label>
            <input type="date" class="legend-date-input" required>
            <label>à :</label>
            <input type="time" class="legend-time-input" value="08:00" required>
        </div>

        <div class="route-preferences hidden">
            <label class="pref-item">
                <input type="checkbox" class="pref-checkbox" data-pref="exclude-tolls">
                <span>Sans péages</span>
            </label>
            <label class="pref-item">
                <input type="checkbox" class="pref-checkbox" data-pref="exclude-motorways">
                <span>Sans autoroutes</span>
            </label>
        </div>

        <button type="button" class="modifierSousEtapes">Ajouter/Modifier Sous-étapes</button>
        <ul class="sousEtapesList block-display"></ul>
    </li>
</template>

<template id="template-sub-etape">
    <div class="subEtape sub-etape-form">
        <input type="text" placeholder="Nom du lieu ou ville" class="subEtapeNom">

        <div class="subEtapeEditorContainer"></div>

        <label class="small-bold-label">Temps passé sur place (estimation)</label>
        <input type="time" class="subEtapeHeure" required>

        <button type="button" class="removeSubEtapeBtn sub-etape-remove-btn">✖</button>
    </div>
</template>

<div id="imageModal" class="image-modal">
    <?= $this->Html->image('', ['id' => 'imageModalContent', 'class' => 'image-modal-content', 'alt' => 'Photo agrandie']) ?>
</div>

<div id="modalSaveRoadtrip" class="custom-modal">
    <div class="modal-content" style="max-width: 500px; padding: 0; background: transparent; box-shadow: none;">

        <div class="sidebar" style="width: 100%; max-width: 100%;">
            <div id="saveContainer" style="margin: 0; border: 2px solid var(--bleu_clair); max-height: 85vh; overflow-y: auto; scrollbar-width: thin;">

                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid rgba(255,255,255,0.2); padding-bottom: 10px;">
                    <h3 style="margin: 0; border: none; padding: 0;">Sauvegarde & Paramètres</h3>
                    <button type="button" onclick="closeRoadtripModal('modalSaveRoadtrip')" style="background: transparent; border: none; color: var(--beige); font-size: 28px; cursor: pointer; line-height: 1;">&times;</button>
                </div>

                <?= $this->Form->create($roadtrip, ['type' => 'file', 'id' => 'roadtripForm']) ?>

                <?= $this->Form->control('title', [
                    'id' => 'roadtripTitle',
                    'placeholder' => 'Titre du RoadTrip',
                    'label' => false,
                    'class' => 'full-width-input margin-bottom'
                ]) ?>

                <?= $this->Form->control('description', [
                    'id' => 'roadtripDescription',
                    'type' => 'textarea',
                    'placeholder' => 'Description (optionnelle)',
                    'label' => false,
                    'class' => 'full-width-input margin-bottom'
                ]) ?>

                <div class="status-selector-container margin-bottom">
                    <?= $this->Form->control('status', [
                        'id' => 'roadtripStatut',
                        'type' => 'select',
                        'label' => ['text' => 'Avancement du projet :', 'class' => 'bold-label'],
                        'class' => 'full-width-input',
                        'options' => [
                            'draft' => '📝 En cours de création (Brouillon)',
                            'completed' => '✅ Projet terminé'
                        ],
                        'default' => $roadtrip->status ?? 'draft'
                    ]) ?>
                </div>

                <div class="visibility-selector-container margin-bottom">
                    <?= $this->Form->control('visibility', [
                        'id' => 'roadtripVisibilite',
                        'type' => 'select',
                        'label' => ['text' => 'Qui peut voir ce RoadTrip ?', 'class' => 'bold-label'],
                        'class' => 'full-width-input',
                        'options' => [
                            'private' => '🔒 Privé (Moi seul)',
                            'friends' => '👥 Amis',
                            'public' => '🌍 Public (Tout le monde)'
                        ],
                        'default' => $roadtrip->visibility ?? 'private'
                    ]) ?>
                    <small class="help-text">
                        * Vous pouvez partager un brouillon en mode "Amis" ou "Public".
                    </small>
                </div>

                <div class="image-upload-container margin-bottom">
                    <label class="bold-label">Couverture du Road Trip :</label>
                    <?php if ($isEditMode && !empty($roadtrip->photo_url)): ?>
                        <div class="current-image-wrapper">
                            <?= $this->Html->image('roadtrips/' . $roadtrip->photo_url, ['class' => 'preview-thumbnail', 'alt' => 'Couverture actuelle']) ?>
                            <br><small>Image actuelle</small>
                        </div>
                    <?php endif; ?>

                    <?= $this->Form->control('photo_cover', [
                        'type' => 'file',
                        'id' => 'roadtripPhoto',
                        'accept' => 'image/*',
                        'label' => false
                    ]) ?>
                </div>

                <?= $this->Form->hidden('trajets', ['id' => 'trajetsJsonData']) ?>

                <?= $this->Form->button($isEditMode ? "Confirmer la mise à jour" : "Confirmer la sauvegarde", [
                    'type' => 'button',
                    'id' => 'saveRoadtrip',
                    'class' => 'submit-btn full-width-btn margin-top-small'
                ]) ?>

                <?= $this->Form->end() ?>
            </div>
        </div>
    </div>
</div>
