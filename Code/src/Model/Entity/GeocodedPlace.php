<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * GeocodedPlace Entity
 *
 * @property int $id
 * @property string $name
 * @property string $latitude
 * @property string $longitude
 * @property string|null $type
 * @property \Cake\I18n\DateTime|null $modified
 */
class GeocodedPlace extends Entity
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
        'name' => true,
        'latitude' => true,
        'longitude' => true,
        'type' => true,
        'modified' => true,
    ];
}
