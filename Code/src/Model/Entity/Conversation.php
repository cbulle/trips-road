<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Conversation Entity
 *
 * @property int $id
 * @property int $user_one_id
 * @property int $user_two_id
 * @property \Cake\I18n\DateTime|null $modified
 *
 * @property \App\Model\Entity\User $user_one
 * @property \App\Model\Entity\User $user_two
 * @property \App\Model\Entity\Message[] $messages
 */
class Conversation extends Entity
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
        'user_one_id' => true,
        'user_two_id' => true,
        'modified' => true,
        'user_one' => true,
        'user_two' => true,
        'messages' => true,
    ];
}
