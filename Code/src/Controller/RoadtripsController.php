<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;
use Cake\View\JsonView;

/**
 * Roadtrips Controller
 *
 * @property \App\Model\Table\RoadtripsTable $Roadtrips
 */
class RoadtripsController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();

    }

    /**
     * Index method
     */
    public function index()
    {
        $this->paginate = [
            'limit' => 12,
        ];

        $query = $this->Roadtrips->find()
            ->contain(['Users'])
            ->where(['visibility' => 'public'])
            ->order(['Roadtrips.created' => 'DESC']);

        $roadtrips = $this->paginate($query);

        $this->set(compact('roadtrips'));
    }

    /**
     * Mes Road Trips
     */
    public function myRoadtrips()
    {
        $userId = $this->request->getAttribute('identity')->getIdentifier();

        $this->paginate = [
            'limit' => 12,
            'order' => ['created' => 'DESC']
        ];

        $query = $this->Roadtrips->find()
            ->where(['user_id' => $userId]);

        $roadtrips = $this->paginate($query);

        $this->set(compact('roadtrips'));
        $this->render('my_roadtrips');
    }

    /**
     * View method
     */
    public function view($id = null)
    {
        $roadtrip = $this->Roadtrips->get($id, [
            'contain' => [
                'Users',
                'Trips' => ['SubSteps'],
                'Comments' => ['Users'],
                'PointsOfInterests'
            ]
        ]);

        $this->set(compact('roadtrip'));
    }

    /**
     * Add method
     */
    public function add()
    {
        $roadtrip = $this->Roadtrips->newEmptyEntity();

        if ($this->request->is('post')) {
            $data = $this->request->getData();

            $data['user_id'] = $this->request->getAttribute('identity')->getIdentifier();

            $photo = $this->request->getData('photo_cover');
            if ($photo instanceof \Laminas\Diactoros\UploadedFile && $photo->getError() === UPLOAD_ERR_OK) {
                $ext = pathinfo($photo->getClientFilename(), PATHINFO_EXTENSION);
                $newName = 'rt_' . uniqid() . '.' . $ext;
                $photo->moveTo(WWW_ROOT . 'uploads/roadtrips/' . $newName);
                $data['photo_url'] = $newName; // Le nom dans la BD
            }

            $jsonTrajets = '[]';
            $trajetsFile = $this->request->getData('trajets_file');

            if ($trajetsFile instanceof \Laminas\Diactoros\UploadedFile && $trajetsFile->getError() === UPLOAD_ERR_OK) {
                $jsonTrajets = file_get_contents($trajetsFile->getStream()->getMetadata('uri'));
            }
            elseif (!empty($data['trajets'])) {
                $jsonTrajets = $data['trajets'];
            }

            $data['trips'] = $this->_mapJsonToCakeEntities($jsonTrajets);

            $roadtrip = $this->Roadtrips->patchEntity($roadtrip, $data, [
                'associated' => ['Trips.SubSteps']
            ]);

            if ($this->Roadtrips->save($roadtrip)) {
                if ($this->request->is('json') || $this->request->is('ajax')) {
                    return $this->response->withType('application/json')
                        ->withStringBody(json_encode(['success' => true, 'id' => $roadtrip->id]));
                }

                $this->Flash->success(__('Roadtrip sauvegardé !'));
                return $this->redirect(['action' => 'myRoadtrips']);
            }

            if ($this->request->is('json') || $this->request->is('ajax')) {
                return $this->response->withType('application/json')
                    ->withStatus(400)
                    ->withStringBody(json_encode(['error' => 'Erreur de sauvegarde', 'details' => $roadtrip->getErrors()]));
            }
            $this->Flash->error(__('Impossible de sauvegarder le roadtrip.'));
        }

        $this->set(compact('roadtrip'));
    }

    /**
     * Edit method (Remplace form_modif.php)
     */
    public function edit($id = null)
    {
        $userId = $this->request->getAttribute('identity')->getIdentifier();

        $roadtrip = $this->Roadtrips->get($id, [
            'contain' => ['Trips' => ['SubSteps']]
        ]);

        if ($roadtrip->user_id !== $userId) {
            $this->Flash->error('Vous n\'avez pas le droit de modifier ce roadtrip.');
            return $this->redirect(['action' => 'index']);
        }

        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();

            if (!empty($data['trajets'])) {
                $this->Roadtrips->Trips->deleteAll(['roadtrip_id' => $roadtrip->id]);
                $data['trips'] = $this->_mapJsonToCakeEntities($data['trajets']);
            }

            $roadtrip = $this->Roadtrips->patchEntity($roadtrip, $data, [
                'associated' => ['Trips.SubSteps']
            ]);

            if ($this->Roadtrips->save($roadtrip)) {
                $this->Flash->success(__('Modifications enregistrées.'));
                return $this->redirect(['action' => 'myRoadtrips']);
            }
            $this->Flash->error(__('Erreur lors de la modification.'));
        }
        $this->set(compact('roadtrip'));
    }

    /**
     * Delete method
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $roadtrip = $this->Roadtrips->get($id);

        $userId = $this->request->getAttribute('identity')->getIdentifier();
        if ($roadtrip->user_id !== $userId) {
            $this->Flash->error('Interdit.');
            return $this->redirect(['action' => 'index']);
        }

        if ($this->Roadtrips->delete($roadtrip)) {
            $this->Flash->success(__('Roadtrip supprimé.'));
        } else {
            $this->Flash->error(__('Erreur suppression.'));
        }

        return $this->redirect(['action' => 'myRoadtrips']);
    }

    private function _mapJsonToCakeEntities($jsonString)
    {
        $decoded = json_decode($jsonString, true);
        if (!$decoded) return [];

        $cakeTrips = [];

        foreach ($decoded as $index => $trajetJs) {
            $newTrajet = [
                'order_number' => $index + 1,
                'title' => ($trajetJs['depart'] ?? '') . ' -> ' . ($trajetJs['arrivee'] ?? ''),
                'departure' => $trajetJs['depart'] ?? '',
                'arrival' => $trajetJs['arrivee'] ?? '',
                'transport_mode' => $trajetJs['mode'] ?? 'Voiture',
                'departure_time' => $trajetJs['heure_depart'] ?? '08:00',
                'sub_steps' => []
            ];

            if (!empty($trajetJs['sousEtapes'])) {
                foreach ($trajetJs['sousEtapes'] as $j => $se) {
                    $newTrajet['sub_steps'][] = [
                        'order_number' => $j + 1,
                        'city' => $se['nom'] ?? '',
                        'description' => $se['remarque'] ?? '',
                        'transport_type' => $trajetJs['mode'] ?? 'Voiture', // On reprend le mode du trajet parent ?
                        'heure' => $se['heure'] ?? null
                    ];
                }
            }

            $cakeTrips[] = $newTrajet;
        }

        return $cakeTrips;
    }
}
