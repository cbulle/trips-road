<?php

namespace App\Controller;

use App\Controller\AppController;

class PageLinkController extends AppController
{

    public function contact(){
        // 1. On vérifie d'abord si on est en train de soumettre le formulaire (POST)
        if ($this->request->is('post')) {

            // 2. On récupère les données du formulaire
            $data = $this->request->getData();

            // 3. Validation simple (vous pouvez étoffer selon vos besoins)
            if (!empty($data['email']) && !empty($data['message'])) {

                // --- LOGIQUE D'ENVOI D'EMAIL ---
                // Pour l'instant, on simule que l'envoi marche toujours ($success = true).
                // Plus tard, vous mettrez ici : $success = $mailer->send(...);
                $success = true;

                // 4. On gère le résultat
                if ($success) {
                    $this->Flash->success('Votre message a bien été envoyé !');

                    // On redirige vers la même page pour vider le formulaire (PRG Pattern)
                    return $this->redirect(['action' => 'contact']);
                } else {
                    $this->Flash->error('Une erreur est survenue lors de l\'envoi.');
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
