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

<div class="roadtrip-vu">

    <div class="roadtrip-header">
        <h1>
            <?= h($roadtrip->title) ?>
            <?php if (isset($isOwner) && $isOwner): ?>
                <?php
                $isPublished = ($roadtrip->status === 'completed' || $roadtrip->status === 'termine');
                $badgeClass = $isPublished ? 'status-termine' : 'status-brouillon';
                $badgeText = $isPublished ? 'Publié' : 'Brouillon';
                ?>
                <span class="status-badge <?= $badgeClass ?>"><?= $badgeText ?></span>
            <?php endif; ?>
        </h1>

        <div class="author-info">
            <?php
            $pp = $roadtrip->user->profile_picture ?: 'User.png';
            $ppUrl = $this->Url->webroot('uploads/pp/' . $pp);
            ?>
            <img src="<?= $ppUrl ?>" class="author-pp" alt="Auteur">
            <span>Proposé par <strong><?= h($roadtrip->user->username) ?></strong></span>
        </div>

        <div class="roadtrip-description">
            <p><?= nl2br(h($roadtrip->description)) ?></p>
        </div>
    </div>

    <h2>Vue d'ensemble 🌍</h2>
    <div id="map-global"></div>

    <?php foreach ($roadtrip->trips as $index => $trip): ?>
        <div class="card-vu" id="card-<?= $trip->id ?>">

            <div class="trajet-header" onclick="toggleTrajet(<?= $trip->id ?>)">
                <div class="trajet-info">
                    <h2 class="trajet-titre"><?= h($trip->departure . ' ➝ ' . $trip->arrival) ?></h2>
                    <div class="trajet-details">
                        <?php if ($trip->trip_date): ?>
                            <span>📅 <?= $trip->trip_date->format('d/m/Y') ?></span>
                        <?php endif; ?>
                        <span class="transport-icon"><?= $getIcon($trip->transport_mode) ?></span>
                    </div>
                </div>
                <div class="toggle-icon">▼</div>
            </div>

            <div class="sous-etapes-container" id="sous-etapes-<?= $trip->id ?>" style="display:none;">
                <div class="sous-etapes-content-wrapper">

                    <div class="trajet-details-column">
                        <?php
                        $steps = [];

                        $steps[] = ['type' => 'dep', 'nom' => $trip->departure, 'heure' => $trip->departure_time];

                        foreach ($trip->sub_steps as $ss) {
                            $dureePause = '00:00:00';
                            if (!empty($ss->heure)) {
                                if (is_object($ss->heure) && method_exists($ss->heure, 'format')) {
                                    $dureePause = $ss->heure->format('H:i:s');
                                } else {
                                    $dureePause = (string)$ss->heure;
                                }
                            }
                            $steps[] = ['type' => 'step', 'nom' => $ss->city, 'heure' => $dureePause, 'obj' => $ss];
                        }

                        $steps[] = ['type' => 'arr', 'nom' => $trip->arrival];
                        ?>

                        <?php foreach ($steps as $i => $step): ?>
                            <?php
                            $class = ($step['type'] == 'dep') ? 'depart-card' : (($step['type'] == 'arr') ? 'arrivee-card' : 'sous-etape-card');
                            $icon = ($step['type'] == 'dep') ? '🚀' : (($step['type'] == 'arr') ? '🏁' : '📍');

                            $dataPauseVal = '00:00:00';
                            if ($step['type'] == 'step') {
                                $dataPauseVal = $step['heure'];
                            }
                            ?>

                            <div class="sous-etape-card <?= $class ?>" data-pause="<?= $dataPauseVal ?>">

                                <div class="sous-etape-header">
                                    <h3><?= $icon ?> <?= h($step['nom']) ?></h3>
                                    <div class="horaire-info">
                                        <?php if ($step['type'] == 'dep'): ?>
                                            <span class="horaire-depart">
                                                🕐 Départ : <strong><?= is_object($step['heure']) ? $step['heure']->format('H:i') : substr($step['heure'], 0, 5) ?></strong>
                                            </span>
                                        <?php else: ?>
                                            <span class="horaire-calcule">
                                                <span class="horaire-loader">⏱️ Calcul...</span>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <?php if ($step['type'] == 'step'): ?>

                                    <?php if ($dataPauseVal !== '00:00:00' && $dataPauseVal !== '00:00'): ?>
                                        <div class="sous-etape-info">
                                            <span class="pause-duree">
                                                ☕ Temps sur place : <strong><?= h(substr($dataPauseVal, 0, 5)) ?></strong>
                                            </span>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($step['obj']->description)): ?>
                                        <div class="sous-etape-description">
                                            <div class="tinymce-content"><?= $step['obj']->description ?></div>
                                        </div>
                                    <?php endif; ?>


                                    <?php if (!empty($step['obj']->sub_step_photos)): ?>
                                        <div class="photos-section">
                                            <h4 class="photos-title">📷 Photos</h4>
                                            <div class="photos-container">
                                                <?php foreach ($step['obj']->sub_step_photos as $photo): ?>
                                                    <?php
                                                    $nomFichier = $photo->photo;
                                                    ?>

                                                    <?= $this->Html->image('/uploads/sousetapes/' . $nomFichier, [
                                                        'class' => 'popup-photo',
                                                        'alt' => 'Photo étape',
                                                        'loading' => 'lazy',
                                                        'onclick' => "window.open(this.src, '_blank')"
                                                    ]) ?>

                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>>

                                <?php endif; ?>
                            </div>

                            <?php if ($i < count($steps) - 1): ?>
                                <div class="segment-transport">
                                    <div class="segment-line"></div>
                                    <div class="segment-info" data-mode="<?= strtolower($trip->transport_mode) ?>">
                                        <span class="segment-icon"><?= $getIcon($trip->transport_mode) ?></span>
                                        <span class="segment-distance">...</span> •
                                        <span class="segment-time">...</span>
                                    </div>
                                </div>
                            <?php endif; ?>

                        <?php endforeach; ?>
                    </div>

                    <div class="map-container-vu">
                        <div id="map-trajet-<?= $trip->id ?>" class="map-trajet"></div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<script>
    const roadTripData = <?= $jsMapDataJson ?>;
</script>
