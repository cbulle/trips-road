<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;
use Cake\Http\Cookie\Cookie;
use Cake\I18n\FrozenTime;
use League\OAuth2\Client\Provider\Google;
use Cake\Core\Configure;
use Cake\Mailer\Mailer;
use Cake\Utility\Security;
use Cake\Routing\Router;

/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 */
class UsersController extends AppController
{
    /**
     * Initialization hook method.
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('Authentication.Authentication');
    }

    /**
     * beforeFilter callback.
     * Allows unauthenticated users to access specific actions.
     *
     * @param \Cake\Event\EventInterface $event An Event instance
     * @return void
     */
    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->Authentication->addUnauthenticatedActions(['login', 'add', 'accessibility', 'loginGoogle', 'callbackGoogle']);
    }

    /**
     * Login method
     * Handles user authentication.
     *
     * @return \Cake\Http\Response|null|void Redirects on successful login, renders view otherwise.
     */
    public function login()
    {
        $result = $this->Authentication->getResult();
        if ($result && $result->isValid()) {
            $target = $this->Authentication->getLoginRedirect() ?? '/';
            return $this->redirect($target);
        }
        if ($this->request->is('post')) {
            $this->Flash->error(__('Identifiant ou mot de passe incorrect'));
        }
    }

    /**
     * Initiates Google OAuth2 login flow.
     *
     * @return \Cake\Http\Response|null Redirects to Google authentication page.
     */
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

    /**
     * Handles the callback from Google OAuth2.
     *
     * @return \Cake\Http\Response|null Redirects to home on success, or login on failure.
     */
    public function callbackGoogle()
    {
        $provider = new Google([
            'clientId' => Configure::read('Google.clientId'),
            'clientSecret' => Configure::read('Google.clientSecret'),
            'redirectUri' => Configure::read('Google.redirectUri'),
        ]);

        $session = $this->request->getSession();
        $state = $this->request->getQuery('state');
        $savedState = $session->read('oauth2state');

        if (empty($state) || ($state !== $savedState)) {
            $session->delete('oauth2state');
            $this->Flash->error(__('État OAuth invalide.'));
            return $this->redirect(['action' => 'login']);
        }

        try {
            $token = $provider->getAccessToken('authorization_code', [
                'code' => $this->request->getQuery('code')
            ]);

            $googleUser = $provider->getResourceOwner($token);

            $email = $googleUser->getEmail();
            $name = $googleUser->getName();
            $socialId = $googleUser->getId();
            $firstName = $googleUser->getFirstName() ?: 'Utilisateur';
            $lastName = $googleUser->getLastName() ?: 'Google';

            $user = $this->Users->find()->where(['email' => $email])->first();

            if (!$user) {
                $user = $this->Users->newEmptyEntity();
                $user->email = $email;
                $user->username = $name . '_' . substr(uniqid(), -4);
                $user->first_name = $firstName;
                $user->last_name = $lastName;
                $user->password = 'Google_' . uniqid() . 'A1!';
                $user->social_id = $socialId;
                $user->social_provider = 'google';

                if (!$this->Users->save($user)) {
                    $this->Flash->error(__('Erreur lors de la création du compte Google.'));
                    return $this->redirect(['action' => 'login']);
                }
            } else {
                if (empty($user->social_id)) {
                    $user->social_id = $socialId;
                    $user->social_provider = 'google';
                    $this->Users->save($user);
                }
            }

            $this->Authentication->setIdentity($user);
            $this->Flash->success('Connexion réussie via Google !');

            $target = $this->Authentication->getLoginRedirect() ?? '/';
            return $this->redirect($target);

        } catch (\Exception $e) {
            $this->Flash->error(__('Erreur d\'authentification Google : ') . $e->getMessage());
            return $this->redirect(['action' => 'login']);
        }
    }

    /**
     * Add method
     * Registers a new user.
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $user = $this->Users->newEmptyEntity();
        if ($this->request->is('post')) {
            $data = $this->request->getData();

            $day = $data['birth_day'] ?? null;
            $month = $data['birth_month'] ?? null;
            $year = $data['birth_year'] ?? null;

            if (!empty($day) && !empty($month) && !empty($year)) {
                $data['birth_date'] = FrozenTime::create((int)$year, (int)$month, (int)$day);
            }

            $user = $this->Users->patchEntity($user, $data);

            if ($this->Users->save($user)) {
                $this->Flash->success(__('L\'utilisateur a été sauvegardé.'));
                return $this->redirect(['action' => 'login']);
            }
            $this->Flash->error(__('L\'utilisateur n\'a pas pu être sauvegardé. Veuillez réessayer.'));
        }

        $days = array_combine(range(1, 31), range(1, 31));
        $years = array_combine(range((int)date('Y'), 1900), range((int)date('Y'), 1900));

        $this->set(compact('user', 'days', 'years'));
    }

    /**
     * Logout method
     * Logs the user out.
     *
     * @return \Cake\Http\Response|null Redirects to login.
     */
    public function logout()
    {
        $result = $this->Authentication->getResult();
        if ($result && $result->isValid()) {
            $this->Authentication->logout();
            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }
    }

    /**
     * Profile method
     * Displays and updates the connected user's profile.
     *
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     */
    public function profile()
    {
        $userId = $this->request->getAttribute('identity')->getIdentifier();
        $user = $this->Users->get($userId);

        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();

            if (empty($data['password'])) {
                unset($data['password']);
                unset($data['confirm_password']);
            } elseif ($data['password'] !== $data['confirm_password']) {
                $this->Flash->error(__('Les mots de passe ne correspondent pas.'));
                return $this->redirect(['controller' => 'Users', 'action' => 'profile']);
            }

            $user = $this->Users->patchEntity($user, $data);

            if ($this->Users->save($user)) {
                $this->Flash->success(__('Profil mis à jour.'));
                return $this->redirect(['controller' => 'Users', 'action' => 'profile']);
            } else {
                $this->Flash->error(__('Erreur lors de la mise à jour.'));
            }
        }

        $this->set(compact('user'));
    }

    /**
     * Accessibility method
     * Manages user accessibility preferences via cookies.
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
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

        $isDarkMode = (bool)$this->request->getCookie('modeSombre');
        $isVisionMode = (bool)$this->request->getCookie('modeMalvoyant');
        $colorBlindType = $this->request->getCookie('typeDaltonien') ?: 'aucun';

        $this->set(compact('user', 'isDarkMode', 'isVisionMode', 'colorBlindType'));
    }

    public function forgotPassword()
    {
        if ($this->request->is('post')) {
            $email = $this->request->getData('email');
            $user = $this->Users->find()->where(['email' => $email])->first();

            if ($user) {
                $user->token = Security::hash(Security::randomBytes(32), 'sha256', false);
                $user->token_expiration = new FrozenTime('+1 hour');

                if ($this->Users->save($user)) {
                    $mailer = new Mailer('default');
                    $mailer->setTo($user->email)
                        ->setSubject('Réinitialisation de votre mot de passe')
                        ->deliver("Cliquez sur ce lien pour réinitialiser votre mot de passe : " .
                            Router::url(['action' => 'resetPassword', $user->token], true));

                    $this->Flash->success(__('Un email de récupération vous a été envoyé.'));
                    return $this->redirect(['action' => 'login']);
                }
            }
            $this->Flash->error(__('Aucun compte n\'est associé à cet email.'));
        }
    }
    public function resetPassword($token = null)
    {
        if (!$token) {
            $this->Flash->error(__('Jeton invalide.'));
            return $this->redirect(['action' => 'login']);
        }

        $user = $this->Users->find()
            ->where(['token' => $token, 'token_expiration >' => new FrozenTime()])
            ->first();

        if (!$user) {
            $this->Flash->error(__('Le lien a expiré ou est invalide.'));
            return $this->redirect(['action' => 'login']);
        }

        if ($this->request->is(['post', 'put'])) {
            $user = $this->Users->patchEntity($user, $this->request->getData());
            $user->token = null;
            $user->token_expiration = null;

            if ($this->Users->save($user)) {
                $this->Flash->success(__('Votre mot de passe a été mis à jour.'));
                return $this->redirect(['action' => 'login']);
            }
            $this->Flash->error(__('Erreur lors de la mise à jour.'));
        }
        $this->set(compact('user'));
    }
}
