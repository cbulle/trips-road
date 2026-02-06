<?php

namespace App\Controller;

use App\Controller\AppController;
use Cake\Mailer\Mailer;
class PageLinkController extends AppController
{

    public function contact(){
        if ($this->request->is('post')) {

            $data = $this->request->getData();

            if (!empty($data['email']) && !empty($data['message'])) {

                try {
                    $mailer = new Mailer('default');

                    $mailer
                        ->setTransport('default')
                        ->setFrom(['tripsandroad@gmail.com' => 'Site Trips & Roads'])
                        ->setTo('tripsandroad@gmail.com')
                        ->setReplyTo($data['email'], $data['nom'])
                        ->setSubject('Contact Site : ' . ($data['sujet'] ?? 'Aucun sujet'))
                        ->deliver(
                            "Nouveau message reçu depuis le site web :\n\n" .
                            "Nom : " . ($data['nom'] ?? 'Non renseigné') . "\n" .
                            "Email : " . $data['email'] . "\n" .
                            "Sujet : " . ($data['sujet'] ?? 'Autre') . "\n\n" .
                            "--------------------------------------------------\n" .
                            "Message :\n" . $data['message']
                        );

                    $this->Flash->success('Votre message a bien été envoyé ! Nous vous répondrons sous peu.');
                    return $this->redirect(['action' => 'contact']);

                } catch (\Exception $e) {
                    $this->Flash->error('Erreur technique lors de l\'envoi : ' . $e->getMessage());
                }

            } else {
                $this->Flash->error('Veuillez remplir tous les champs obligatoires.');
            }
        }
    }

    public function faq(){

    }

    public function cgu(){

    }

    public function road_trip(){

    }

    public function cookie(){

    }

    public function politique(){

    }
}
