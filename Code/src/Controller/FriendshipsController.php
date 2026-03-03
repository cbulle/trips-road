<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Response;

/**
 * Friendships Controller
 *
 * @property \App\Model\Table\FriendshipsTable $Friendships
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
        $identity = $this->request->getAttribute('identity');
        $userId = (int)$identity->getIdentifier();

        $search = (string)$this->request->getQuery('search');
        $users = [];
        $friends = [];

        // Recherche d'utilisateurs (Excluant l'utilisateur connecté)
        if (!empty($search)) {
            $users = $this->Friendships->Users->find()
                ->where([
                    'OR' => [
                        'Users.first_name LIKE' => '%' . $search . '%',
                        'Users.last_name LIKE' => '%' . $search . '%',
                    ],
                    'Users.id !=' => $userId
                ])
                ->limit(20)
                ->all();
        }

        // Récupération des amitiés acceptées
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

        // Formatage de la liste d'amis
        foreach ($friendships as $friendship) {
            if ($friendship->user_id === $userId && !empty($friendship->friends_user)) {
                $friends[] = [
                    'friend' => $friendship->friends_user,
                    'friendship_id' => $friendship->id
                ];
            } elseif ($friendship->friend_id === $userId && !empty($friendship->user)) {
                $friends[] = [
                    'friend' => $friendship->user,
                    'friendship_id' => $friendship->id
                ];
            }
        }

        // Demandes d'amis en attente
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
     */
    public function view($id = null)
    {
        $identity = $this->request->getAttribute('identity');
        $userId = (int)$identity->getIdentifier();

        $friendship = $this->Friendships->get($id, [
            'contain' => ['Users', 'FriendsUsers'],
        ]);

        // Sécurité : Seuls les deux concernés peuvent voir l'amitié
        if ($friendship->user_id !== $userId && $friendship->friend_id !== $userId) {
            throw new ForbiddenException(__('Vous n’avez pas accès à cette ressource.'));
        }

        $this->set(compact('friendship'));
    }

    /**
     * Add method
     *
     * @param string|null $friendId Friend user id.
     * @return \Cake\Http\Response|null Redirects on success.
     */
    public function add($friendId = null): ?Response
    {
        $this->request->allowMethod(['post']);
        
        $userId = (int)$this->Authentication->getIdentity()->getIdentifier();
        $friendId = (int)$friendId;

        if ($userId === $friendId) {
            $this->Flash->error(__('Action invalide : vous ne pouvez pas vous ajouter vous-même.'));
            return $this->redirect(['action' => 'index']);
        }

        if (!$this->Friendships->Users->exists(['id' => $friendId])) {
            $this->Flash->error(__('Utilisateur introuvable.'));
            return $this->redirect(['action' => 'index']);
        }

        // Vérification de l'existence d'une relation
        $friendship = $this->Friendships->find()
            ->where([
                'OR' => [
                    ['user_id' => $userId, 'friend_id' => $friendId],
                    ['user_id' => $friendId, 'friend_id' => $userId],
                ]
            ])
            ->first();

        if ($friendship) {
            switch ($friendship->status) {
                case 'accepted':
                    $this->Flash->warning(__('Vous êtes déjà amis.'));
                    break;
                case 'pending':
                    $this->Flash->warning(__('Une demande est déjà en attente.'));
                    break;
                case 'deleted':
                    $friendship = $this->Friendships->patchEntity($friendship, [
                        'user_id' => $userId,
                        'friend_id' => $friendId,
                        'status' => 'pending'
                    ]);
                    $this->Friendships->save($friendship);
                    $this->Flash->success(__('Nouvelle demande d’ami envoyée.'));
                    break;
            }
            return $this->redirect(['action' => 'index']);
        }

        // Création d'une nouvelle demande
        $newFriendship = $this->Friendships->newEntity([
            'user_id' => $userId,
            'friend_id' => $friendId,
            'status' => 'pending'
        ]);

        if ($this->Friendships->save($newFriendship)) {
            $this->Flash->success(__('Demande d’ami envoyée.'));
        } else {
            $this->Flash->error(__('Erreur lors de l’envoi de la demande.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Accept method
     *
     * @param string|null $id Friendship id.
     * @return \Cake\Http\Response|null Redirects to index.
     */
    public function accept($id = null): ?Response
    {
        $this->request->allowMethod(['post', 'get']);
        $userId = (int)$this->Authentication->getIdentity()->getIdentifier();

        $friendship = $this->Friendships->find()
            ->where([
                'id' => $id,
                'friend_id' => $userId, // Sécurité : seul le destinataire peut accepter
                'status' => 'pending'
            ])
            ->first();

        if (!$friendship) {
            $this->Flash->error(__('Demande introuvable ou déjà traitée.'));
            return $this->redirect(['action' => 'index']);
        }

        $friendship->status = 'accepted';

        if ($this->Friendships->save($friendship)) {
            $this->Flash->success(__('Demande d’ami acceptée.'));
        } else {
            $this->Flash->error(__('Erreur lors de l’acceptation.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Reject method
     *
     * @param string|null $id Friendship id.
     * @return \Cake\Http\Response|null Redirects to index.
     */
    public function reject($id = null): ?Response
    {
        $this->request->allowMethod(['post', 'get']);
        $userId = (int)$this->Authentication->getIdentity()->getIdentifier();

        $friendship = $this->Friendships->find()
            ->where([
                'id' => $id,
                'friend_id' => $userId, // Sécurité : seul le destinataire peut refuser
                'status' => 'pending'
            ])
            ->first();

        if (!$friendship) {
            $this->Flash->error(__('Demande introuvable.'));
            return $this->redirect(['action' => 'index']);
        }

        if ($this->Friendships->delete($friendship)) {
            $this->Flash->success(__('Demande d’ami refusée.'));
        } else {
            $this->Flash->error(__('Erreur lors du refus.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Friendship id.
     * @return \Cake\Http\Response|null Redirects to index.
     */
    public function delete($id = null): ?Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $userId = (int)$this->Authentication->getIdentity()->getIdentifier();

        $friendship = $this->Friendships->get($id);

        // Sécurité : On vérifie que l'utilisateur fait partie de la relation
        if ($friendship->user_id !== $userId && $friendship->friend_id !== $userId) {
            throw new ForbiddenException(__('Action non autorisée.'));
        }

        $friendship->status = 'deleted';
        
        if ($this->Friendships->save($friendship)) {
            $this->Flash->success(__('Ami supprimé de votre liste.'));
        } else {
            $this->Flash->error(__('Erreur lors de la suppression.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}