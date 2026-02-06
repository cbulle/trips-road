<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * SubStepPhoto Entity
 *
 * @property int $id
 * @property int $sub_step_id
 * @property string $photo
 *
 * @property \App\Model\Entity\SubStep $sub_step
 */
class SubStepPhoto extends Entity
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
        'sub_step_id' => true,
        'photo' => true,
        'sub_step' => true,
    ];
}
