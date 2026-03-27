<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;
use Cake\I18n\FrozenTime;
use Cake\View\JsonView;
use Cake\Http\Client;
use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Roadtrips Controller
 *
 * @property \App\Model\Table\RoadtripsTable $Roadtrips
 */
class RoadtripsController extends AppController
{
    /**
     * Initialization hook method.
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
    }

    /**
     * beforeFilter callback.
     * Allows unauthenticated users to access specific actions.
     *
     * @param \Cake\Event\EventInterface $event An Event instance
     * @return void
     */
    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->Authentication->addUnauthenticatedActions(['index', 'publicRoadtrips', 'view']);
    }

    /**
     * Index method
     * Displays public roadtrips and random featured roadtrips.
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $identity = $this->request->getAttribute('identity');
        $userId = $identity ? $identity->getIdentifier() : null;

        $user = null;
        if ($userId) {
            $user = $this->Roadtrips->Users->get($userId);
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
            ])
            ->order('RAND()')
            ->limit(3)
            ->all();

        $favoriteIds = [];
        if ($userId) {
            try {
                $favoriteIds = $this->Roadtrips->Favorites->find()
                    ->select(['roadtrip_id'])
                    ->where(['user_id' => $userId])
                    ->all()
                    ->extract('roadtrip_id')
                    ->toArray();
            } catch (\Exception $e) {
                $favoriteIds = [];
            }
        }
        $this->set(compact('roadtrips', 'randomRoadtrips', 'favoriteIds', 'userId', 'user'));
    }

    /**
     * Retrieves the connected user's favorite places for the map.
     *
     * @return \Cake\Http\Response JSON response containing favorite places
     */
    public function getFavoritePlaces()
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
                        'nom_lieu' => $fav->nom_lieu ?? $fav->name,
                        'adresse' => $fav->adresse ?? '',
                        'latitude' => $fav->latitude,
                        'longitude' => $fav->longitude,
                        'categorie' => $fav->categorie ?? 'divers'
                    ];
                }
            } catch (\Exception $e) {
                \Cake\Log\Log::error("Error fetching favorite places: " . $e->getMessage());
                $response = [];
            }
        }

        return $this->response
            ->withType('application/json')
            ->withStringBody(json_encode($response));
    }

    /**
     * Add method
     * Creates a new roadtrip.
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $roadtrip = $this->Roadtrips->newEmptyEntity();

        $isEditMode = false;
        $existingTrips = [];
        $userDefaultCity = $this->request->getSession()->read('Auth.ville') ?? '';

        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $data['user_id'] = $this->request->getAttribute('identity')->getIdentifier();

            $jsonTrips = '[]';
            $tripsFile = $this->request->getData('trajets_file');

            if ($tripsFile instanceof \Laminas\Diactoros\UploadedFile && $tripsFile->getError() === UPLOAD_ERR_OK) {
                $jsonTrips = file_get_contents($tripsFile->getStream()->getMetadata('uri'));
            } elseif (!empty($data['trajets'])) {
                $jsonTrips = $data['trajets'];
            }

            $data['trips'] = $this->_mapJsonToCakeEntities($jsonTrips);

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
                                'message' => 'Roadtrip successfully created!'
                            ]));
                    }

                    $this->Flash->success(__('Roadtrip saved successfully!'));
                    return $this->redirect(['action' => 'myRoadtrips']);
                }

                if ($this->request->is(['ajax', 'json'])) {
                    return $this->response
                        ->withType('application/json')
                        ->withStatus(400)
                        ->withStringBody(json_encode([
                            'success' => false,
                            'message' => 'Validation error (Invalid fields)',
                            'details' => $roadtrip->getErrors()
                        ]));
                }

            } catch (\Exception $e) {
                \Cake\Log\Log::error("Crash Save Roadtrip: " . $e->getMessage());

                if ($this->request->is(['ajax', 'json'])) {
                    return $this->response
                        ->withType('application/json')
                        ->withStatus(500)
                        ->withStringBody(json_encode([
                            'success' => false,
                            'message' => 'Critical Server Error (Exception)',
                            'error_debug' => $e->getMessage()
                        ]));
                }
            }
            $this->Flash->error(__('Unable to save the roadtrip.'));
        }
        $this->set(compact('roadtrip', 'isEditMode', 'existingTrips', 'userDefaultCity'));
        return $this->render('form');
    }

    /**
     * Edit method
     * Modifies an existing roadtrip.
     *
     * @param string|null $id Roadtrip id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     */
    public function edit($id = null)
    {
        $userId = $this->request->getAttribute('identity')->getIdentifier();

        try {
            $roadtrip = $this->Roadtrips->get($id, [
                'contain' => ['Trips' => ['SubSteps']]
            ]);
        } catch (\Exception $e) {
            $this->Flash->error('Roadtrip not found.');
            return $this->redirect(['action' => 'index']);
        }

        if ($roadtrip->user_id !== $userId) {
            $this->Flash->error('You are not allowed to edit this roadtrip.');
            return $this->redirect(['action' => 'index']);
        }

        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();

            $tripsFile = $this->request->getData('trajets_file');

            if ($tripsFile instanceof \Laminas\Diactoros\UploadedFile && $tripsFile->getError() === UPLOAD_ERR_OK) {
                $jsonTrips = file_get_contents($tripsFile->getStream()->getMetadata('uri'));
                $this->Roadtrips->Trips->deleteAll(['roadtrip_id' => $roadtrip->id]);
                $data['trips'] = $this->_mapJsonToCakeEntities($jsonTrips);
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
                                'message' => 'Roadtrip successfully updated!'
                            ]));
                    }

                    $this->Flash->success(__('Roadtrip saved successfully!'));
                    return $this->redirect(['action' => 'myRoadtrips']);
                }

                if ($this->request->is(['ajax', 'json'])) {
                    return $this->response
                        ->withType('application/json')
                        ->withStatus(400)
                        ->withStringBody(json_encode([
                            'success' => false,
                            'message' => 'Validation error (Invalid fields)',
                            'details' => $roadtrip->getErrors()
                        ]));
                }

            } catch (\Exception $e) {
                \Cake\Log\Log::error("Crash Update Roadtrip: " . $e->getMessage());

                if ($this->request->is(['ajax', 'json'])) {
                    return $this->response
                        ->withType('application/json')
                        ->withStatus(500)
                        ->withStringBody(json_encode([
                            'success' => false,
                            'message' => 'Critical Server Error (Exception)'
                        ]));
                }
            }
            $this->Flash->error(__('Error while updating the roadtrip.'));
        }

        $isEditMode = true;
        $existingTrips = $this->_formatTripsForJs($roadtrip->trips);
        $userDefaultCity = $this->request->getSession()->read('Auth.ville') ?? '';

        $this->set(compact('roadtrip', 'isEditMode', 'existingTrips', 'userDefaultCity'));
        return $this->render('form');
    }

    /**
     * Delete method
     *
     * @param string|null $id Roadtrip id.
     * @return \Cake\Http\Response|null Redirects to myRoadtrips.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $roadtrip = $this->Roadtrips->get($id);

        $currentUser = $this->request->getAttribute('identity');
        $userId = $currentUser->getIdentifier();
        $userRole = $currentUser->role ?? 'user';

        if ($roadtrip->user_id !== $userId && $userRole !== 'admin') {
            $this->Flash->error(__('Unauthorized action. You do not have permission.'));
            return $this->redirect(['action' => 'index']);
        }

        if ($this->Roadtrips->delete($roadtrip)) {
            $this->Flash->success(__('The roadtrip has been deleted successfully.'));
        } else {
            $this->Flash->error(__('Error while deleting the roadtrip.'));
        }

        return $this->redirect($this->referer(['action' => 'myRoadtrips']));
    }

    /**
     * Displays the connected user's roadtrips.
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function myRoadtrips()
    {
        $userId = $this->request->getAttribute('identity')->getIdentifier();
        $user = $this->Roadtrips->Users->get($userId);

        $showShare = $this->request->getQuery('show_share');
        $shareUrl = $this->request->getSession()->read('share_url');

        $this->paginate = [
            'order' => ['id' => 'DESC']
        ];

        $query = $this->Roadtrips->find()
            ->where(['user_id' => $userId]);

        $roadtrips = $this->paginate($query);

        $this->set(compact('roadtrips', 'showShare', 'shareUrl', 'user'));
        $this->render('my_roadtrips');
    }

    /**
     * Displays all public roadtrips.
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function publicRoadtrips()
    {
        $identity = $this->request->getAttribute('identity');
        $userId = $identity ? $identity->getIdentifier() : null;

        $user = null;
        if ($userId) {
            $user = $this->Roadtrips->Users->get($userId);
        }

        $this->paginate = [
            'order' => ['Roadtrips.id' => 'DESC']
        ];

        $query = $this->Roadtrips->find()
            ->contain([
                'Users',
                'Comments' => [
                    'Users',
                    'sort' => ['Comments.created' => 'DESC']
                ]
            ])
            ->where(['visibility' => 'public'])
            ->order(['Roadtrips.id' => 'DESC']);

        $roadtrips = $this->paginate($query);

        $newComment = $this->Roadtrips->Comments->newEmptyEntity();

        $favoriteIds = [];
        if ($userId) {
            try {
                $favoriteIds = $this->Roadtrips->Favorites->find()
                    ->select(['roadtrip_id'])
                    ->where(['user_id' => $userId])
                    ->all()
                    ->extract('roadtrip_id')
                    ->toArray();
            } catch (\Exception $e) {
                $favoriteIds = [];
            }
        }

        $this->set(compact('roadtrips', 'newComment', 'favoriteIds', 'userId', 'user'));
    }

    /**
     * Generates a sharing link for a roadtrip.
     *
     * @param string|null $id Roadtrip id.
     * @return \Cake\Http\Response|null Redirects back to myRoadtrips with share url.
     */
    public function share($id = null)
    {
        $token = md5((string)$id . uniqid());
        $link = \Cake\Routing\Router::url(['controller' => 'Roadtrips', 'action' => 'view', 'token' => $token], true);

        $this->request->getSession()->write('share_url', $link);

        return $this->redirect(['action' => 'myRoadtrips', '?' => ['show_share' => 1]]);
    }

    /**
     * View method
     * Displays details of a specific roadtrip.
     *
     * @param string|null $id Roadtrip id.
     * @return \Cake\Http\Response|null|void Renders view
     */
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
            $this->Flash->error(__('Road trip not found.'));
            return $this->redirect(['action' => 'index']);
        }

        $currentUserId = $this->request->getAttribute('identity')?->getIdentifier();
        $isOwner = ($currentUserId === $roadtrip->user_id);

        if (!$isOwner && $roadtrip->visibility !== 'public') {
            $this->Flash->error(__('This road trip is private.'));
            return $this->redirect(['action' => 'index']);
        }

        $identity = $this->request->getAttribute('identity');

        if ($identity) {
            $userId = $identity->getIdentifier();
            $historiesTable = $this->Roadtrips->Histories;

            $existingHistory = $historiesTable->find()
                ->where([
                    'user_id' => $userId,
                    'roadtrip_id' => $id
                ])
                ->first();

            if ($existingHistory) {
                $existingHistory->created = new \Cake\I18n\FrozenTime();
                $historiesTable->save($existingHistory);
            } else {
                $newHistory = $historiesTable->newEmptyEntity();
                $newHistory->user_id = $userId;
                $newHistory->roadtrip_id = $id;
                $newHistory->date_visite = new \Cake\I18n\FrozenTime();
                $historiesTable->save($newHistory);
            }
        }

        $geocodedPlacesTable = $this->fetchTable('GeocodedPlaces');
        $jsMapData = [];

        foreach ($roadtrip->trips as $trip) {
            $departureCoords = $this->_getCoordinates($trip->departure, $geocodedPlacesTable);
            $arrivalCoords = $this->_getCoordinates($trip->arrival, $geocodedPlacesTable);

            $subStepCoords = [];
            foreach ($trip->sub_steps as $se) {
                if (!empty($se->city)) {
                    $coords = $this->_getCoordinates($se->city, $geocodedPlacesTable);
                    if ($coords) {
                        $subStepCoords[] = [
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
                    'lat' => $departureCoords['lat'] ?? null,
                    'lon' => $departureCoords['lon'] ?? null,
                    'nom' => $trip->departure
                ],
                'arrivee' => [
                    'lat' => $arrivalCoords['lat'] ?? null,
                    'lon' => $arrivalCoords['lon'] ?? null,
                    'nom' => $trip->arrival
                ],
                'heure_depart' => $trip->departure_time ? $trip->departure_time->format('H:i') : null,
                'sousEtapes' => $subStepCoords,
                'hasCoords' => ($departureCoords && $arrivalCoords)
            ];
        }

        $jsMapDataJson = json_encode($jsMapData);

        $this->set(compact('roadtrip', 'jsMapDataJson', 'isOwner'));
    }

    /**
     * Formats trips entities for JavaScript map rendering.
     *
     * @param iterable $trips The trips to format.
     * @return array
     */
    private function _formatTripsForJs($trips)
    {
        $data = [];
        foreach ($trips as $trip) {
            $subSteps = [];
            foreach ($trip->sub_steps as $step) {
                $formattedTime = '';

                if (!empty($step->duration)) {
                    if (is_object($step->duration) && method_exists($step->duration, 'format')) {
                        $formattedTime = $step->duration->format('H:i');
                    } else {
                        $formattedTime = substr((string)$step->duration, 0, 5);
                    }
                }

                $subSteps[] = [
                    'nom' => $step->city,
                    'heure' => $formattedTime,
                    'remarque' => $step->description,
                    'lat' => $step->latitude ?? null,
                    'lon' => $step->longitude ?? null,
                    'coords' => [
                        (float)($step->latitude ?? 0),
                        (float)($step->longitude ?? 0)
                    ]
                ];
            }

            $data[] = [
                'id' => $trip->id,
                'depart' => $trip->departure,
                'departLat' => $trip->departure_latitude ?? null,
                'departLon' => $trip->departure_longitude ?? null,
                'arrivee' => $trip->arrival,
                'arriveeLat' => $trip->arrival_latitude ?? null,
                'arriveeLon' => $trip->arrival_longitude ?? null,
                'mode' => $trip->transport_mode,
                'date_trajet' => !empty($trip->date) ?
                    ($trip->date instanceof \DateTimeInterface ? $trip->date->format('Y-m-d') : $trip->date)
                    : null,

                'heure_depart' => $trip->departure_time ? $trip->departure_time->format('H:i') : '08:00',
                'sousEtapes' => $subSteps
            ];
        }
        return $data;
    }

    /**
     * Retrieves coordinates for a given city name via DB cache or Nominatim API.
     *
     * @param string $cityName The name of the city.
     * @param \Cake\ORM\Table $table The GeocodedPlaces table instance.
     * @return array|null Array with 'lat' and 'lon', or null if not found.
     */
    protected function _getCoordinates($cityName, $table)
    {
        if (empty($cityName)) return null;

        $cleanName = trim($cityName);

        $place = $table->find()
            ->where(['name' => $cleanName])
            ->first();

        if ($place) {
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
            \Cake\Log\Log::error("Geocoding API Error: " . $e->getMessage());
            return null;
        }

        return null;
    }

    /**
     * Maps raw JSON string from frontend to CakePHP entity format.
     *
     * @param string $jsonString The JSON string representing trips.
     * @return array Array formatted for CakePHP entities.
     */
    private function _mapJsonToCakeEntities($jsonString)
    {
        $decoded = json_decode($jsonString, true);
        if (!$decoded) return [];

        $cakeTrips = [];

        foreach ($decoded as $index => $tripJs) {
            $newTrip = [
                'order_number' => $index + 1,
                'title' => ($tripJs['depart'] ?? '') . ' -> ' . ($tripJs['arrivee'] ?? ''),
                'departure' => $tripJs['depart'] ?? '',
                'arrival' => $tripJs['arrivee'] ?? '',
                'transport_mode' => $tripJs['mode'] ?? 'Voiture',
                'departure_time' => $tripJs['heure_depart'] ?? '08:00',
                'date' => !empty($tripJs['date_trajet']) ? $tripJs['date_trajet'] : null,
                'sub_steps' => []
            ];

            if (!empty($tripJs['sousEtapes'])) {
                foreach ($tripJs['sousEtapes'] as $j => $se) {
                    $newTrip['sub_steps'][] = [
                        'order_number' => $j + 1,
                        'city' => $se['nom'] ?? '',
                        'description' => $se['remarque'] ?? '',
                        'transport_type' => $tripJs['mode'] ?? 'Voiture',
                        'duration' => $se['heure'] ?? null
                    ];
                }
            }

            $cakeTrips[] = $newTrip;
        }

        return $cakeTrips;
    }

    /**
     * History method
     * Displays the roadtrips visited by the user.
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function history()
    {
        $historiesTable = $this->Roadtrips->Histories;
        $userId = $this->request->getAttribute('identity')->getIdentifier();

        $query = $historiesTable->find()
            ->contain([
                'Roadtrips' => [
                    'Users'
                ]
            ])
            ->where(['Histories.user_id' => $userId])
            ->order(['Histories.created' => 'DESC']);

        try {
            $historyRecords = $this->paginate($query, ['limit' => 12]);
        } catch (\Exception $e) {
            return $this->redirect(['action' => 'history']);
        }

        $this->set(compact('historyRecords'));
    }

    /**
     * Deletes all history records for the current user.
     *
     * @return \Cake\Http\Response|null Redirects to history.
     */
    public function deleteHistory()
    {
        $this->request->allowMethod(['post', 'delete']);

        $historiesTable = $this->Roadtrips->Histories;
        $userId = $this->request->getAttribute('identity')->getIdentifier();

        $historiesTable->deleteAll(['user_id' => $userId]);

        $this->Flash->success('Your history has been cleared.');

        return $this->redirect(['action' => 'history']);
    }

    /**
     * Uploads an image for a sub-step via AJAX.
     *
     * @return \Cake\Http\Response JSON response with the image URL.
     */
    public function uploadStepImage()
    {
        $this->request->allowMethod(['post', 'ajax']);
        $this->viewBuilder()->disableAutoLayout();

        $response = ['success' => false, 'message' => 'An unknown error occurred.'];

        $image = $this->request->getData('image');

        if ($image instanceof \Laminas\Diactoros\UploadedFile && $image->getError() === UPLOAD_ERR_OK) {

            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $mimeType = $image->getClientMediaType();

            if (!in_array($mimeType, $allowedMimeTypes)) {
                $response['message'] = 'Unauthorized file format. Only JPG, PNG, GIF, or WEBP are allowed.';
            } else {
                $ext = pathinfo($image->getClientFilename(), PATHINFO_EXTENSION);
                if (empty($ext)) {
                    $ext = str_replace('image/', '', $mimeType);
                }

                $newName = 'step_' . uniqid() . '.' . $ext;
                $dirPath = WWW_ROOT . 'uploads' . DS . 'sousetapes';

                if (!is_dir($dirPath)) {
                    mkdir($dirPath, 0777, true);
                }

                $destination = $dirPath . DS . $newName;

                try {
                    $image->moveTo($destination);
                    $publicUrl = \Cake\Routing\Router::url('/uploads/sousetapes/' . $newName, true);

                    $response = [
                        'success' => true,
                        'url' => $publicUrl
                    ];
                } catch (\Exception $e) {
                    \Cake\Log\Log::error("Step image upload error: " . $e->getMessage());
                    $response['message'] = 'Error saving the file to the server.';
                }
            }
        } else {
            $response['message'] = 'No valid file received or transfer error.';
        }

        return $this->response
            ->withType('application/json')
            ->withStringBody(json_encode($response));
    }

    /**
     * Exports a roadtrip as a PDF file.
     *
     * @param string|null $id Roadtrip id.
     * @return \Cake\Http\Response|null
     */
    public function exportPdf($id = null)
    {
        try {
            $roadtrip = $this->Roadtrips->get($id, [
                'contain' => [
                    'Users',
                    'Trips' => [
                        'sort' => ['Trips.order_number' => 'ASC'],
                        'SubSteps' => [
                            'sort' => ['SubSteps.order_number' => 'ASC']
                        ]
                    ]
                ]
            ]);
        } catch (\Cake\Datasource\Exception\RecordNotFoundException $e) {
            $this->Flash->error(__('Road trip not found.'));
            return $this->redirect(['action' => 'index']);
        }

        $currentUserId = $this->request->getAttribute('identity')?->getIdentifier();
        $isOwner = ($currentUserId === $roadtrip->user_id);

        if (!$isOwner && $roadtrip->visibility !== 'public') {
            $this->Flash->error(__('This road trip is private and cannot be exported.'));
            return $this->redirect(['action' => 'index']);
        }

        $this->viewBuilder()
            ->setTemplatePath('Roadtrips')
            ->setTemplate('export_pdf')
            ->setLayout('layoutPdf');

        $this->set(compact('roadtrip'));

        $view = $this->createView();
        $html = $view->render();

        $options = new Options();
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $pdfOutput = $dompdf->output();
        $fileName = 'Carnet_de_route_' . preg_replace('/[^a-zA-Z0-9]/', '_', $roadtrip->title) . '.pdf';

        return $this->response
            ->withType('application/pdf')
            ->withDownload($fileName)
            ->withStringBody($pdfOutput);
    }

    /**
     * Exports a roadtrip as a GPX file.
     *
     * @param string|null $id Roadtrip id.
     * @return \Cake\Http\Response|null
     */
    public function exportGpx($id = null)
    {
        $roadtrip = $this->Roadtrips->get($id, [
            'contain' => [
                'Trips' => [
                    'sort' => ['Trips.order_number' => 'ASC'],
                    'SubSteps' => [
                        'sort' => ['SubSteps.order_number' => 'ASC']
                    ]
                ]
            ]
        ]);

        $geocodedPlacesTable = \Cake\ORM\TableRegistry::getTableLocator()->get('GeocodedPlaces');

        $xmlString = '<?xml version="1.0" encoding="UTF-8" standalone="no" ?>' .
            '<gpx version="1.1" creator="TripsAndRoads" xmlns="http://www.topografix.com/GPX/1/1"></gpx>';
        $xml = new \SimpleXMLElement($xmlString);

        $metadata = $xml->addChild('metadata');
        $metadata->addChild('name', h($roadtrip->title));

        foreach ($roadtrip->trips as $trip) {
            foreach ($trip->sub_steps as $step) {
                if (empty($step->city)) {
                    continue;
                }
                $place = $geocodedPlacesTable->find()
                    ->where(['name' => $step->city])
                    ->first();

                if ($place) {
                    $wpt = $xml->addChild('wpt');
                    $wpt->addAttribute('lat', (string)$place->latitude);
                    $wpt->addAttribute('lon', (string)$place->longitude);

                    $wpt->addChild('name', h($step->city));

                    if (!empty($step->description)) {
                        $wpt->addChild('desc', h($step->description));
                    }

                    $wpt->addChild('ele', '0');
                }
            }
        }

        $fileName = preg_replace('/[^a-zA-Z0-9]/', '_', $roadtrip->title) . '.gpx';

        return $this->response
            ->withType('application/gpx+xml')
            ->withDownload($fileName)
            ->withStringBody($xml->asXML());
    }
}
