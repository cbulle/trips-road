<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Mailer\Mailer;
use Cake\Log\Log;

/**
 * PageLink Controller
 * Handles static pages, terms, privacy policy, and contact/FAQ forms.
 */
class PageLinkController extends AppController
{
    /**
     * Initialization hook method.
     * Allows unauthenticated users to access all informational pages.
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->Authentication->addUnauthenticatedActions(['contact', 'faq', 'terms', 'about', 'cookies', 'privacy']);
    }

    /**
     * Contact page and form handling.
     *
     * @return \Cake\Http\Response|null Redirects on success, renders otherwise.
     */
    public function contact()
    {
        if ($this->request->is('post')) {
            $data = $this->request->getData();

            if (!empty($data['email']) && !empty($data['message'])) {
                if ($this->_sendEmail($data, 'contact')) {
                    $this->Flash->success(__('Votre message a bien été envoyé ! Nous vous répondrons sous peu.'));
                    return $this->redirect(['action' => 'contact']);
                } else {
                    $this->Flash->error(__('Erreur technique lors de l\'envoi. Veuillez réessayer plus tard.'));
                }
            } else {
                $this->Flash->error(__('Veuillez remplir tous les champs obligatoires.'));
            }
        }
    }

    /**
     * FAQ page and question form handling.
     *
     * @return \Cake\Http\Response|null Redirects on success, renders otherwise.
     */
    public function faq()
    {
        if ($this->request->is('post')) {
            $data = $this->request->getData();

            if (!empty($data['email']) && !empty($data['question'])) {
                if ($this->_sendEmail($data, 'faq')) {
                    $this->Flash->success(__('Votre question a bien été envoyée ! Nous vous répondrons dès que possible.'));
                    return $this->redirect(['action' => 'faq']);
                } else {
                    $this->Flash->error(__('Erreur technique lors de l\'envoi. Veuillez réessayer plus tard.'));
                }
            } else {
                $this->Flash->error(__('Veuillez remplir tous les champs obligatoires.'));
            }
        }
    }

    /**
     * Terms of Service page (CGU).
     */
    public function terms()
    {
    }

    /**
     * About / Road Trip info page.
     */
    public function about()
    {
    }

    /**
     * Cookies policy page.
     */
    public function cookies()
    {
    }

    /**
     * Privacy policy page.
     */
    public function privacy()
    {
    }

    /**
     * Helper method to send emails (Skinny Controller approach).
     *
     * @param array $data Form data from the user request.
     * @param string $type The context of the email ('contact' or 'faq').
     * @return bool Returns true if the email was successfully sent, false otherwise.
     */
    private function _sendEmail(array $data, string $type): bool
    {
        try {
            $mailer = new Mailer('default');

            $name = $data['name'] ?? 'Non renseigné';
            $subject = $data['subject'] ?? 'Autre';
            $content = $data['message'] ?? $data['question'] ?? '';

            $emailSubject = ($type === 'faq' ? 'FAQ Site : ' : 'Contact Site : ') . $subject;
            $introText = ($type === 'faq')
                ? "Nouvelle question reçue depuis le site web :\n\n"
                : "Nouveau message reçu depuis le site web :\n\n";

            $mailer
                ->setTransport('default')
                ->setFrom(['tripsandroad@gmail.com' => 'Site Trips & Roads'])
                ->setTo('tripsandroad@gmail.com')
                ->setReplyTo($data['email'], $name)
                ->setSubject($emailSubject)
                ->deliver(
                    $introText .
                    "Nom : " . $name . "\n" .
                    "Email : " . $data['email'] . "\n" .
                    "Sujet : " . $subject . "\n\n" .
                    "--------------------------------------------------\n" .
                    "Contenu :\n" . $content
                );

            return true;
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'envoi d\'email : ' . $e->getMessage());
            return false;
        }
    }
}
