<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Roadtrip Entity
 *
 * @property int $id
 * @property string $title
 * @property string|null $description
 * @property string|null $visibility
 * @property \Cake\I18n\DateTime|null $created
 * @property int $user_id
 * @property string|null $photo_url
 * @property string|null $status
 *
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\Comment[] $comments
 * @property \App\Model\Entity\Favorite[] $favorites
 * @property \App\Model\Entity\History[] $histories
 * @property \App\Model\Entity\SharedRoadtrip[] $shared_roadtrips
 * @property \App\Model\Entity\Trip[] $trips
 * @property \App\Model\Entity\PointsOfInterest[] $points_of_interests
 */
class Roadtrip extends Entity
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
        'title' => true,
        'description' => true,
        'visibility' => true,
        'created' => true,
        'user_id' => true,
        'photo_url' => true,
        'status' => true,
        'user' => true,
        'comments' => true,
        'favorites' => true,
        'histories' => true,
        'shared_roadtrips' => true,
        'trips' => true,
        'points_of_interests' => true,
        'place' => true,
        'photo_cover' => true,
    ];

    /**
     * Calculate and return the url of the cover image
     * @return string
     */
    protected function _getCoverImage(): string
    {
        $defaultImage = '/img/imgBase.png';
        $photoName = $this->photo_url ?? $this->photo ?? $this->photo_cover ?? null;

        if (!empty($photoName)) {
            $physicalPath = WWW_ROOT . 'uploads' . DS . 'roadtrips' . DS . $photoName;
            if (file_exists($physicalPath)) {
                return '/uploads/roadtrips/' . $photoName;
            }
        }
        return $defaultImage;
    }

    /**
     * Check if the roadtrip is over.
     * @return bool
     */
    protected function _getIsCompleted(): bool
    {
        return ($this->status === 'completed' || $this->status === 'termine');
    }
}
