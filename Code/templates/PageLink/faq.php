<main>
    <div class="cgu-container">
        <h1>Foire aux Questions</h1>
        <p>Toutes les réponses pour préparer votre prochain Road Trip sereinement.</p>
    </div>
    <p>Toutes les réponses pour préparer votre prochain Road Trip sereinement.</p>
    </div>
    <section class="faq-category">
        <h2> Gestion du Compte</h2>

        <details>
            <summary>Comment créer un compte sur Trips & Road ?</summary>
            <div class="faq-answer">
                <p>C'est très simple ! Cliquez sur l'icône utilisateur en haut à droite, puis sélectionnez "S'inscrire". Remplissez le formulaire avec votre email, un mot de passe sécurisé et vos informations de profil. Une fois inscrit, vous pourrez commencer à créer vos trajets immédiatement.</p>
            </div>
        </details>

        <details>
            <summary>J'ai oublié mon mot de passe, que faire ?</summary>
            <div class="faq-answer">
                <p>Pas de panique. Sur la page de connexion, cliquez sur le lien "Mot de passe oublié ?". Entrez votre adresse email, et nous vous enverrons un lien sécurisé (valable 30 minutes) pour réinitialiser votre mot de passe.</p>
            </div>
        </details>

        <details>
            <summary>Comment modifier ma photo de profil ou mes informations ?</summary>
            <div class="faq-answer">
                <p>Une fois connecté, rendez-vous sur votre page <strong>Profil</strong>. Vous verrez un bouton "Modifier mes informations" qui vous permettra de changer votre bio, votre ville de résidence ou d'uploader une nouvelle photo de profil.</p>
            </div>
        </details>
    </section>

    <section class="faq-category">
        <h2> Mes Road Trips</h2>

        <details>
            <summary>Comment fonctionne la confidentialité de mes trajets ?</summary>
            <div class="faq-answer">
                <p>Lors de la création d'un Road Trip, vous avez le choix entre trois niveaux de visibilité :</p>
                <ul>
                    <li><strong>Public :</strong> Visible par tous les visiteurs du site.</li>
                    <li><strong>Amis :</strong> Visible uniquement par les utilisateurs que vous avez acceptés en amis.</li>
                    <li><strong>Privé :</strong> Visible uniquement par vous.</li>
                </ul>
                <p>Vous pouvez modifier ce réglage à tout moment dans les options du trajet.</p>
            </div>
        </details>

        <details>
            <summary>Comment ajouter des étapes à mon itinéraire ?</summary>
            <div class="faq-answer">
                <p>Sur la page de création ou de modification d'un Road Trip, utilisez la barre de recherche pour trouver une ville ou un lieu. Cliquez sur "Ajouter" pour l'insérer dans votre liste. Vous pouvez ensuite réorganiser l'ordre des étapes par simple glisser-déposer.</p>
            </div>
        </details>

        <details>
            <summary>Puis-je partager mon trajet avec des personnes qui n'ont pas de compte ?</summary>
            <div class="faq-answer">
                <p>Oui ! Utilisez le bouton <strong>Partager</strong> sur la fiche de votre Road Trip. Cela générera un lien unique que vous pouvez envoyer par email ou SMS. Toute personne disposant de ce lien pourra consulter votre itinéraire, même sans être inscrite.</p>
            </div>
        </details>
    </section>

    <section class="faq-category">
        <h2>Communauté & Amis</h2>

        <details>
            <summary>Comment ajouter des amis ?</summary>
            <div class="faq-answer">
                <p>Allez sur la page <strong>Amis</strong> et utilisez la barre de recherche pour trouver un utilisateur par son pseudo ou son nom. Cliquez sur "Ajouter". Une fois qu'il aura accepté votre demande, vous pourrez voir ses voyages réservés aux amis et discuter via la messagerie.</p>
            </div>
        </details>

        <details>
            <summary>Comment fonctionne la messagerie privée ?</summary>
            <div class="faq-answer">
                <p>Vous pouvez envoyer des messages privés à tous vos amis confirmés. Cliquez sur l'icône "Enveloppe" dans le menu ou allez directement sur le profil d'un ami pour démarrer une conversation sécurisée.</p>
            </div>
        </details>
    </section>

    <section class="faq-category">
        <h2> Technique & Support</h2>

        <details>
            <summary>L'application est-elle gratuite ?</summary>
            <div class="faq-answer">
                <p>Oui, Trips & Road est un projet étudiant entièrement gratuit. Toutes les fonctionnalités (calcul d'itinéraire, stockage de photos, messagerie) sont accessibles sans frais.</p>
            </div>
        </details>

        <details>
            <summary>Comment signaler un bug ou un contenu inapproprié ?</summary>
            <div class="faq-answer">
                <p>Si vous rencontrez un problème technique ou un comportement abusif, veuillez utiliser notre formulaire de <a href="contact.php" class="contact-link">Contact</a> ou nous écrire directement à support@tripsandroads.com.</p>
            </div>
        </details>
    </section>

    <section class="ask-question">
        <h2>Posez votre propre question</h2>

        <?php if (isset($message)): ?>
            <p class="confirmation-message"><?= $message ?></p>
        <?php endif; ?>

        <?= $this->Form->create(); ?>

            <?= $this->Form->control('Nom', ['id' => 'nom', 'title' => 'nom']) ;?>

            <?= $this->Form->control('email', [
                'type' => 'email',
                'name' => 'email',
                'id' => 'email',
            ]); ?>


            <?= $this->Form->control('Sujet', [
                'label' => 'Sujet',
                'name' => 'sujet',
                'id' => 'sujet',
            ]); ?>

        <?= $this->Form->control('Message', [
            'type' => 'textarea',
            'label' => 'Votre question:',
            'name' => 'question',
            'id' => 'question',
            'rows' => 4,
        ]); ?>

        <?= $this->Form->button('Poser votre question'); ?>
        <?= $this->Form->end();?>


<!--        <form action="/../formulaire/form_faq.php" method="POST">-->
<!---->
<!--            <label for="nom">Nom :</label>-->
<!--            <input type="text" name="nom" id="nom" value="--><?php //= isset($nom) ? $nom : '' ?><!--" required>-->
<!---->
<!--            <label for="email">Email :</label>-->
<!--            <input type="email" name="email" id="email" value="--><?php //= isset($email) ? $email : '' ?><!--" required>-->
<!---->
<!--            <label for="sujet">Sujet :</label>-->
<!--            <input type="text" name="sujet" id="sujet" value="--><?php //= isset($sujet) ? $sujet : '' ?><!--" required>-->
<!---->
<!--            <label for="question">Votre question :</label>-->
<!--            <textarea name="question" id="question" rows="4" required>--><?php //= isset($question) ? $question : '' ?><!--</textarea>-->
<!---->
<!--            <button type="submit">Poser la question</button>-->
<!--        </form>-->
    </section>



    </div>


</main>
