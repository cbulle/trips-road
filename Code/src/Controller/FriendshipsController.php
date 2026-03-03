<?php

namespace App\Controller;

use Cake\Http\Exception\ForbiddenException;

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
        $friends = [];

        if (!empty($search)) {
            $users = $this->Friendships->Users->find()
                ->where([
                    'OR' => [
                        'Users.first_name LIKE' => "%$search%",
                        'Users.last_name LIKE' => "%$search%",
                    ],
                    'Users.id !=' => $userId
                ])
                ->limit(20)
                ->all();
        }

        $friendships = $this->Friendships->find()
            ->where([
                'status' => 'accepted',
                'OR' => [
                    ['user_id' => $userId],
                    ['friend_id' => $userId],
                ]
            ])
            ->contain(['Users', 'FriendsUsers'])
            ->all();

        foreach ($friendships as $f) {
            if ($f->user_id == $userId && !empty($f->friends_user)) {
                $friends[] = [
                    'friend' => $f->friends_user,
                    'friendship_id' => $f->id
                ];
            }
            if ($f->friend_id == $userId && !empty($f->user)) {
                $friends[] = [
                    'friend' => $f->user,
                    'friendship_id' => $f->id
                ];
            }
        }

        $requests = $this->Friendships->find()
            ->where([
                'friend_id' => $userId,
                'status' => 'pending'
            ])
            ->contain(['Users'])
            ->all();

        $this->set(compact('users', 'friends', 'requests', 'search', 'userId'));
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
    public function add($friendId = null)
    {
        $this->request->allowMethod(['post']);
        $userId = $this->Authentication->getIdentity()->getIdentifier();
        $friendId = (int)$friendId;

        if ($userId === $friendId) {
            $this->Flash->error('Action invalide.');
            return $this->redirect(['action' => 'index']);
        }

        if (!$this->Friendships->Users->exists(['id' => $friendId])) {
            $this->Flash->error('Utilisateur introuvable.');
            return $this->redirect(['action' => 'index']);
        }

        $friendship = $this->Friendships->find()
            ->where([
                'OR' => [
                    ['user_id' => $userId, 'friend_id' => $friendId],
                    ['user_id' => $friendId, 'friend_id' => $userId],
                ]
            ])
            ->first();

        if ($friendship) {
            if ($friendship->status === 'accepted') {
                $this->Flash->warning('Vous êtes déjà amis.');
                return $this->redirect(['action' => 'index']);
            }
            if ($friendship->status === 'pending') {
                $this->Flash->warning('Une demande est déjà en attente.');
                return $this->redirect(['action' => 'index']);
            }
            if ($friendship->status === 'deleted') {
                $friendship = $this->Friendships->patchEntity($friendship, [
                    'user_id' => $userId,
                    'friend_id' => $friendId,
                    'status' => 'pending'
                ]);
                $this->Friendships->save($friendship);
                $this->Flash->success('Nouvelle demande d’ami envoyée.');
                return $this->redirect(['action' => 'index']);
            }
        }

        $friendship = $this->Friendships->newEntity([
            'user_id' => $userId,
            'friend_id' => $friendId,
            'status' => 'pending'
        ]);

        if ($this->Friendships->save($friendship)) {
            $this->Flash->success('Demande d’ami envoyée.');
        } else {
            $this->Flash->error('Erreur lors de l’envoi.');
        }

        return $this->redirect(['action' => 'index']);
    }

    public function accept($id = null)
    {
        $this->request->allowMethod(['post', 'get']);

        $userId = $this->Authentication->getIdentity()->getIdentifier();

        $friendship = $this->Friendships->find()
            ->where([
                'id' => $id,
                'friend_id' => $userId,
                'status' => 'pending'
            ])
            ->first();

        if (!$friendship) {
            $this->Flash->error('Demande introuvable.');
            return $this->redirect(['action' => 'index']);
        }

        $friendship->status = 'accepted';

        if ($this->Friendships->save($friendship)) {
            $this->Flash->success('Demande d’ami acceptée.');
        } else {
            $this->Flash->error('Erreur lors de l’acceptation.');
        }

        return $this->redirect(['action' => 'index']);
    }
    public function reject($id = null)
    {
        $this->request->allowMethod(['post', 'get']);

        $userId = $this->Authentication->getIdentity()->getIdentifier();

        $friendship = $this->Friendships->find()
            ->where([
                'id' => $id,
                'friend_id' => $userId,
                'status' => 'pending'
            ])
            ->first();

        if (!$friendship) {
            $this->Flash->error('Demande introuvable.');
            return $this->redirect(['action' => 'index']);
        }

        if ($this->Friendships->delete($friendship)) {
            $this->Flash->success('Demande d’ami refusée.');
        } else {
            $this->Flash->error('Erreur lors du refus.');
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
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);

        $userId = $this->Authentication->getIdentity()->id;

        $friendship = $this->Friendships->get($id);

        if ($friendship->user_id !== $userId && $friendship->friend_id !== $userId) {
            throw new ForbiddenException();
        }

        $friendship->status = 'deleted';
        $this->Friendships->saveOrFail($friendship);

        $this->Flash->success('Ami supprimé.');
        return $this->redirect(['action' => 'index']);
    }





}
