<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;
use Cake\Datasource\Exception\RecordNotFoundException;

/**
 * Comments Controller
 * Manages user reviews and comments on roadtrips or points of interest.
 *
 * @property \App\Model\Table\CommentsTable $Comments
 */
class CommentsController extends AppController
{
    /**
     * Index method
     * Lists comments. Admins see all, regular users see only theirs.
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $identity = $this->request->getAttribute('identity');
        $query = $this->Comments->find()
            ->contain(['Users', 'Roadtrips', 'PointsOfInterests']);

        // If the user is NOT an admin, filter to show only their own comments
        if ($identity->get('role') !== 'admin') {
            $query->where(['Comments.user_id' => $identity->getIdentifier()]);
        }

        $comments = $this->paginate($query);
        $this->set(compact('comments'));
    }

    /**
     * View method
     * Displays a specific comment.
     *
     * @param string|null $id Comment id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found or not owned by user.
     */
    public function view($id = null)
    {
        $identity = $this->request->getAttribute('identity');
        $query = $this->Comments->find()
            ->contain(['Users', 'Roadtrips', 'PointsOfInterests'])
            ->where(['Comments.id' => $id]);

        if ($identity->get('role') !== 'admin') {
            $query->where(['Comments.user_id' => $identity->getIdentifier()]);
        }

        $comment = $query->firstOrFail();
        $this->set(compact('comment'));
    }

    /**
     * Add method
     * Creates a new comment and binds it securely to the current user.
     *
     * @return \Cake\Http\Response|null Redirects on successful add.
     */
    public function add(): ?Response
    {
        $comment = $this->Comments->newEmptyEntity();

        if ($this->request->is('post')) {
            $comment = $this->Comments->patchEntity($comment, $this->request->getData());

            // Security: Force the user_id to be the logged-in user
            $comment->user_id = $this->request->getAttribute('identity')->getIdentifier();

            if ($this->Comments->save($comment)) {
                $this->Flash->success(__('Votre avis a été publié.'));
            } else {
                $this->Flash->error(__('Erreur lors de la sauvegarde. Veuillez vérifier vos saisies.'));
            }
        }

        return $this->redirect($this->referer());
    }

    /**
     * Edit method
     * Modifies an existing comment owned by the user.
     *
     * @param string|null $id Comment id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $userId = $this->request->getAttribute('identity')->getIdentifier();

        // Ensure the user can only edit their own comment
        $comment = $this->Comments->find()
            ->where(['id' => $id, 'user_id' => $userId])
            ->firstOrFail();

        if ($this->request->is(['patch', 'post', 'put'])) {
            $comment = $this->Comments->patchEntity($comment, $this->request->getData());

            if ($this->Comments->save($comment)) {
                $this->Flash->success(__('Le commentaire a été modifié avec succès.'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('Erreur lors de la sauvegarde.'));
        }

        // Fetch related data for the dropdowns (no need for users list anymore)
        $roadtrips = $this->Comments->Roadtrips->find('list', ['limit' => 200])->all();
        $pointsOfInterests = $this->Comments->PointsOfInterests->find('list', ['limit' => 200])->all();

        $this->set(compact('comment', 'roadtrips', 'pointsOfInterests'));
    }

    /**
     * Delete method
     * Deletes a comment owned by the user.
     *
     * @param string|null $id Comment id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null): ?Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $userId = $this->request->getAttribute('identity')->getIdentifier();

        $comment = $this->Comments->find()
            ->where(['id' => $id, 'user_id' => $userId])
            ->firstOrFail();

        if ($this->Comments->delete($comment)) {
            $this->Flash->success(__('Commentaire supprimé définitivement.'));
        } else {
            $this->Flash->error(__('Erreur lors de la suppression.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
