<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Report Entity
 *
 * @property int $id
 * @property int $reporter_id
 * @property string $content_type
 * @property int $target_id
 * @property string|null $reason
 * @property \Cake\I18n\DateTime|null $created
 *
 * @property \App\Model\Entity\User $reporter
 */
class Report extends Entity
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
        'reporter_id' => true,
        'content_type' => true,
        'target_id' => true,
        'reason' => true,
        'created' => true,
        'reporter' => true,
    ];
}
