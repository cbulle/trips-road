<?php
/**
 * Vue PDF "Cool & Aventure"
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Roadtrip $roadtrip
 */
$this->assign('title', h($roadtrip->title));

$parsedown = new \Parsedown();
?>

<div class="header-rt">
    <h1><?= h($roadtrip->title) ?></h1>
    <p>Road-Trip organisé par <?= h($roadtrip->user->username) ?></p>
</div>

<?php if (!empty($roadtrip->description)): ?>
    <div class="desc-box">
        <?= nl2br(h($roadtrip->description)) ?>
    </div>
<?php endif; ?>

<?php foreach ($roadtrip->trips as $trip): ?>

    <?php
    $currentTimestamp = null;
    if ($trip->departure_time) {
        $currentTimestamp = strtotime($trip->departure_time->format('H:i:s'));
    }
    $tempsDeRouteSecondes = 45 * 60;
    ?>

    <div class="trip-card">
        <div class="trip-title">
            De <?= h($trip->departure) ?> à <?= h($trip->arrival) ?>
            <span class="trip-badge">
                <?= ucfirst($trip->transport_mode) ?>
                <?php if ($trip->date): ?> | <?= $trip->date->format('d/m/Y') ?><?php endif; ?>
            </span>
        </div>

        <div class="timeline">

            <div class="step start">
                <?php if ($currentTimestamp): ?>
                    <div class="step-time">Départ : <?= date('H:i', $currentTimestamp) ?></div>
                    <?php $currentTimestamp += $tempsDeRouteSecondes; ?>
                <?php endif; ?>
                <h3 class="step-name"><?= h($trip->departure) ?></h3>
            </div>

            <?php foreach ($trip->sub_steps as $step): ?>
                <div class="step">

                    <?php if ($currentTimestamp): ?>
                        <div class="step-time">Passage estimé : ~<?= date('H:i', $currentTimestamp) ?></div>
                    <?php endif; ?>

                    <h3 class="step-name"><?= h($step->city) ?></h3>

                    <?php
                    $pauseStr = is_object($step->duration) ? $step->duration->format('H:i') : substr((string)$step->duration, 0, 5);
                    if ($pauseStr && $pauseStr != '00:00'):

                        if ($currentTimestamp) {
                            $parts = explode(':', $pauseStr);
                            $h = isset($parts[0]) ? (int)$parts[0] : 0;
                            $m = isset($parts[1]) ? (int)$parts[1] : 0;
                            $currentTimestamp += ($h * 3600) + ($m * 60);
                        }
                        ?>
                        <span class="step-pause">Temps sur place prévu : <?= $pauseStr ?></span>
                    <?php endif; ?>

                    <?php if (!empty($step->description)): ?>
                        <div class="step-desc">
                            <?= $parsedown->text($step->description) ?>
                        </div>
                    <?php endif; ?>

                    <?php
                    if ($currentTimestamp) {
                        $currentTimestamp += $tempsDeRouteSecondes;
                    }
                    ?>
                </div>
            <?php endforeach; ?>

            <div class="step end">
                <?php if ($currentTimestamp): ?>
                    <div class="step-time">Arrivée estimée : ~<?= date('H:i', $currentTimestamp) ?></div>
                <?php endif; ?>
                <h3 class="step-name"><?= h($trip->arrival) ?></h3>
            </div>

        </div>
    </div>
<?php endforeach; ?>
