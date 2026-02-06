<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Comment Entity
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $roadtrip_id
 * @property int|null $points_of_interest_id
 * @property string $body
 * @property int|null $rating
 * @property \Cake\I18n\DateTime|null $created
 *
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\Roadtrip $roadtrip
 * @property \App\Model\Entity\PointsOfInterest $points_of_interest
 */
class Comment extends Entity
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
        'user_id' => true,
        'roadtrip_id' => true,
        'points_of_interest_id' => true,
        'body' => true,
        'rating' => true,
        'created' => true,
        'user' => true,
        'roadtrip' => true,
        'points_of_interest' => true,
    ];
}
