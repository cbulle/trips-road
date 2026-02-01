<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * PointsOfInterest Entity
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property string|null $latitude
 * @property string|null $longitude
 * @property string|null $category
 * @property int|null $user_id
 *
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\Comment[] $comments
 * @property \App\Model\Entity\Roadtrip[] $roadtrips
 */
class PointsOfInterest extends Entity
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
        'description' => true,
        'latitude' => true,
        'longitude' => true,
        'category' => true,
        'user_id' => true,
        'user' => true,
        'comments' => true,
        'roadtrips' => true,
    ];
}
