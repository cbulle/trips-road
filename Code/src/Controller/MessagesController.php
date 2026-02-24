<?php
namespace App\Controller;

use App\Model\Entity\Message;
use Cake\Http\Exception\ForbiddenException;

class MessagesController extends AppController
{
    /**
     * Index : La récupération des conversations pour la sidebar
     * est maintenant gérée par la Cell dans la vue.
     */
    public function index()
    {
        $userId = $this->Authentication->getIdentity()->getIdentifier();

        $this->set([
            'userId' => $userId
        ]);
    }

    /**
     * Démarrer une conversation
     */
    public function start($amiId = null)
    {
        $userId = $this->Authentication->getIdentity()->getIdentifier();
        $amiId = (int)$amiId;

        if ($userId === $amiId) {
            throw new ForbiddenException("Vous ne pouvez pas discuter avec vous-même.");
        }

        $friendshipsTable = $this->getTableLocator()->get('Friendships');
        $isFriend = $friendshipsTable->find()
            ->where([
                'status' => 'accepted',
                'OR' => [
                    ['user_id' => $userId, 'friend_id' => $amiId],
                    ['user_id' => $amiId, 'friend_id' => $userId],
                ]
            ])
            ->first();

        if (!$isFriend) {
            throw new ForbiddenException("Vous n'êtes pas ami avec cet utilisateur.");
        }

        return $this->redirect(['action' => 'view', $amiId]);
    }

    /**
     * Voir une conversation
     */
    public function view($amiId = null)
    {
        $userId = $this->Authentication->getIdentity()->getIdentifier();
        $amiId = (int)$amiId;

        if (!$amiId) {
            return $this->redirect(['action' => 'index']);
        }

        $ami = $this->Messages->Recipients->get($amiId);

        $messages = $this->Messages->find()
            ->where([
                'OR' => [
                    ['sender_id' => $userId, 'recipient_id' => $amiId],
                    ['sender_id' => $amiId, 'recipient_id' => $userId],
                ]
            ])
            ->orderAsc('created')
            ->all();

        // Marquer comme lus
        $this->Messages->updateAll(
            ['is_read' => 1, 'read_at' => date('Y-m-d H:i:s')],
            ['sender_id' => $amiId, 'recipient_id' => $userId, 'is_read' => 0]
        );

        $conversation_id = $amiId;

        $this->set(compact(
            'messages',
            'ami',
            'userId',
            'amiId',
            'conversation_id'
        ));
    }

    /**
     * Envoyer un message
     */
    public function sendMessage()
    {
        $this->request->allowMethod(['post']);
        $userId = $this->Authentication->getIdentity()->getIdentifier();
        $amiId = (int)$this->request->getData('ami_id');
        $body = trim($this->request->getData('body'));

        $convTable = $this->Messages->Conversations;

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

        $message = $this->Messages->newEmptyEntity();

        $message = $this->Messages->patchEntity($message, [
            'sender_id' => $userId,
            'recipient_id' => $amiId,
            'conversation_id' => $conversation->id,
            'is_read' => 0
        ]);

        $message->body = $body;

        if ($this->Messages->save($message)) {
            return $this->redirect(['action' => 'view', $amiId]);
        }

        return $this->redirect(['action' => 'view', $amiId]);
    }
}
