<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Roadtrip $roadtrip
 * @var string $jsMapDataJson
 * @var bool $isOwner
 */
$this->assign('title', h($roadtrip->title));
$this->assign('mainClass', 'roadtrip-view-page');

$transportIcons = [
    'voiture' => '🚗', 'velo' => '🚴', 'vélo' => '🚴', 'marche' => '🚶', 'à pied' => '🚶',
    'train' => '🚂', 'bus' => '🚌', 'avion' => '✈️', 'moto' => '🏍️'
];
$getTransportIcon = fn($mode) => $transportIcons[strtolower($mode ?? '')] ?? '🚗';
?>
<div class="roadtrip-view-container">

    <div class="roadtrip-hero">
        <?php if (isset($isOwner) && $isOwner): ?>
            <?php
            $badgeClass = $roadtrip->is_completed ? 'status-termine' : 'status-brouillon';
            $badgeText = $roadtrip->is_completed ? 'Publié' : 'Brouillon';
            ?>
            <div class="mb-10">
                <span class="status-badge <?= $badgeClass ?>"><?= $badgeText ?></span>
            </div>
        <?php endif; ?>

        <h1><?= h($roadtrip->title) ?></h1>

        <div class="roadtrip-meta">
            <div class="author-pill">
                <?php
                $authorProfilePic = $roadtrip->user->profile_picture ?: 'User.png';
                ?>
                <?= $this->Html->image('/uploads/pp/' . $authorProfilePic, ['alt' => 'Auteur']) ?>
                <span>Par <strong><?= h($roadtrip->user->username) ?></strong></span>
            </div>
        </div>

        <?php if (!empty($roadtrip->description)): ?>
            <div class="roadtrip-intro-text">
                <?= nl2br(h($roadtrip->description)) ?>
            </div>
        <?php endif; ?>
    </div>

    <div id="map-global" class="map-global-large"></div>

    <div class="roadtrip-trips-list">
        <?php foreach ($roadtrip->trips as $trip): ?>

            <div class="trip-block card-vu" id="card-<?= $trip->id ?>">
                <div class="trip-header js-trip-toggle" data-trip-id="<?= $trip->id ?>">
                    <div class="trip-title-group">
                        <h2><?= h($trip->departure) ?> ➝ <?= h($trip->arrival) ?></h2>
                        <div class="trip-infos">
                            <span>📅 <?= !empty($trip->date) ? (is_object($trip->date) ? $trip->date->format('d/m/Y') : h($trip->date)) : '' ?></span>

                            <span><?= $getTransportIcon($trip->transport_mode) ?> <?= ucfirst((string)$trip->transport_mode) ?></span>
                        </div>
                    </div>
                    <div class="trip-toggle-btn toggle-icon">▼</div>
                </div>

                <div class="trip-content sub-steps-container d-none" id="sub-steps-<?= $trip->id ?>">
                    <div class="timeline-wrapper">
                        <?php
                        $timelineSteps = [];
                        $timelineSteps[] = ['type' => 'departure', 'name' => $trip->departure, 'time' => $trip->departure_time];

                        foreach ($trip->sub_steps as $subStep) {
                            $pauseValue = '00:00:00';
                            $timeField = $subStep->duration ?? $subStep->heure ?? null;
                            if (!empty($timeField)) {
                                $pauseValue = is_object($timeField) && method_exists($timeField, 'format') ? $timeField->format('H:i:s') : (string)$timeField;
                            }
                            $timelineSteps[] = ['type' => 'stop', 'name' => $subStep->city, 'time' => $pauseValue, 'entity' => $subStep];
                        }

                        $timelineSteps[] = ['type' => 'arrival', 'name' => $trip->arrival];
                        ?>

                        <?php foreach ($timelineSteps as $index => $timelineStep): ?>

                            <?php
                            $stepClass = 'sous-etape-card';
                            $dataPause = ($timelineStep['type'] === 'stop') ? $timelineStep['time'] : '00:00:00';

                            $iconType = ($timelineStep['type'] === 'departure') ? 'icon-start' : (($timelineStep['type'] === 'arrival') ? 'icon-end' : 'icon-step');
                            $emoji = ($timelineStep['type'] === 'departure') ? '🚀' : (($timelineStep['type'] === 'arrival') ? '🏁' : '📍');
                            ?>

                            <div class="step-row">
                                <div class="step-icon <?= $iconType ?>"><?= $emoji ?></div>

                                <div class="step-card <?= $stepClass ?>" data-pause="<?= $dataPause ?>">

                                    <div class="step-header">
                                        <h3><?= h($timelineStep['name']) ?></h3>

                                        <?php if ($timelineStep['type'] === 'departure'): ?>
                                            <span class="step-time">
                                                Départ : <strong><?= is_object($timelineStep['time']) ? $timelineStep['time']->format('H:i') : substr((string)$timelineStep['time'], 0, 5) ?></strong>
                                            </span>
                                        <?php else: ?>
                                            <span class="horaire-calcule">
                                                <span class="segment-loader font-sm-muted">Calcul...</span>
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <?php if ($timelineStep['type'] === 'stop'): ?>
                                        <?php if ($dataPause !== '00:00' && $dataPause !== '00:00:00'): ?>
                                            <div class="pause-text">
                                                ☕ Pause : <strong><?= substr((string)$dataPause, 0, 5) ?></strong>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (!empty($timelineStep['entity']->description)): ?>
                                            <div class="step-desc markdown-to-html"
                                                 data-markdown="<?= htmlspecialchars($timelineStep['entity']->description, ENT_QUOTES, 'UTF-8') ?>">
                                                <span class="font-sm-muted-md">Chargement du texte...</span>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (!empty($timelineStep['entity']->sub_step_photos)): ?>
                                            <div class="step-photos">
                                                <?php foreach ($timelineStep['entity']->sub_step_photos as $photo): ?>
                                                    <?= $this->Html->image('/uploads/sousetapes/' . $photo->photo, [
                                                        'alt' => 'Photo',
                                                        'class' => 'js-zoom-photo'
                                                    ]) ?>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <?php if ($index < count($timelineSteps) - 1): ?>
                                <div class="transport-segment">
                                    <div class="segment-info"
                                         data-mode="<?= strtolower((string)$trip->transport_mode) ?>">
                                        <span
                                            class="segment-icon"><?= $getTransportIcon($trip->transport_mode) ?></span>
                                        <span class="segment-distance">...</span> •
                                        <span class="segment-time">...</span>
                                    </div>
                                </div>
                            <?php endif; ?>

                        <?php endforeach; ?>
                    </div>

                    <div class="map-sticky-wrapper">
                        <div id="map-trajet-<?= $trip->id ?>" class="map-details w-100-h-100"></div>
                    </div>

                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <script>
        const roadTripData = <?= $jsMapDataJson ?>;
    </script>
