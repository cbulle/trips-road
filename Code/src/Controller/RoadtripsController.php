<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;
use Cake\I18n\FrozenTime;
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

    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->Authentication->addUnauthenticatedActions(['index', 'publicRoadtrips', 'view']);
    }

    public function index()
    {
        $identity = $this->request->getAttribute('identity');
        $userId = $identity ? $identity->getIdentifier() : null;

        $user = null;
        if ($userId) {
            $user = $this->fetchTable('Users')->get($userId);
        }

        $this->paginate = [
            'limit' => 12,
            'order' => ['Roadtrips.id' => 'DESC']
        ];

        $query = $this->Roadtrips->find()
            ->contain(['Users'])
            ->where(['visibility' => 'public'])
            ->order(['Roadtrips.id' => 'DESC']);

        $roadtrips = $this->paginate($query);

        $randomRoadtrips = $this->Roadtrips->find()
            ->contain(['Users'])
            ->where([
                'visibility' => 'public',
                'status' => 'completed'
            ])
            ->order('RAND()')
            ->limit(3)
            ->all();

        $favorisIds = [];
        if ($userId) {
            try {
                $favoritesTable = $this->fetchTable('Favorites');
                $favorisIds = $favoritesTable->find()
                    ->select(['roadtrip_id'])
                    ->where(['user_id' => $userId])
                    ->all()
                    ->extract('roadtrip_id')
                    ->toArray();
            } catch (\Exception $e) {
                $favorisIds = [];
            }
        }

        $this->set(compact('roadtrips', 'randomRoadtrips', 'favorisIds', 'userId', 'user'));
    }

    public function getLieuxFavoris()
    {
        $this->request->allowMethod(['get', 'ajax']);

        $this->viewBuilder()->disableAutoLayout();

        $userId = $this->request->getAttribute('identity')?->getIdentifier();
        $response = [];

        if ($userId) {
            try {
                $favoritesTable = $this->fetchTable('FavoritePlaces');

                $favorites = $favoritesTable->find()
                    ->where(['user_id' => $userId])
                    ->all();

                foreach ($favorites as $fav) {
                    $response[] = [
                        'nom_lieu'  => $fav->nom_lieu ?? $fav->name,
                        'adresse'   => $fav->adresse ?? '',
                        'latitude'  => $fav->latitude,
                        'longitude' => $fav->longitude,
                        'categorie' => $fav->categorie ?? 'divers'
                    ];
                }
            } catch (\Exception $e) {
                \Cake\Log\Log::error("Erreur récupération favoris : " . $e->getMessage());
                $response = [];
            }
        }

        return $this->response
            ->withType('application/json')
            ->withStringBody(json_encode($response));
    }

    public function add()
    {
        $roadtrip = $this->Roadtrips->newEmptyEntity();

        $modeEdition = false;
        $existingTrajets = [];
        $userDefaultCity = $this->request->getSession()->read('Auth.ville') ?? '';

        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $data['user_id'] = $this->request->getAttribute('identity')->getIdentifier();

            $photo = $this->request->getData('photo_cover');
            if ($photo instanceof \Laminas\Diactoros\UploadedFile && $photo->getError() === UPLOAD_ERR_OK) {
                $ext = pathinfo($photo->getClientFilename(), PATHINFO_EXTENSION);
                $newName = 'rt_' . uniqid() . '.' . $ext;
                $destination = WWW_ROOT . 'uploads/roadtrips/' . $newName;

                if (!file_exists(dirname($destination))) {
                    mkdir(dirname($destination), 0777, true);
                }

                $photo->moveTo($destination);
                $data['photo_url'] = $newName;
            }

            $jsonTrajets = '[]';
            $trajetsFile = $this->request->getData('trajets_file');

            if ($trajetsFile instanceof \Laminas\Diactoros\UploadedFile && $trajetsFile->getError() === UPLOAD_ERR_OK) {
                $jsonTrajets = file_get_contents($trajetsFile->getStream()->getMetadata('uri'));
            } elseif (!empty($data['trajets'])) {
                $jsonTrajets = $data['trajets'];
            }

            $data['trips'] = $this->_mapJsonToCakeEntities($jsonTrajets);

            $roadtrip = $this->Roadtrips->patchEntity($roadtrip, $data, [
                'associated' => ['Trips.SubSteps']
            ]);

            try {
                if ($this->Roadtrips->save($roadtrip)) {

                    if ($this->request->is(['ajax', 'json'])) {
                        return $this->response
                            ->withType('application/json')
                            ->withStringBody(json_encode([
                                'success' => true,
                                'id' => $roadtrip->id,
                                'message' => 'Roadtrip créé avec succès !'
                            ]));
                    }

                    $this->Flash->success(__('Roadtrip sauvegardé !'));
                    return $this->redirect(['action' => 'myRoadtrips']);
                }

                if ($this->request->is(['ajax', 'json'])) {
                    return $this->response
                        ->withType('application/json')
                        ->withStatus(400)
                        ->withStringBody(json_encode([
                            'success' => false,
                            'message' => 'Erreur de validation (Champs invalides)',
                            'details' => $roadtrip->getErrors()
                        ]));
                }

            } catch (\Exception $e) {
                \Cake\Log\Log::error("Crash Save Roadtrip : " . $e->getMessage());

                if ($this->request->is(['ajax', 'json'])) {
                    return $this->response
                        ->withType('application/json')
                        ->withStatus(500)
                        ->withStringBody(json_encode([
                            'success' => false,
                            'message' => 'Erreur Critique Serveur (Exception)',
                            'error_debug' => $e->getMessage(), // Le message technique
                            'file' => $e->getFile(),
                            'line' => $e->getLine()
                        ]));
                }
            }
            $this->Flash->error(__('Impossible de sauvegarder le roadtrip.'));
        }

        $this->set(compact('roadtrip', 'modeEdition', 'existingTrajets', 'userDefaultCity'));
        return $this->render('form');
    }

    public function edit($id = null)
    {
        $userId = $this->request->getAttribute('identity')->getIdentifier();

        try {
            $roadtrip = $this->Roadtrips->get($id, [
                'contain' => ['Trips' => ['SubSteps']]
            ]);
        } catch (\Exception $e) {
            $this->Flash->error('Roadtrip introuvable.');
            return $this->redirect(['action' => 'index']);
        }

        if ($roadtrip->user_id !== $userId) {
            $this->Flash->error('Vous n\'avez pas le droit de modifier ce roadtrip.');
            return $this->redirect(['action' => 'index']);
        }

        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();

            $photo = $this->request->getData('photo_cover');
            if ($photo instanceof \Laminas\Diactoros\UploadedFile && $photo->getError() === UPLOAD_ERR_OK) {
                $ext = pathinfo($photo->getClientFilename(), PATHINFO_EXTENSION);
                $newName = 'rt_' . uniqid() . '.' . $ext;
                $destination = WWW_ROOT . 'uploads/roadtrips/' . $newName;
                $photo->moveTo($destination);
                $data['photo_url'] = $newName;
            }
            $trajetsFile = [];
            $trajetsFile = $this->request->getData('trajets_file');

            if ($trajetsFile instanceof \Laminas\Diactoros\UploadedFile && $trajetsFile->getError() === UPLOAD_ERR_OK) {
                $jsonTrajets = file_get_contents($trajetsFile->getStream()->getMetadata('uri'));

                $this->Roadtrips->Trips->deleteAll(['roadtrip_id' => $roadtrip->id]);

                $data['trips'] = $this->_mapJsonToCakeEntities($jsonTrajets);
            }

            $roadtrip = $this->Roadtrips->patchEntity($roadtrip, $data, [
                'associated' => ['Trips.SubSteps']
            ]);

            try {
                if ($this->Roadtrips->save($roadtrip)) {

                    if ($this->request->is(['ajax', 'json'])) {
                        return $this->response
                            ->withType('application/json')
                            ->withStringBody(json_encode([
                                'success' => true,
                                'id' => $roadtrip->id,
                                'message' => 'Roadtrip créé avec succès !'
                            ]));
                    }

                    $this->Flash->success(__('Roadtrip sauvegardé !'));
                    return $this->redirect(['action' => 'myRoadtrips']);
                }

                if ($this->request->is(['ajax', 'json'])) {
                    return $this->response
                        ->withType('application/json')
                        ->withStatus(400)
                        ->withStringBody(json_encode([
                            'success' => false,
                            'message' => 'Erreur de validation (Champs invalides)',
                            'details' => $roadtrip->getErrors()
                        ]));
                }

            } catch (\Exception $e) {
                \Cake\Log\Log::error("Crash Save Roadtrip : " . $e->getMessage());

                if ($this->request->is(['ajax', 'json'])) {
                    return $this->response
                        ->withType('application/json')
                        ->withStatus(500)
                        ->withStringBody(json_encode([
                            'success' => false,
                            'message' => 'Erreur Critique Serveur (Exception)',
                            'error_debug' => $e->getMessage(), // Le message technique
                            'file' => $e->getFile(),
                            'line' => $e->getLine()
                        ]));
                }
            }
            $this->Flash->error(__('Erreur lors de la modification.'));
        }

        $modeEdition = true;
        $existingTrajets = $this->_formatTripsForJs($roadtrip->trips); // Assure-toi d'avoir cette méthode helper
        $userDefaultCity = $this->request->getSession()->read('Auth.ville') ?? '';

        $this->set(compact('roadtrip', 'modeEdition', 'existingTrajets', 'userDefaultCity'));
        return $this->render('form');
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

    public function myRoadtrips()
    {
        $userId = $this->request->getAttribute('identity')->getIdentifier();

        $user = $this->fetchTable('Users')->get($userId);

        $show_share = $this->request->getQuery('show_share');
        $share_url = $this->request->getSession()->read('share_url');

        $this->paginate = [
            'order' => ['id' => 'DESC']
        ];

        $query = $this->Roadtrips->find()
            ->where(['user_id' => $userId]);

        $roadtrips = $this->paginate($query);

        $this->set(compact('roadtrips', 'show_share', 'share_url', 'user'));

        $this->render('my_roadtrips');
    }

    public function publicRoadtrips()
    {
        $identity = $this->request->getAttribute('identity');
        $userId = $identity ? $identity->getIdentifier() : null;

        $user = null;
        if ($userId) {
            $user = $this->fetchTable('Users')->get($userId);
        }

        $this->paginate = [
            'order' => ['id' => 'DESC']
        ];

        $query = $this->Roadtrips->find()
            ->contain(['Users'])
            ->where(['visibility' => 'public'])
            ->order(['Roadtrips.id' => 'DESC']);

        $roadtrips = $this->paginate($query);

        $favorisIds = [];
        if ($userId) {
            try {
                $favoritesTable = $this->fetchTable('Favorites');
                $favorisIds = $favoritesTable->find()
                    ->select(['roadtrip_id'])
                    ->where(['user_id' => $userId])
                    ->all()
                    ->extract('roadtrip_id')
                    ->toArray();
            } catch (\Exception $e) {
                $favorisIds = [];
            }
        }

        $this->set(compact('roadtrips', 'favorisIds', 'userId', 'user'));
    }


    public function share($id = null)
    {
        $token = md5((string)$id . uniqid());

        $link = \Cake\Routing\Router::url(['controller' => 'Roadtrips', 'action' => 'view', 'token' => $token], true);

        $this->request->getSession()->write('share_url', $link);

        return $this->redirect(['action' => 'myRoadtrips', '?' => ['show_share' => 1]]);
    }

    public function view($id = null)
    {
        try {
            $roadtrip = $this->Roadtrips->get($id, [
                'contain' => [
                    'Users',
                    'Trips' => [
                        'sort' => ['Trips.order_number' => 'ASC'],
                        'SubSteps' => [
                            'sort' => ['SubSteps.order_number' => 'ASC'],
                            'SubStepPhotos'
                        ]
                    ]
                ]
            ]);
        } catch (\Cake\Datasource\Exception\RecordNotFoundException $e) {
            $this->Flash->error(__('Road trip introuvable.'));
            return $this->redirect(['action' => 'index']);
        }

        $currentUserId = $this->request->getAttribute('identity')?->getIdentifier();
        $isOwner = ($currentUserId === $roadtrip->user_id);

        if (!$isOwner && $roadtrip->visibility !== 'public') {
            $this->Flash->error(__('Ce road trip est privé.'));
            return $this->redirect(['action' => 'index']); // ou explorePublic
        }

        $geocodedPlacesTable = $this->fetchTable('GeocodedPlaces');
        $jsMapData = [];

        foreach ($roadtrip->trips as $trip) {

            $coordsDep = $this->_getCoordinates($trip->departure, $geocodedPlacesTable);
            $coordsArr = $this->_getCoordinates($trip->arrival, $geocodedPlacesTable);

            $sousEtapesCoords = [];
            foreach ($trip->sub_steps as $se) {
                if (!empty($se->city)) {
                    $coords = $this->_getCoordinates($se->city, $geocodedPlacesTable);
                    if ($coords) {
                        $sousEtapesCoords[] = [
                            'lat' => $coords['lat'],
                            'lon' => $coords['lon'],
                            'nom' => $se->city,
                            'heure' => $se->duration ? $se->duration->format('H:i') : '',
                            'description' => $se->description,
                            'photos' => $se->sub_step_photos
                        ];
                    }
                }
            }

            $jsMapData[] = [
                'id' => $trip->id,
                'titre' => $trip->title,
                'mode' => strtolower($trip->transport_mode),
                'depart' => [
                    'lat' => $coordsDep['lat'] ?? null,
                    'lon' => $coordsDep['lon'] ?? null,
                    'nom' => $trip->departure
                ],
                'arrivee' => [
                    'lat' => $coordsArr['lat'] ?? null,
                    'lon' => $coordsArr['lon'] ?? null,
                    'nom' => $trip->arrival
                ],
                'heure_depart' => $trip->departure_time ? $trip->departure_time->format('H:i') : null,
                'sousEtapes' => $sousEtapesCoords,
                'hasCoords' => ($coordsDep && $coordsArr)
            ];
        }

        $jsMapDataJson = json_encode($jsMapData);

        $this->set(compact('roadtrip', 'jsMapDataJson', 'isOwner'));
    }

    private function _formatTripsForJs($trips)
    {
        $data = [];
        foreach ($trips as $trip) {
            $sousEtapes = [];
            foreach ($trip->sub_steps as $step) {

                $heureFormattee = '';
                if (!empty($step->duration)) {
                    if ($step->duration instanceof \DateTimeInterface) {
                        $heureFormattee = $step->duration->format('H:i');
                    }
                    elseif (is_string($step->duration)) {
                        $heureFormattee = substr($step->duration, 0, 5); // "02:30:00" -> "02:30"
                    }
                }

                $sousEtapes[] = [
                    'nom' => $step->city,
                    'heure' => $heureFormattee,
                    'remarque' => $step->description,

                    'coords' => [
                        (float)($step->latitude ?? 0),
                        (float)($step->longitude ?? 0)
                    ]
                ];
            }

            $data[] = [
                'id' => $trip->id,
                'depart' => $trip->departure,
                'arrivee' => $trip->arrival,
                'mode' => $trip->transport_mode,

                'date_trajet' => $trip->departure_time ? $trip->departure_time->format('Y-m-d') : null,
                'heure_depart' => $trip->departure_time ? $trip->departure_time->format('H:i') : '08:00',

                'sousEtapes' => $sousEtapes
            ];
        }
        return $data;
    }
    protected function _getCoordinates($nomVille, $table)
    {
        if (empty($nomVille)) return null;

        $cleanName = trim($nomVille);

        $place = $table->find()
            ->where(['name' => $cleanName])
            ->first();

        if ($place) {
             \Cake\Log\Log::debug("Found in cache: " . $cleanName);

            return [
                'lat' => $place->latitude,
                'lon' => $place->longitude
            ];
        }

        try {
            $http = new \Cake\Http\Client();
            $response = $http->get('https://nominatim.openstreetmap.org/search', [
                'q' => $cleanName,
                'format' => 'json',
                'limit' => 1,
                'accept-language' => 'fr'
            ], [
                'headers' => ['User-Agent' => 'SaeRoadTripApp_PublicView']
            ]);

            if ($response->isOk()) {
                $json = $response->getJson();
                if (!empty($json) && isset($json[0]['lat'])) {
                    $lat = $json[0]['lat'];
                    $lon = $json[0]['lon'];

                    $newPlace = $table->newEmptyEntity();
                    $newPlace->name = $cleanName;
                    $newPlace->latitude = $lat;
                    $newPlace->longitude = $lon;

                    $newPlace->last_used = \Cake\I18n\FrozenTime::now();

                    $table->save($newPlace);

                    return ['lat' => $lat, 'lon' => $lon];
                }
            }
        } catch (\Exception $e) {
            \Cake\Log\Log::error("Erreur Geocoding API: " . $e->getMessage());
            return null;
        }

        return null;
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

    /**
     * Affiche l'historique de l'utilisateur
     */
    public function historique()
    {
        // CORRECTION : On utilise fetchTable au lieu de loadModel
        $historiqueTable = $this->fetchTable('Historique');

        // Récupérer l'ID de l'utilisateur connecté
        $userId = $this->request->getAttribute('identity')->getIdentifier();

        // Faire la requête sur la table récupérée
        $query = $historiqueTable->find()
            ->contain([
                'Roadtrips' => [
                    'Users' 
                ]
            ])
            ->where(['Historique.user_id' => $userId])
            // Vérifie bien si ta colonne de date s'appelle 'date_visite' ou 'created'
            ->order(['Historique.date_visite' => 'DESC']); 

        try {
            $historique = $this->paginate($query, ['limit' => 12]);
        } catch (\Exception $e) {
            return $this->redirect(['action' => 'historique']);
        }

        $this->set(compact('historique'));
    }

    /**
     * Action pour le bouton "Tout effacer"
     */
    public function deleteHistorique()
    {
        $this->request->allowMethod(['post', 'delete']);
        
        // CORRECTION : On utilise fetchTable ici aussi
        $historiqueTable = $this->fetchTable('Historique');
        
        $userId = $this->request->getAttribute('identity')->getIdentifier();

        // Suppression via la table récupérée
        $historiqueTable->deleteAll(['user_id' => $userId]);

        $this->Flash->success('Votre historique a été vidé.');

        return $this->redirect(['action' => 'historique']);
    }
}
