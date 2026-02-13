<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Roadtrip $roadtrip
 * @var string $jsMapDataJson
 * @var bool $isOwner
 */
$this->assign('title', h($roadtrip->title));
$this->assign('mainClass', 'roadtrip-view-page');

$mapDataArray = json_decode($jsMapDataJson, true);

$transportIcons = [
    'voiture' => '🚗', 'velo' => '🚴', 'vélo' => '🚴', 'marche' => '🚶', 'à pied' => '🚶',
    'train' => '🚂', 'bus' => '🚌', 'avion' => '✈️', 'moto' => '🏍️'
];
$getIcon = fn($m) => $transportIcons[strtolower($m ?? '')] ?? '🚗';
?>
<div class="roadtrip-view-container">

    <div class="roadtrip-hero">
        <?php if (isset($isOwner) && $isOwner): ?>
            <?php
            $isPublished = ($roadtrip->status === 'completed' || $roadtrip->status === 'termine');
            $badgeClass = $isPublished ? 'status-termine' : 'status-brouillon';
            $badgeText = $isPublished ? 'Publié' : 'Brouillon';
            ?>
            <div style="margin-bottom:10px;">
                <span class="status-badge <?= $badgeClass ?>"><?= $badgeText ?></span>
            </div>
        <?php endif; ?>

        <h1><?= h($roadtrip->title) ?></h1>

        <div class="roadtrip-meta">
            <div class="author-pill">
                <?php
                $pp = $roadtrip->user->profile_picture ?: 'User.png';
                $ppUrl = $this->Url->webroot('uploads/pp/' . $pp);
                ?>
                <img src="<?= $ppUrl ?>" alt="Auteur">
                <span>Par <strong><?= h($roadtrip->user->username) ?></strong></span>
            </div>
        </div>

        <?php if (!empty($roadtrip->description)): ?>
            <div class="roadtrip-intro-text">
                <?= nl2br(h($roadtrip->description)) ?>
            </div>
        <?php endif; ?>
    </div>

    <div id="map-global" style="width: 100%; height: 450px; display: block;"></div>

    <div class="roadtrip-trips-list">
        <?php foreach ($roadtrip->trips as $trip): ?>

            <div class="trip-block card-vu" id="card-<?= $trip->id ?>">

                <div class="trip-header" onclick="toggleTrajet(<?= $trip->id ?>)">
                    <div class="trip-title-group">
                        <h2><?= h($trip->departure) ?> ➝ <?= h($trip->arrival) ?></h2>
                        <div class="trip-infos">
                            <span>📅 <?= $trip->trip_date ? $trip->trip_date->format('d/m/Y') : '' ?></span>
                            <span><?= $getIcon($trip->transport_mode) ?> <?= ucfirst($trip->transport_mode) ?></span>
                        </div>
                    </div>
                    <div class="trip-toggle-btn toggle-icon">▼</div>
                </div>

                <div class="trip-content sous-etapes-container" id="sous-etapes-<?= $trip->id ?>" style="display:none;">

                    <div class="timeline-wrapper">
                        <?php
                        $steps = [];

                        $steps[] = ['type' => 'dep', 'nom' => $trip->departure, 'heure' => $trip->departure_time];

                        foreach ($trip->sub_steps as $ss) {
                            $pauseVal = '00:00:00';
                            if (!empty($ss->heure)) {
                                if (is_object($ss->heure) && method_exists($ss->heure, 'format')) {
                                    $pauseVal = $ss->heure->format('H:i:s');
                                } else {
                                    $pauseVal = (string)$ss->heure;
                                }
                            }
                            $steps[] = ['type' => 'step', 'nom' => $ss->city, 'heure' => $pauseVal, 'obj' => $ss];
                        }

                        $steps[] = ['type' => 'arr', 'nom' => $trip->arrival];
                        ?>

                        <?php foreach ($steps as $i => $step): ?>

                            <?php
                            $jsClass = 'sous-etape-card';
                            $dataPause = '00:00:00';
                            if ($step['type'] == 'step') {
                                $dataPause = $step['heure'];
                            }

                            $iconType = ($step['type'] == 'dep') ? 'icon-start' : (($step['type'] == 'arr') ? 'icon-end' : 'icon-step');
                            $emoji = ($step['type'] == 'dep') ? '🚀' : (($step['type'] == 'arr') ? '🏁' : '📍');
                            ?>

                            <div class="step-row">
                                <div class="step-icon <?= $iconType ?>"><?= $emoji ?></div>

                                <div class="step-card <?= $jsClass ?>" data-pause="<?= $dataPause ?>">

                                    <div class="step-header">
                                        <h3><?= h($step['nom']) ?></h3>

                                        <?php if ($step['type'] == 'dep'): ?>
                                            <span class="step-time">
                                                Départ : <strong><?= is_object($step['heure']) ? $step['heure']->format('H:i') : substr($step['heure'], 0, 5) ?></strong>
                                            </span>
                                        <?php else: ?>
                                            <span class="horaire-calcule">
                                                <span class="segment-loader" style="font-size:0.8em; color:#999;">Calcul...</span>
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <?php if ($step['type'] == 'step'): ?>
                                        <?php if ($dataPause != '00:00' && $dataPause != '00:00:00'): ?>
                                            <div style="font-size:0.9rem; margin-bottom:10px; color:#666;">
                                                ☕ Pause : <strong><?= substr($dataPause, 0, 5) ?></strong>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (!empty($step['obj']->description)): ?>
                                            <div class="step-desc markdown-to-html"
                                                 data-markdown="<?= htmlspecialchars($step['obj']->description, ENT_QUOTES, 'UTF-8') ?>">
                                                <span
                                                    style="color:#999; font-size:0.85em;">Chargement du texte...</span>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (!empty($step['obj']->sub_step_photos)): ?>
                                            <div class="step-photos">
                                                <?php foreach ($step['obj']->sub_step_photos as $photo): ?>
                                                    <?= $this->Html->image('/uploads/sousetapes/' . $photo->photo, [
                                                        'alt' => 'Photo',
                                                        'onclick' => "window.open(this.src, '_blank')"
                                                    ]) ?>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <?php if ($i < count($steps) - 1): ?>
                                <div class="transport-segment">
                                    <div class="segment-info" data-mode="<?= strtolower($trip->transport_mode) ?>">
                                        <span class="segment-icon"><?= $getIcon($trip->transport_mode) ?></span>
                                        <span class="segment-distance">...</span> •
                                        <span class="segment-time">...</span>
                                    </div>
                                </div>
                            <?php endif; ?>

                        <?php endforeach; ?>
                    </div>

                    <div class="map-sticky-wrapper">
                        <div id="map-trajet-<?= $trip->id ?>" class="map-details"
                             style="width:100%; height:100%;"></div>
                    </div>

                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
    const roadTripData = <?= $jsMapDataJson ?>;
</script>
