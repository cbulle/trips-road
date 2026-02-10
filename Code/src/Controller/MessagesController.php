<?php
namespace App\Controller;

use Cake\Http\Exception\ForbiddenException;

class MessagesController extends AppController
{
    /**
     * Liste des conversations
     */
    public function index()
    {
        $userId = $this->Authentication->getIdentity()->getIdentifier();

        $messagesTable = $this->fetchTable('Messages');
        $usersTable    = $this->fetchTable('Users');

        // Dernier message par ami
        $lastMessages = $messagesTable->find()
            ->select([
                'id',
                'sender_id',
                'recipient_id',
                'body',
                'created'
            ])
            ->where([
                'OR' => [
                    'sender_id' => $userId,
                    'recipient_id' => $userId
                ]
            ])
            ->orderDesc('created')
            ->all();

        $conversations = [];

        foreach ($lastMessages as $msg) {
            $amiId = ($msg->sender_id === $userId)
                ? $msg->recipient_id
                : $msg->sender_id;

            if (isset($conversations[$amiId])) {
                continue;
            }

            $ami = $usersTable->get($amiId);

            $unreadCount = $messagesTable->find()
                ->where([
                    'sender_id' => $amiId,
                    'recipient_id' => $userId,
                    'is_read' => 0
                ])
                ->count();

            $conversations[$amiId] = (object)[
                'id' => $amiId,
                'ami' => $ami,
                'last_message' => $msg->body,
                'unread_count' => $unreadCount
            ];
        }

        $this->set([
            'enriched' => array_values($conversations),
            'userId' => $userId
        ]);
    }

    /**
     * Démarrer une discussion avec un ami
     */
    public function start($amiId = null)
    {
        $userId = $this->Authentication->getIdentity()->getIdentifier();
        $amiId  = (int)$amiId;

        if ($userId === $amiId) {
            throw new ForbiddenException();
        }

        // Vérifier qu'ils sont amis
        $isFriend = $this->fetchTable('Friendships')->find()
            ->where([
                'status' => 'accepted',
                'OR' => [
                    ['user_id' => $userId, 'friend_id' => $amiId],
                    ['user_id' => $amiId, 'friend_id' => $userId],
                ]
            ])
            ->first();

        if (!$isFriend) {
            throw new ForbiddenException();
        }

        return $this->redirect(['action' => 'view', $amiId]);
    }

    /**
     * Voir une discussion
     */
    public function view($amiId = null)
    {
        $userId = $this->Authentication->getIdentity()->getIdentifier();
        $amiId  = (int)$amiId;

        $ami = $this->fetchTable('Users')->get($amiId);

        $messages = $this->fetchTable('Messages')->find()
            ->where([
                'OR' => [
                    ['sender_id' => $userId, 'recipient_id' => $amiId],
                    ['sender_id' => $amiId, 'recipient_id' => $userId],
                ]
            ])
            ->orderAsc('created')
            ->all();

        // Marquer comme lus
        $this->fetchTable('Messages')->updateAll(
            [
                'is_read' => 1,
                'read_at' => date('Y-m-d H:i:s')
            ],
            [
                'sender_id' => $amiId,
                'recipient_id' => $userId,
                'is_read' => 0
            ]
        );

        $this->set(compact(
            'messages',
            'ami',
            'userId',
            'amiId'
        ));
    }

    /**
     * Envoyer un message
     */
    public function sendMessage()
    {
        $this->request->allowMethod(['post']);

        $userId = $this->Authentication->getIdentity()->getIdentifier();
        $amiId  = (int)$this->request->getData('ami_id');
        $body   = trim($this->request->getData('body'));

        if ($body === '') {
            return $this->redirect($this->referer());
        }

        $message = $this->fetchTable('Messages')->newEntity([
            'sender_id' => $userId,
            'recipient_id' => $amiId,
            'body' => $body,
            'is_read' => 0
        ]);

        $this->fetchTable('Messages')->saveOrFail($message);

        return $this->redirect(['action' => 'view', $amiId]);
    }
}
