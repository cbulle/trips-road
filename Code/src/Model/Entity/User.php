<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * User Entity
 *
 * @property int $id
 * @property string $last_name
 * @property string|null $first_name
 * @property string $email
 * @property string|null $password
 * @property string|null $address
 * @property int|null $zipcode
 * @property string|null $city
 * @property string|null $phone
 * @property \Cake\I18n\Date|null $birth_date
 * @property string|null $profile_picture
 * @property string|null $public_key
 * @property string|null $session_id
 * @property string|null $username
 * @property string|null $reset_token_hash
 * @property \Cake\I18n\DateTime|null $reset_expires_at
 * @property string|null $google_id
 * @property \Cake\I18n\DateTime|null $created
 *
 * @property \App\Model\Entity\NotificationPreference $notification_preference
 * @property \App\Model\Entity\Comment[] $comments
 * @property \App\Model\Entity\FavoritePlace[] $favorite_places
 * @property \App\Model\Entity\Favorite[] $favorites
 * @property \App\Model\Entity\Friend[] $friends
 * @property \App\Model\Entity\History[] $histories
 * @property \App\Model\Entity\PointsOfInterest[] $points_of_interests
 * @property \App\Model\Entity\Roadtrip[] $roadtrips
 * @property \App\Model\Entity\UserToken[] $user_tokens
 */
class User extends Entity
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
        'last_name' => true,
        'first_name' => true,
        'email' => true,
        'password' => true,
        'address' => true,
        'zipcode' => true,
        'city' => true,
        'phone' => true,
        'birth_date' => true,
        'profile_picture' => true,
        'public_key' => true,
        'session_id' => true,
        'username' => true,
        'reset_token_hash' => true,
        'reset_expires_at' => true,
        'google_id' => true,
        'created' => true,
        'notification_preference' => true,
        'comments' => true,
        'favorite_places' => true,
        'favorites' => true,
        'friends' => true,
        'histories' => true,
        'points_of_interests' => true,
        'roadtrips' => true,
        'user_tokens' => true,
    ];

    /**
     * Fields that are excluded from JSON versions of the entity.
     *
     * @var array<string>
     */
    protected array $_hidden = [
        'password',
    ];
}
