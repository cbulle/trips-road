<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * Favorites Controller
 *
 * @property \App\Model\Table\FavoritesTable $Favorites
 */
class FavoritesController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->Favorites->find()
            ->contain(['Users', 'Roadtrips']);
        $favorites = $this->paginate($query);

        $this->set(compact('favorites'));
    }

    /**
     * View method
     *
     * @param string|null $id Favorite id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $favorite = $this->Favorites->get($id, contain: ['Users', 'Roadtrips']);
        $this->set(compact('favorite'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $favorite = $this->Favorites->newEmptyEntity();
        if ($this->request->is('post')) {
            $favorite = $this->Favorites->patchEntity($favorite, $this->request->getData());
            if ($this->Favorites->save($favorite)) {
                $this->Flash->success(__('The favorite has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The favorite could not be saved. Please, try again.'));
        }
        $users = $this->Favorites->Users->find('list', limit: 200)->all();
        $roadtrips = $this->Favorites->Roadtrips->find('list', limit: 200)->all();
        $this->set(compact('favorite', 'users', 'roadtrips'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Favorite id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $favorite = $this->Favorites->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $favorite = $this->Favorites->patchEntity($favorite, $this->request->getData());
            if ($this->Favorites->save($favorite)) {
                $this->Flash->success(__('The favorite has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The favorite could not be saved. Please, try again.'));
        }
        $users = $this->Favorites->Users->find('list', limit: 200)->all();
        $roadtrips = $this->Favorites->Roadtrips->find('list', limit: 200)->all();
        $this->set(compact('favorite', 'users', 'roadtrips'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Favorite id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $favorite = $this->Favorites->get($id);
        if ($this->Favorites->delete($favorite)) {
            $this->Flash->success(__('The favorite has been deleted.'));
        } else {
            $this->Flash->error(__('The favorite could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
