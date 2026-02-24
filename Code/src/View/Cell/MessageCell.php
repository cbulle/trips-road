<?php
namespace App\View\Cell;

use Cake\View\Cell;

class MessageCell extends Cell
{
    public function display($userId)
    {
        $messagesTable = $this->fetchTable('Messages');

        $lastMessages = $messagesTable->find()
            ->contain(['Senders', 'Recipients']) // Charge les entités User liées
            ->where(['OR' => [['sender_id' => $userId], ['recipient_id' => $userId]]])
            ->orderDesc('Messages.created')
            ->all();

        $conversations = [];
        foreach ($lastMessages as $msg) {
            $isSender = ($msg->sender_id == $userId);
            $ami = $isSender ? $msg->recipient : $msg->sender;
            $amiId = $ami->id;

            if (isset($conversations[$amiId])) continue;

            $unreadCount = $messagesTable->find()
                ->where(['sender_id' => $amiId, 'recipient_id' => $userId, 'is_read' => 0])
                ->count();

            $conversations[$amiId] = (object)[
                'id' => $amiId,
                'ami' => $ami,
                'last_message' => $msg->body, // Chiffrement géré par l'entité
                'unread_count' => $unreadCount
            ];
        }

        $this->set('enriched', array_values($conversations));
    }
}
