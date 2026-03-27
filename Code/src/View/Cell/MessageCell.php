<?php
declare(strict_types=1);

namespace App\View\Cell;

use Cake\View\Cell;

/**
 * Message cell
 */
class MessageCell extends Cell
{
    /**
     * List of valid options that can be passed into this
     * cell's constructor.
     *
     * @var array<string, mixed>
     */
    protected array $_validCellOptions = [];

    /**
     * Default display method.
     *
     * @param int $userId The current logged in user
     * @param int|null $activeFriendId The friend currently being chatted with
     * @return void
     */
    public function display(int $userId, ?int $activeFriendId = null): void
    {
        // Remplacement de loadModel par fetchTable (Standard CakePHP récent)
        $conversationsTable = $this->fetchTable('Conversations');
        $usersTable = $this->fetchTable('Users');

        $conversations = $conversationsTable->find()
            ->where(['OR' => ['user_one_id' => $userId, 'user_two_id' => $userId]])
            ->contain([
                'Messages' => function ($q) {
                    return $q->orderBy(['Messages.created' => 'DESC'])->limit(1);
                }
            ])
            ->all();

        $formattedConversations = [];

        foreach ($conversations as $conv) {
            $friendId = ($conv->user_one_id === $userId) ? $conv->user_two_id : $conv->user_one_id;
            $friend = $usersTable->get($friendId);

            $lastMessage = !empty($conv->messages) ? $conv->messages[0] : null;

            $formattedConversations[] = [
                'friend' => $friend,
                'lastMessage' => $lastMessage,
                'isActive' => ($friendId === $activeFriendId)
            ];
        }

        usort($formattedConversations, function($a, $b) {
            $timeA = $a['lastMessage'] ? $a['lastMessage']->created->toUnixString() : 0;
            $timeB = $b['lastMessage'] ? $b['lastMessage']->created->toUnixString() : 0;
            return $timeB <=> $timeA;
        });

        $this->set(compact('formattedConversations', 'userId'));
    }
}
