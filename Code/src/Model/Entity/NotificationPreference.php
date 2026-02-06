<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * NotificationPreference Entity
 *
 * @property int $id
 * @property int $user_id
 * @property bool|null $receive_message_notifications
 * @property bool|null $receive_email_notifications
 * @property bool|null $receive_roadtrip_notifications
 * @property bool|null $receive_file_notifications
 * @property \Cake\I18n\Time|null $quiet_hours_start
 * @property \Cake\I18n\Time|null $quiet_hours_end
 * @property \Cake\I18n\DateTime|null $modified
 *
 * @property \App\Model\Entity\User $user
 */
class NotificationPreference extends Entity
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
        'receive_message_notifications' => true,
        'receive_email_notifications' => true,
        'receive_roadtrip_notifications' => true,
        'receive_file_notifications' => true,
        'quiet_hours_start' => true,
        'quiet_hours_end' => true,
        'modified' => true,
        'user' => true,
    ];
}
