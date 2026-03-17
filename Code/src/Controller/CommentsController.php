<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * Comments Controller
 *
 * @property \App\Model\Table\CommentsTable $Comments
 */
class CommentsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $identity = $this->request->getAttribute('identity');
        $query = $this->Comments->find()
            ->contain(['Users', 'Roadtrips', 'PointsOfInterests']);

        // Si l'utilisateur n'est PAS admin, on filtre uniquement ses commentaires
        if ($identity->get('role') !== 'admin') {
            $query->where(['Comments.user_id' => $identity->getIdentifier()]);
        }

        $comments = $this->paginate($query);
        $this->set(compact('comments'));
    }

    /**
     * View method
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
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */


    public function add()
    {
        $comment = $this->Comments->newEmptyEntity();
        if ($this->request->is('post')) {
            $comment = $this->Comments->patchEntity($comment, $this->request->getData());
            $comment->user_id = $this->request->getAttribute('identity')->getIdentifier();

            if ($this->Comments->save($comment)) {
                $this->Flash->success(__('Votre avis a été publié.'));
                return $this->redirect($this->referer());
            }
            $this->Flash->error(__('Erreur lors de la sauvegarde.'));
        }
        return $this->redirect($this->referer());
    }

    /**
     * Edit method
     *
     * @param string|null $id Comment id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $userId = $this->request->getAttribute('identity')->getIdentifier();

        $comment = $this->Comments->find()
            ->where(['id' => $id, 'user_id' => $userId])
            ->firstOrFail();

        if ($this->request->is(['patch', 'post', 'put'])) {
            $comment = $this->Comments->patchEntity($comment, $this->request->getData());
            if ($this->Comments->save($comment)) {
                $this->Flash->success(__('Le commentaire a été modifié.'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('Erreur lors de la sauvegarde.'));
        }
        $users = $this->Comments->Users->find('list', limit: 200)->all();
        $roadtrips = $this->Comments->Roadtrips->find('list', limit: 200)->all();
        $pointsOfInterests = $this->Comments->PointsOfInterests->find('list', limit: 200)->all();
        $this->set(compact('comment', 'users', 'roadtrips', 'pointsOfInterests'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Comment id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $userId = $this->request->getAttribute('identity')->getIdentifier();

        $comment = $this->Comments->find()
            ->where(['id' => $id, 'user_id' => $userId])
            ->firstOrFail();

        if ($this->Comments->delete($comment)) {
            $this->Flash->success(__('Commentaire supprimé.'));
        } else {
            $this->Flash->error(__('Erreur lors de la suppression.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
