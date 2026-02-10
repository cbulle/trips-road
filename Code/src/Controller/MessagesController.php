<?php

namespace App\Controller;

use Cake\Utility\Security;

/**
 * Messages Controller - Version Simplifiée
 *
 * Gère les conversations et les messages chiffrés
 * - Les messages sont chiffrés côté serveur (AES-256-CBC)
 * - Déchiffrement côté serveur pour l'affichage
 * - PAS d'upload de fichiers
 */
class MessagesController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();

        $this->Messages = $this->fetchTable('Messages');
        $this->Conversations = $this->fetchTable('Conversations');
    }



    /**
     * Liste les conversations de l'utilisateur connecté
     * GET /messages
     */
    public function index()
    {
        $userId = $this->Authentication->getIdentity()->id;

        // Récupérer les conversations
        $conversations = $this->Conversations->find()
            ->where([
                'OR' => [
                    'Conversations.user_one_id' => $userId,
                    'Conversations.user_two_id' => $userId,
                ]
            ])
            ->contain(['UserOnes', 'UserTwos', 'Messages'])
            ->order(['Conversations.modified' => 'DESC'])
            ->all();

        // Enrichir les données
        $enriched = [];
        foreach ($conversations as $conv) {
            $amiId = ($conv->user_one_id === $userId) ? $conv->user_two_id : $conv->user_one_id;
            $ami = ($conv->user_one_id === $userId) ? $conv->user_two : $conv->user_one;

            $unreadCount = $this->Messages->find()
                ->where([
                    'conversation_id' => $conv->id,
                    'recipient_id' => $userId,
                    'is_read' => false
                ])
                ->count();

            $lastMessage = $this->Messages->find()
                ->where(['conversation_id' => $conv->id])
                ->order(['created' => 'DESC'])
                ->first();

            $conv->ami_id = $amiId;
            $conv->ami = $ami;
            $conv->unread_count = $unreadCount;
            $conv->last_message = $lastMessage ? mb_substr($lastMessage->body, 0, 50) : '';

            $enriched[] = $conv;
        }

        $this->set(compact('enriched', 'userId'));
    }

    /**
     * Affiche une conversation spécifique
     * GET /messages/:id
     */
    public function view($conversationId = null)
    {
        if (!$conversationId) {
            return $this->redirect(['action' => 'index']);
        }

        $userId = $this->Authentication->getIdentity()->id;

        // Récupérer la conversation
        $conversation = $this->Conversations->get($conversationId, [
            'contain' => ['UserOnes', 'UserTwos']
        ]);

        // Vérifier l'accès
        if ($conversation->user_one_id !== $userId && $conversation->user_two_id !== $userId) {
            throw new \Cake\Http\Exception\ForbiddenException('Accès refusé');
        }

        // Identifier l'ami
        $amiId = ($conversation->user_one_id === $userId) ? $conversation->user_two_id : $conversation->user_one_id;
        $ami = ($conversation->user_one_id === $userId) ? $conversation->user_two : $conversation->user_one;

        // Marquer les messages comme lus
        $this->Messages->updateAll(
            ['is_read' => true, 'read_at' => date('Y-m-d H:i:s')],
            [
                'conversation_id' => $conversationId,
                'recipient_id' => $userId,
                'is_read' => false
            ]
        );

        // Récupérer les messages
        $messages = $this->Messages->find()
            ->where(['conversation_id' => $conversationId])
            ->contain(['Senders'])
            ->order(['Messages.created' => 'ASC'])
            ->all();

        // Déchiffrer les messages
        foreach ($messages as $message) {
            $message->body = $this->decryptMessage($message->body, $conversationId);
        }

        $this->set(compact('conversation', 'ami', 'amiId', 'messages', 'userId'));
    }

    /**
     * Envoyer un message chiffré
     * POST /messages/send-message
     */
    public function sendMessage()
    {
        $userId = $this->Authentication->getIdentity()->id;

        if (!$this->request->is('post')) {
            return $this->sendJsonResponse(false, 'Method not allowed', 405);
        }

        $conversationId = $this->request->getData('conversation_id');
        $recipientId = $this->request->getData('recipient_id');
        $messageBody = $this->request->getData('body') ?? $this->request->getData('message');

        if (!$conversationId || !$recipientId || !$messageBody) {
            return $this->sendJsonResponse(false, 'Données manquantes', 400);
        }

        // Vérifier l'accès
        try {
            $conversation = $this->Conversations->get($conversationId);
        } catch (\Exception $e) {
            return $this->sendJsonResponse(false, 'Conversation not found', 404);
        }

        if ($conversation->user_one_id !== $userId && $conversation->user_two_id !== $userId) {
            return $this->sendJsonResponse(false, 'Accès refusé', 403);
        }

        // Chiffrer et sauvegarder
        $encryptedMessage = $this->encryptMessage($messageBody, $conversationId);

        $message = $this->Messages->newEntity([
            'conversation_id' => $conversationId,
            'sender_id' => $userId,
            'recipient_id' => $recipientId,
            'body' => $encryptedMessage,
            'is_read' => false,
            'delivered_at' => date('Y-m-d H:i:s')
        ]);
        $blocked = $this->Friendships->find()
            ->where([
                'status' => 'blocked',
                'OR' => [
                    ['user_id' => $userId, 'friend_id' => $recipientId],
                    ['user_id' => $recipientId, 'friend_id' => $userId],
                ]
            ])
            ->count();

        if ($blocked) {
            return $this->sendJsonResponse(false, 'Utilisateur bloqué', 403);
        }

        if ($this->Messages->save($message)) {


            return $this->sendJsonResponse(true, 'Message sent', 200, [
                'message_id' => $message->id
            ]);
        }

        return $this->sendJsonResponse(false, 'Error saving message', 500);
    }

    /**
     * Créer ou récupérer une conversation
     * POST /messages/get-or-create
     */
    public function getOrCreateConversation()
    {
        $userId = $this->Authentication->getIdentity()->id;

        if (!$this->request->is('post')) {
            return $this->sendJsonResponse(false, 'Method not allowed', 405);
        }

        $recipientId = $this->request->getData('recipient_id');

        if (!$recipientId) {
            return $this->sendJsonResponse(false, 'recipient_id manquant', 400);
        }

        // Chercher une conversation existante
        $conversation = $this->Conversations->find()
            ->where([
                'OR' => [
                    ['user_one_id' => $userId, 'user_two_id' => $recipientId],
                    ['user_one_id' => $recipientId, 'user_two_id' => $userId],
                ]
            ])
            ->first();

        if (!$conversation) {
            // Créer une nouvelle
            $conversation = $this->Conversations->newEntity([
                'user_one_id' => $userId,
                'user_two_id' => $recipientId
            ]);

            if (!$this->Conversations->save($conversation)) {
                return $this->sendJsonResponse(false, 'Error creating conversation', 500);
            }
        }

        return $this->sendJsonResponse(true, 'Success', 200, [
            'conversation_id' => $conversation->id
        ]);
    }

    /**
     * Chiffrer un message - AES-256-CBC
     */
    private function encryptMessage(string $message, int $conversationId): string
    {
        $key = hash('sha256', 'roadtrip_conv_' . $conversationId . Security::getSalt());
        $iv = openssl_random_pseudo_bytes(16);

        $encrypted = openssl_encrypt(
            $message,
            'AES-256-CBC',
            hash('sha256', $key, true),
            OPENSSL_RAW_DATA,
            $iv
        );

        return base64_encode($iv . $encrypted);
    }

    /**
     * Déchiffrer un message - AES-256-CBC
     */
    private function decryptMessage(string $encryptedMessage, int $conversationId): string
    {
        try {
            $key = hash('sha256', 'roadtrip_conv_' . $conversationId . Security::getSalt());
            $data = base64_decode($encryptedMessage, true);

            if ($data === false) {
                return $encryptedMessage;
            }

            $iv = substr($data, 0, 16);
            $encrypted = substr($data, 16);

            $decrypted = openssl_decrypt(
                $encrypted,
                'AES-256-CBC',
                hash('sha256', $key, true),
                OPENSSL_RAW_DATA,
                $iv
            );

            return $decrypted !== false ? $decrypted : $encryptedMessage;
        } catch (\Exception $e) {
            return $encryptedMessage;
        }
    }

    /**
     * Helper pour réponses JSON
     */
    private function sendJsonResponse(bool $success, string $message, int $statusCode = 200, array $data = []): \Cake\Http\Response
    {
        $response = [
            'success' => $success,
            'message' => $message
        ];

        if (!empty($data)) {
            $response = array_merge($response, $data);
        }

        return $this->response
            ->withStatus($statusCode)
            ->withType('application/json')
            ->withStringBody(json_encode($response));
    }
}
