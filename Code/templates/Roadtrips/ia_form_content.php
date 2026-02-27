<?php $this->Html->script('roadtrip-generator', ['block' => true]); ?>

<div id="ai-form-container" class="p-4 card shadow-sm">
    <div class="progress mb-4" style="height: 10px;">
        <div id="progress-bar" class="progress-bar bg-success" style="width: 16%"></div>
    </div>

    <?= $this->Form->create(null, ['id' => 'roadtrip-ai-form']) ?>
    <div class="step" data-step="1">
        <h3>📍 D'où partez-vous et où allez-vous ?</h3>
        <div class="mb-3">
            <label>Ville de départ</label>
            <input type="text" name="depart" class="form-control" placeholder="Ex: Paris" required>
        </div>
        <div class="mb-3">
            <label>Destination finale</label>
            <input type="text" name="destination" class="form-control" placeholder="Ex: Marseille" required>
        </div>
    </div>

    <div class="step d-none" data-step="2">
        <h3>📅 Combien de jours dure le voyage ?</h3>
        <input type="number" name="jours" class="form-control" min="1" max="15" value="5">
    </div>

    <div class="step d-none" data-step="3">
        <h3>⚡ Quel est votre rythme ?</h3>
        <select name="rythme" class="form-select">
            <option value="detente">Détente (Peu de route, plus de repos)</option>
            <option value="equilibre" selected>Équilibré (Moyen)</option>
            <option value="intense">Intense (Beaucoup de visites et de route)</option>
        </select>
    </div>

    <div class="step d-none" data-step="4">
        <h3>🌿 Quel est le thème du voyage ?</h3>
        <select name="theme" class="form-select">
            <option value="nature">Nature & Paysages</option>
            <option value="culture">Culture & Histoire</option>
            <option value="gastronomie">Gastronomie</option>
            <option value="aventure">Aventure</option>
        </select>
    </div>

    <div class="step d-none" data-step="5">
        <h3>💰 Quel est votre budget ?</h3>
        <select name="budget" class="form-select">
            <option value="eco">Économique</option>
            <option value="standard" selected>Standard</option>
            <option value="luxe">Luxe</option>
        </select>
    </div>

    <div class="step d-none" data-step="6">
        <h3>🚗 Moyen de transport ?</h3>
        <select name="mode" class="form-select">
            <option value="Voiture">Voiture</option>
            <option value="Moto">Moto</option>
            <option value="Van">Van / Camping-car</option>
            <option value="Vélo">Vélo</option>
        </select>
    </div>

    <div class="mt-4 d-flex justify-content-between">
        <button type="button" id="prev-btn" class="btn btn-secondary d-none">Précédent</button>
        <button type="button" id="next-btn" class="btn btn-primary">Suivant</button>
        <button type="submit" id="generate-btn" class="btn btn-success d-none">✨ Générer mon Roadtrip</button>
    </div>
    <?= $this->Form->end() ?>
</div>
