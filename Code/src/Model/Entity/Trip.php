<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Trip Entity
 *
 * @property int $id
 * @property int $order_number
 * @property string $title
 * @property string $departure
 * @property string $arrival
 * @property \Cake\I18n\DateTime|null $created
 * @property string|null $transport_mode
 * @property int $roadtrip_id
 * @property bool|null $avoid_highways
 * @property bool|null $avoid_tolls
 * @property \Cake\I18n\Time $departure_time
 *
 * @property \App\Model\Entity\Roadtrip $roadtrip
 * @property \App\Model\Entity\SubStep[] $sub_steps
 */
class Trip extends Entity
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
        'title' => true,
        'departure' => true,
        'arrival' => true,
        'created' => true,
        'transport_mode' => true,
        'roadtrip_id' => true,
        'avoid_highways' => true,
        'avoid_tolls' => true,
        'departure_time' => true,
        'roadtrip' => true,
        'sub_steps' => true,
    ];
}
