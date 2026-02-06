<?php

namespace App\Controller;

/**
 * Friendships Controller
 *
 */
class FriendshipsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        /** @var int $userId */

        $identity = $this->request->getAttribute('identity');
        $userId = $identity->getIdentifier();

        $search = $this->request->getQuery('search');
        $users = [];

        if (!empty($search)) {
            $users = $this->fetchTable('Users')
                ->find()
                ->where([
                    'OR' => [
                        'Users.first_name LIKE' => "%$search%",
                        'Users.last_name LIKE' => "%$search%",
                    ],
                    'Users.id !=' => $userId
                ])
                ->limit(20)
                ->all();

            $friendships = $this->Friendships->find()
                ->where([
                    'OR' => [
                        ['user_id' => $userId],
                        ['friend_id' => $userId],
                    ]
                ])
                ->all()
                ->indexBy(function ($row) use ($userId) {
                    return $row->user_id === $userId
                        ? $row->friend_id
                        : $row->user_id;
                })
                ->toArray();

            foreach ($users as $user) {
                $user->friendship_status =
                    $friendships[$user->id]->status ?? null;
            }
        }

        $friends = $this->Friendships->find()
            ->where([
                'status' => 'accepted',
                'OR' => [
                    'user_id' => $userId,
                    'friend_id' => $userId,
                ]
            ])
            ->contain([
                'Users' => function ($q) use ($userId) {
                    return $q->where(['Users.id !=' => $userId]);
                }
            ])
            ->all();

        foreach ($friends as $friendship) {
            $friendship->friend =
                $friendship->user_id === $userId
                    ? $friendship->user
                    : $friendship->friend;
        }

        $requests = $this->Friendships->find()
            ->where([
                'friend_id' => $userId,
                'status' => 'pending'
            ])
            ->contain(['Users'])
            ->all();

        $this->set(compact(
            'users',
            'friends',
            'requests',
            'search',
            'userId'
        ));
    }


    /**
     * View method
     *
     * @param string|null $id Friendship id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $friendship = $this->Friendships->get($id);
        $this->set(compact('friendship'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $this->request->allowMethod(['post']);
        $friendId = (int)$this->request->getData('friend_id');

        $userId = $this->Authentication->getIdentity()->id;

        if ($userId === $friendId) {
            $this->Flash->error('Action invalide.');
            return $this->redirect(['action' => 'index']);
        }

        if (!$this->Friendships->Users->exists(['id' => $friendId])) {
            $this->Flash->error('Utilisateur introuvable.');
            return $this->redirect(['action' => 'index']);
        }

        $exists = $this->Friendships->find()
            ->where([
                'OR' => [
                    ['user_id' => $userId, 'friend_id' => $friendId],
                    ['user_id' => $friendId, 'friend_id' => $userId],
                ]
            ])
            ->first();

        if ($exists) {
            $this->Flash->warning('Relation déjà existante.');
            return $this->redirect(['action' => 'index']);
        }

        $friendship = $this->Friendships->newEntity([
            'user_id' => $userId,
            'friend_id' => $friendId,
            'status' => 'pending'
        ]);

        if ($this->Friendships->save($friendship)) {
            $this->loadModel('Notifications');
            $this->Notifications->save(
                $this->Notifications->newEntity([
                    'user_id' => $friendId,
                    'type' => 'friend_request',
                    'message' => 'Nouvelle demande d’ami'
                ])
            );

            $this->Flash->success('Demande envoyée.');
        } else {
            $this->Flash->error('Erreur lors de l’envoi.');
        }

        return $this->redirect(['action' => 'index']);
    }


    /**
     * Delete method
     *
     * @param string|null $id Friendship id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete()
    {
        $userId = $this->Authentication->getIdentity()->id;
        $friendId = (int)$this->request->getData('friend_id');


        $friendship = $this->Friendships->find()
            ->where([
                'OR' => [
                    ['user_id' => $userId, 'friend_id' => $friendId],
                    ['user_id' => $friendId, 'friend_id' => $userId],
                ]
            ])
            ->firstOrFail();
        if (
            $friendship->user_id !== $userId &&
            $friendship->friend_id !== $userId
        ) {
            throw new ForbiddenException();
        }

        $friendship->status = 'deleted';
        $this->Friendships->saveOrFail($friendship);

        $this->Flash->success('Ami supprimé.');
        return $this->redirect(['action' => 'index']);

    }

}
