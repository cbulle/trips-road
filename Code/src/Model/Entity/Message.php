<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Message Entity
 *
 * @property int $id
 * @property int $conversation_id
 * @property int $sender_id
 * @property int $recipient_id
 * @property string $body
 * @property bool|null $is_read
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $delivered_at
 * @property \Cake\I18n\DateTime|null $read_at
 * @property string|null $nonce
 *
 * @property \App\Model\Entity\Conversation $conversation
 * @property \App\Model\Entity\User $sender
 * @property \App\Model\Entity\User $recipient
 */
class Message extends Entity
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
        'conversation_id' => true,
        'sender_id' => true,
        'recipient_id' => true,
        'body' => true,
        'is_read' => true,
        'created' => true,
        'delivered_at' => true,
        'read_at' => true,
        'nonce' => true,
        'conversation' => true,
        'sender' => true,
        'recipient' => true,
    ];
}
