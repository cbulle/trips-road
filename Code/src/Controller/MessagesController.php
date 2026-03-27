<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Entity\Message;
use Cake\Http\Exception\ForbiddenException;

/**
 * Messages Controller
 * Handles user-to-user private messaging.
 *
 * @property \App\Model\Table\MessagesTable $Messages
 */
class MessagesController extends AppController
{
    /**
     * Index method
     * Displays the initial messaging interface (sidebar handled by a Cell).
     *
     * @return void
     */
    public function index(): void
    {
        $userId = $this->Authentication->getIdentity()->getIdentifier();

        $this->set(compact('userId'));
    }

    /**
     * Start method
     * Initiates or verifies a conversation with a friend.
     *
     * @param string|null $friendId The ID of the friend to chat with.
     * @return \Cake\Http\Response|null Redirects to the conversation view.
     * @throws \Cake\Http\Exception\ForbiddenException
     */
    public function start($friendId = null)
    {
        $userId = $this->Authentication->getIdentity()->getIdentifier();
        $friendId = (int)$friendId;

        if ($userId === $friendId) {
            throw new ForbiddenException("Vous ne pouvez pas discuter avec vous-même.");
        }

        $friendshipsTable = $this->getTableLocator()->get('Friendships');

        $isFriend = $friendshipsTable->find()
            ->where([
                'status' => 'accepted',
                'OR' => [
                    ['user_id' => $userId, 'friend_id' => $friendId],
                    ['user_id' => $friendId, 'friend_id' => $userId],
                ]
            ])
            ->first();

        if (!$isFriend) {
            throw new ForbiddenException("Vous n'êtes pas ami avec cet utilisateur.");
        }

        return $this->redirect(['action' => 'view', $friendId]);
    }

    /**
     * View method
     * Displays the chat history with a specific friend.
     *
     * @param string|null $friendId The ID of the friend.
     * @return \Cake\Http\Response|null|void Renders view or redirects if no ID.
     */
    public function view($friendId = null)
    {
        $userId = $this->Authentication->getIdentity()->getIdentifier();
        $friendId = (int)$friendId;

        if (!$friendId) {
            return $this->redirect(['action' => 'index']);
        }

        $friend = $this->Messages->Recipients->get($friendId);

        $user = $this->Messages->Senders->get($userId);

        $messages = $this->Messages->find()
            ->where([
                'OR' => [
                    ['sender_id' => $userId, 'recipient_id' => $friendId],
                    ['sender_id' => $friendId, 'recipient_id' => $userId],
                ]
            ])
            ->orderAsc('created')
            ->all();

        $this->Messages->updateAll(
            ['is_read' => 1, 'read_at' => date('Y-m-d H:i:s')],
            ['sender_id' => $friendId, 'recipient_id' => $userId, 'is_read' => 0]
        );

        $conversationId = $friendId;

        $this->set(compact('messages', 'friend', 'user', 'userId', 'friendId', 'conversationId'));
    }

    /**
     * SendMessage method
     * Handles the submission of a new message via POST.
     *
     * @return \Cake\Http\Response|null Redirects back to the conversation view.
     */
    public function sendMessage()
    {
        $this->request->allowMethod(['post']);

        $userId = $this->Authentication->getIdentity()->getIdentifier();
        $friendId = (int)$this->request->getData('friend_id');
        $body = trim((string)$this->request->getData('body'));

        if (!empty($body) && $friendId) {
            $conversationsTable = $this->getTableLocator()->get('Conversations');

            $conversation = $conversationsTable->find()
                ->where(['OR' => [
                    ['user_one_id' => $userId, 'user_two_id' => $friendId],
                    ['user_one_id' => $friendId, 'user_two_id' => $userId],
                ]])->first();

            if (!$conversation) {
                $conversation = $conversationsTable->newEntity([
                    'user_one_id' => $userId,
                    'user_two_id' => $friendId
                ]);
                $conversationsTable->save($conversation);
            }

            $message = $this->Messages->newEntity([
                'sender_id' => $userId,
                'recipient_id' => $friendId,
                'conversation_id' => $conversation->id,
                'body' => $body,
                'is_read' => 0
            ]);

            $this->Messages->save($message);
        }

        return $this->redirect(['action' => 'view', $friendId]);
    }
}
