<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;
use Cake\Http\Cookie\Cookie;
use Cake\I18n\FrozenTime;

/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 */
class UsersController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('Authentication.Authentication');

    }

    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->Authentication->addUnauthenticatedActions(['login', 'add', 'accessibility']);
    }

    public function login()
    {
        $result = $this->Authentication->getResult();
        if ($result && $result->isValid()) {
            $target = $this->Authentication->getLoginRedirect() ?? '/';
            return $this->redirect($target);
        }
        if ($this->request->is('post')) {
            $this->Flash->error('Invalid username or password');
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
            '01' => 'Janvier', '02' => 'Février', '03' => 'Mars', '04' => 'Avril',
            '05' => 'Mai', '06' => 'Juin', '07' => 'Juillet', '08' => 'Août',
            '09' => 'Septembre', '10' => 'Octobre', '11' => 'Novembre', '12' => 'Décembre'
        ];

        $user = $this->Users->newEmptyEntity();

        if ($this->request->is('post')) {
            $data = $this->request->getData();

            if (!empty($data['birth_year']) && !empty($data['birth_month']) && !empty($data['birth_day'])) {

                $birthDateStr = sprintf(
                    '%d-%02d-%02d',
                    $data['birth_year'],
                    $data['birth_month'],
                    $data['birth_day']
                );

                try {
                    $birthDate = new \DateTime($birthDateStr);
                    $today = new \DateTime();
                    $age = $today->diff($birthDate)->y;

                    if ($age < 18) {
                        $this->Flash->error(__('Vous devez avoir au moins 18 ans pour vous inscrire.'));
                        $this->set(compact('user', 'days', 'years', 'months'));
                        return;
                    }

                    $data['birth_date'] = $birthDateStr;

                } catch (\Exception $e) {
                    $this->Flash->error(__('Date de naissance invalide.'));
                    $this->set(compact('user', 'days', 'years', 'months'));
                    return;
                }

            } else {
                $this->Flash->error(__('Veuillez renseigner votre date de naissance complète.'));
                $this->set(compact('user', 'days', 'years', 'months'));
                return;
            }

            $image = $this->request->getData('image');
            if ($image instanceof \Laminas\Diactoros\UploadedFile && $image->getError() === UPLOAD_ERR_OK) {
                $ext = pathinfo($image->getClientFilename(), PATHINFO_EXTENSION);
                $newName = 'pp_' . uniqid() . '.' . $ext;
                $targetPath = WWW_ROOT . 'uploads/pp/' . $newName;

                if (!is_dir(dirname($targetPath))) {
                    mkdir(dirname($targetPath), 0777, true);
                }

                $image->moveTo($targetPath);
                $data['profile_picture'] = $newName;
            }

            $user = $this->Users->patchEntity($user, $data);

            if ($this->Users->save($user)) {
                $this->Flash->success(__('Inscription réussie ! Veuillez vous connecter.'));
                return $this->redirect(['action' => 'login']);
            }

            $this->Flash->error(__('Impossible de créer le compte. Veuillez vérifier les champs.'));
        }

        $this->set(compact('user', 'days', 'years', 'months'));
    }

    public function profile()
    {
        $userId = $this->request->getAttribute('identity')->getIdentifier();

        $user = $this->Users->get($userId);

        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();

            if (empty($data['password'])) {
                unset($data['password']);
            }

            $image = $this->request->getData('profile_picture_file');
            if ($image instanceof \Laminas\Diactoros\UploadedFile && $image->getError() === UPLOAD_ERR_OK) {
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

        $userId = $this->request->getAttribute('identity')->getIdentifier();
        $user = $this->Users->get($userId);

        $this->set(compact('user'));
    }
}
