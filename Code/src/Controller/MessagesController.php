<?php
namespace App\Controller;
use App\Model\Entity\Message;
use Cake\Http\Exception\ForbiddenException;

class MessagesController extends AppController
{
    /**
     * Méthode privée pour récupérer la liste des conversations (DRY)
     */
    private function _getConversationsList($userId)
    {
        $messagesTable = $this->fetchTable('Messages');
        $usersTable = $this->fetchTable('Users');

        // Dernier message par ami
        $lastMessages = $messagesTable->find()
            ->select(['id', 'sender_id', 'recipient_id', 'body', 'created'])
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
            $amiId = ($msg->sender_id == $userId)
                ? $msg->recipient_id
                : $msg->sender_id;

            if (isset($conversations[$amiId])) {
                continue;
            }

            // On récupère l'ami (attention s'il n'existe plus, ajouter un try/catch serait mieux)
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
                'last_message' => $msg->body, // Sera décrypté automatiquement par l'Entité (voir étape 2)
                'unread_count' => $unreadCount
            ];
        }

        return array_values($conversations);
    }

    public function index()
    {
        $userId = $this->Authentication->getIdentity()->getIdentifier();

        // Appel de la méthode partagée
        $enriched = $this->_getConversationsList($userId);

        $this->set([
            'enriched' => $enriched,
            'userId' => $userId
        ]);
    }

    public function start($amiId = null)
    {
        $userId = $this->Authentication->getIdentity()->getIdentifier();
        $amiId = (int)$amiId;

        if ($userId === $amiId) {
            throw new ForbiddenException();
        }

        // Vérification amitié
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

    public function view($amiId = null)
    {
        $userId = $this->Authentication->getIdentity()->getIdentifier();
        $amiId = (int)$amiId;

        if (!$amiId) {
            return $this->redirect(['action' => 'index']);
        }

        // 1. On récupère la liste des conversations pour la sidebar (CORRECTION DU BUG)
        $enriched = $this->_getConversationsList($userId);

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
            ['is_read' => 1, 'read_at' => date('Y-m-d H:i:s')],
            ['sender_id' => $amiId, 'recipient_id' => $userId, 'is_read' => 0]
        );

        $conversation_id = $amiId;

        $this->set(compact(
            'messages',
            'ami',
            'userId',
            'amiId',
            'conversation_id',
            'enriched' // On passe la variable à la vue
        ));
    }

    public function sendMessage()
    {
        $this->request->allowMethod(['post']);
        $userId = $this->Authentication->getIdentity()->getIdentifier();
        $amiId = (int)$this->request->getData('ami_id');
        $body = trim($this->request->getData('body'));

        $messagesTable = $this->fetchTable('Messages');
        $convTable = $this->fetchTable('Conversations');

        // 1. Trouver ou créer la conversation D'ABORD
        $conversation = $convTable->find()
            ->where(['OR' => [
                ['user_one_id' => $userId, 'user_two_id' => $amiId],
                ['user_one_id' => $amiId, 'user_two_id' => $userId],
            ]])->first();

        if (!$conversation) {
            $conversation = $convTable->newEmptyEntity();
            $conversation = $convTable->patchEntity($conversation, [
                'user_one_id' => $userId,
                'user_two_id' => $amiId
            ]);
            $convTable->save($conversation);
        }

        // 2. Créer le message en une seule fois avec toutes les données
        // Le fait de passer 'body' dans le tableau de patchEntity FORCE l'appel à _setBody
        $message = $messagesTable->newEmptyEntity();
        $data = [
            'sender_id' => $userId,
            'recipient_id' => $amiId,
            'conversation_id' => $conversation->id,
            'body' => $body,
            'is_read' => 0
        ];

        $message = $messagesTable->patchEntity($message, $data);

        if ($messagesTable->save($message)) {
            return $this->redirect(['action' => 'view', $amiId]);
        } else {
            // ... gestion erreur
        }
    }
}
