<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;
use Cake\Http\Cookie\Cookie;
use Cake\I18n\FrozenTime;
use League\OAuth2\Client\Provider\Google;
use Cake\Core\Configure;

class UsersController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('Authentication.Authentication');
    }

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->Authentication->addUnauthenticatedActions(['login', 'add', 'accessibility', 'loginGoogle', 'callbackGoogle']);
    }

    public function login()
    {
        $result = $this->Authentication->getResult();
        if ($result && $result->isValid()) {
            $target = $this->Authentication->getLoginRedirect() ?? '/';
            return $this->redirect($target);
        }
        if ($this->request->is('post')) {
            $this->Flash->error('Identifiant ou mot de passe incorrect');
        }
    }

    public function loginGoogle()
    {
        $provider = new Google([
            'clientId' => Configure::read('Google.clientId'),
            'clientSecret' => Configure::read('Google.clientSecret'),
            'redirectUri' => Configure::read('Google.redirectUri'),
        ]);

        $authUrl = $provider->getAuthorizationUrl([
            'scope' => ['email', 'profile']
        ]);

        $this->request->getSession()->write('oauth2state', $provider->getState());

        return $this->redirect($authUrl);
    }

    public function callbackGoogle()
    {
        $session = $this->request->getSession();
        $code = $this->request->getQuery('code');
        $state = $this->request->getQuery('state');

        if (empty($code) || empty($state) || $state !== $session->read('oauth2state')) {
            $session->delete('oauth2state');
            $this->Flash->error('Erreur de sécurité ou annulation.');
            return $this->redirect(['action' => 'login']);
        }

        try {
            $provider = new Google([
                'clientId' => Configure::read('Google.clientId'),
                'clientSecret' => Configure::read('Google.clientSecret'),
                'redirectUri' => Configure::read('Google.redirectUri'),
            ]);

            $accessToken = $provider->getAccessToken('authorization_code', [
                'code' => $code
            ]);

            $googleUser = $provider->getResourceOwner($accessToken);
            $email = $googleUser->getEmail();
            $socialId = $googleUser->getId();
            $name = $googleUser->getName();

            $user = $this->Users->find()->where(['email' => $email])->first();

            if ($user) {
                if (empty($user->social_id)) {
                    $user->social_id = $socialId;
                    $user->social_provider = 'google';
                    $this->Users->save($user);
                }
            } else {
                $user = $this->Users->newEmptyEntity();
                $user->email = $email;
                $user->username = $name;
                $user->password = 'SOCIAL_' . uniqid();
                $user->social_id = $socialId;
                $user->social_provider = 'google';

                if (!$this->Users->save($user)) {
                    $this->Flash->error('Erreur lors de la création du compte Google.');
                    return $this->redirect(['action' => 'login']);
                }
            }

            $this->Authentication->setIdentity($user);

            $this->Flash->success('Connexion réussie via Google !');

            $target = $this->Authentication->getLoginRedirect() ?? '/';
            return $this->redirect($target);

        } catch (\Exception $e) {
            $this->Flash->error('Erreur Google : ' . $e->getMessage());
            return $this->redirect(['action' => 'login']);
        }
    }

    public function logout()
    {
        $this->Authentication->logout();
        $this->Flash->success(__('Vous avez été déconnecté.'));
        return $this->redirect(['controller' => 'Users', 'action' => 'login']);
    }

    public function add()
    {
        $days = array_combine(range(1, 31), range(1, 31));
        $currentYear = date('Y');
        $years = array_combine(range($currentYear, 1920), range($currentYear, 1920));

        $months = [
            1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
            5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
            9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
        ];

        $user = $this->Users->newEmptyEntity();
        if ($this->request->is('post')) {
            $data = $this->request->getData();

            if (!empty($data['birth_day']) && !empty($data['birth_month']) && !empty($data['birth_year'])) {
                $birthdate = sprintf('%d-%02d-%02d', $data['birth_year'], $data['birth_month'], $data['birth_day']);
                $data['date_naissance'] = new FrozenTime($birthdate);
            }

            $user = $this->Users->patchEntity($user, $data);
            if ($this->Users->save($user)) {
                $this->Flash->success(__('Inscription réussie. Connectez-vous maintenant.'));
                return $this->redirect(['action' => 'login']);
            }
            $this->Flash->error(__('Impossible de créer le compte. Vérifiez les erreurs.'));
        }
        $this->set(compact('user', 'days', 'months', 'years'));
    }

    public function profile()
    {
        $userId = $this->request->getAttribute('identity')->getIdentifier();
        $user = $this->Users->get($userId);

        if ($this->request->is(['post', 'put'])) {
            $data = $this->request->getData();

            $image = $this->request->getData('profile_picture_file');
            if ($image && $image->getError() === UPLOAD_ERR_OK) {
                $ext = pathinfo($image->getClientFilename(), PATHINFO_EXTENSION);
                $newName = 'pp_' . uniqid() . '.' . $ext;
                $image->moveTo(WWW_ROOT . 'uploads/pp/' . $newName);
                $data['profile_picture'] = $newName;
            }

            $user = $this->Users->patchEntity($user, $data);

            if ($this->Users->save($user)) {
                $this->Flash->success(__('Profil mis à jour.'));
                return $this->redirect(['controller' => 'Users', 'action' => 'profile']);
            } else {
                $this->Flash->error(__('Erreur lors de la mise à jour.'));
                return $this->redirect(['controller' => 'Users', 'action' => 'profile']);
            }
        }

        $this->set(compact('user'));
    }

    public function accessibility()
    {
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $response = $this->response;

            if (isset($data['mode_sombre'])) {
                $response = $response->withCookie(Cookie::create('modeSombre', '1'));
            } else {
                $response = $response->withExpiredCookie(new Cookie('modeSombre'));
            }

            if (isset($data['daltonism-type'])) {
                $response = $response->withCookie(Cookie::create('typeDaltonien', $data['daltonism-type']));
            }

            $this->response = $response;
            $this->Flash->success(__('Préférences enregistrées.'));
        }

        $userId = null;
        $user = null;

        $identity = $this->request->getAttribute('identity');
        if ($identity) {
            $userId = $identity->getIdentifier();
            $user = $this->Users->get($userId);
        }

        $this->set(compact('user'));
    }
}
