<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * SubStep Entity
 *
 * @property int $id
 * @property int $order_number
 * @property string $city
 * @property string|null $description
 * @property int $trip_id
 * @property string $transport_type
 * @property \Cake\I18n\Time|null $heure
 * @property bool|null $avoid_highways
 * @property bool|null $avoid_tolls
 *
 * @property \App\Model\Entity\Trip $trip
 * @property \App\Model\Entity\SubStepPhoto[] $sub_step_photos
 */
class SubStep extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'order_number' => true,
        'city' => true,
        'description' => true,
        'trip_id' => true,
        'transport_type' => true,
        'heure' => true,
        'avoid_highways' => true,
        'avoid_tolls' => true,
        'trip' => true,
        'sub_step_photos' => true,
    ];
}
