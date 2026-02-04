<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Roadtrip $roadtrip
 * @var string $jsMapDataJson (Le JSON généré par le Controller)
 * @var bool $isMyRoadTrip
 */

$this->assign('mainClass', '');

$mapDataArray = json_decode($jsMapDataJson, true);

$transportIcons = [
    'voiture' => '🚗',
    'velo' => '🚴', 'vélo' => '🚴',
    'marche' => '🚶', 'à pied' => '🚶',
    'train' => '🚂', 'bus' => '🚌', 'avion' => '✈️', 'moto' => '🏍️'
];

$getIcon = function ($mode) use ($transportIcons) {
    return $transportIcons[strtolower($mode ?? '')] ?? '🚗';
};
?>

<div class="roadtrip-vu">

    <div class="roadtrip-header">
        <h1><?= h($roadtrip->title) ?></h1>
        <div class="author-info">
            <?php
            $ppUrl = '/img/User.png';
            if (!empty($roadtrip->user->profile_picture)) {
                $ppUrl = WWW_ROOT.'/uploads/pp/' . $roadtrip->user->profile_picture;
            }
            ?>
            <img src="<?= $ppUrl ?>" class="author-pp" alt="Auteur">
            <span>Proposé par <strong><?= h($roadtrip->user->username) ?></strong></span>
        </div>
        <p><?= nl2br($roadtrip->description) ?></p>
    </div>

    <h2>Vue d'ensemble 🌍</h2>
    <div id="map-global"></div>

    <?php foreach ($roadtrip->trips as $index => $trip) : ?>
        <?php
        $currentMapData = $mapDataArray[$index] ?? null;
        ?>

        <div class="card-vu" id="card-<?= $trip->id ?>">

            <div class="trajet-header" onclick="toggleTrajet(<?= $trip->id ?>)">
                <div class="trajet-info">
                    <h2 class="trajet-titre"><?= h($trip->departure . ' ➝ ' . $trip->arrival) ?></h2>
                    <div class="trajet-details">
                        <?php if (!empty($trip->trip_date)) : ?>
                            <div class="trajet-detail-item">
                                <span>📅 <?= $trip->trip_date->format('d/m/Y') ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="trajet-detail-item">
                            <span class="transport-icon"><?= $getIcon($trip->transport_mode) ?></span>
                        </div>
                    </div>
                </div>
                <div class="toggle-icon">▼</div>
            </div>

            <div class="sous-etapes-container" id="sous-etapes-<?= $trip->id ?>" style="display: none;">

                <div class="trajet-details-column">
                    <?php
                    $timeline = [];

                    $timeline[] = [
                        'type' => 'depart',
                        'nom' => $trip->departure,
                        'heure' => $trip->departure_time ? $trip->departure_time->format('H:i') : null,
                        'coords' => $currentMapData['depart'] ?? null
                    ];

                    foreach ($trip->sub_steps as $k => $subStep) {
                        $timeline[] = [
                            'type' => 'etape',
                            'nom' => $subStep->city,
                            'heure' => $subStep->duration ? $subStep->duration->format('H:i') : '00:00',
                            'desc' => $subStep->description,
                            'photos' => $subStep->sub_step_photos,
                            'coords' => $currentMapData['sousEtapes'][$k] ?? null
                        ];
                    }

                    $timeline[] = [
                        'type' => 'arrivee',
                        'nom' => $trip->arrival,
                        'coords' => $currentMapData['arrivee'] ?? null
                    ];

                    for ($i = 0; $i < count($timeline); $i++) :
                        $step = $timeline[$i];
                        $isDep = ($step['type'] === 'depart');
                        $isArr = ($step['type'] === 'arrivee');
                        $pauseDuration = $step['heure'] ?? '00:00';
                        ?>
                        <div class="sous-etape-card <?= $isDep ? 'depart-card' : ($isArr ? 'arrivee-card' : '') ?>">

                            <div class="sous-etape-header">
                                <h3>
                                    <?php
                                    if ($isDep) echo '🚀 Départ : ';
                                    elseif ($isArr) echo '🏁 Arrivée : ';
                                    else echo '📍 ';
                                    echo h($step['nom']);
                                    ?>
                                </h3>
                                <div class="horaire-info">
                                    <?php if ($isDep && !empty($step['heure'])): ?>
                                        <span
                                            class="horaire-depart">🕐 Départ : <strong><?= h($step['heure']) ?></strong></span>
                                    <?php elseif (!$isDep): ?>
                                        <span class="horaire-calcule">
                                            <span class="horaire-loader">⏱️ Calcul...</span>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <?php if (!$isDep && !$isArr): ?>
                                <div class="sous-etape-info">
                                    <?php if (!empty($step['heure']) && $step['heure'] !== '00:00') : ?>
                                        <span
                                            class="pause-duree">⏰ Temps sur place : <strong><?= h($step['heure']) ?></strong></span>
                                    <?php endif; ?>
                                </div>

                                <?php if (!empty($step['desc'])) : ?>
                                    <div class="sous-etape-description">
                                        <h4 class="description-title">📝 Description</h4>
                                        <div class="tinymce-content">
                                            <?= $step['desc'] ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($step['photos'])) : ?>
                                    <div class="photos-section">
                                        <h4 class="photos-title">📷 Photos</h4>
                                        <div class="photos-container">
                                            <?php foreach ($step['photos'] as $photo) : ?>
                                                <?= $this->Html->image('/uploads/sousetapes/' . $photo->photo_url, [
                                                    'alt' => 'Photo',
                                                    'class' => 'popup-photo'
                                                ]) ?>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>

                        <?php
                        if ($i < count($timeline) - 1) :
                            $nextStep = $timeline[$i + 1];

                            $c1 = $step['coords'];
                            $c2 = $nextStep['coords'];

                            if ($c1 && $c2 && !empty($c1['lat']) && !empty($c2['lat'])) :
                                ?>
                                <div class="segment-transport">
                                    <div class="segment-line"></div>
                                    <div class="segment-info"
                                         data-lat-dep="<?= $c1['lat'] ?>"
                                         data-lon-dep="<?= $c1['lon'] ?>"
                                         data-lat-arr="<?= $c2['lat'] ?>"
                                         data-lon-arr="<?= $c2['lon'] ?>"
                                         data-mode="<?= strtolower($trip->transport_mode) ?>">
                                        <span class="segment-icon"><?= $getIcon($trip->transport_mode) ?></span>
                                        <span class="segment-distance">...</span>
                                        <span class="segment-separator">•</span>
                                        <span class="segment-time">...</span>
                                    </div>
                                </div>
                            <?php
                            endif;
                        endif;
                        ?>
                    <?php endfor; ?>
                </div>

                <div class="map-container-vu">
                    <div id="map-trajet-<?= $trip->id ?>" class="map-trajet"></div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<script>
    const roadTripData = <?= $jsMapDataJson ?>;
    console.log("Données chargées pour la carte :", roadTripData);

    function toggleTrajet(id) {
        const content = document.getElementById('sous-etapes-' + id);
        const card = document.getElementById('card-' + id);

        if (content.style.display === 'block') {
            content.style.display = 'none';
            card.classList.remove('active');
        } else {
            content.style.display = 'block';
            card.classList.add('active');

            setTimeout(() => {
                window.dispatchEvent(new Event('resize'));
            }, 200);
        }
    }
</script>

