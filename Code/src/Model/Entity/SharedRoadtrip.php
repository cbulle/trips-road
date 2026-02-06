<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * SharedRoadtrip Entity
 *
 * @property int $id
 * @property int $roadtrip_id
 * @property string $token
 * @property int $view_count
 * @property \Cake\I18n\DateTime $created
 *
 * @property \App\Model\Entity\Roadtrip $roadtrip
 */
class SharedRoadtrip extends Entity
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
        'roadtrip_id' => true,
        'token' => true,
        'view_count' => true,
        'created' => true,
        'roadtrip' => true,
    ];

    /**
     * Fields that are excluded from JSON versions of the entity.
     *
     * @var array<string>
     */
    protected array $_hidden = [
        'token',
    ];
}
