<main>
    <div class="contact-wrapper">

        <div class="contact-info">
            <h2>Discutons !</h2>
            <p>Une question sur votre itinéraire ? Un bug à signaler ? Ou simplement envie de dire bonjour ? Notre équipe étudiante est à votre écoute.</p>

            <div class="info-item">
                <h3>Adresse</h3>
                <p>IUT Lyon 1 - Site de Bourg<br>71 Rue Peter Fink<br>01000 Bourg-en-Bresse</p>
            </div>

            <div class="info-item">
                <h3>Email</h3>
                <p>tripsandroads@gmail.com</p>
            </div>
        </div>

        <div class="contact-form">
            <h1>Envoyez-nous un message</h1>

            <?= $this->Flash->render() ?>

            <?= $this->Form->create(null) ?>

            <?= $this->Form->control('nom', [
                'label' => 'Votre Nom',
                'required' => true,
                'placeholder' => 'Jean Dupont',
                'container' => ['class' => 'form-group']
            ]) ?>

            <?= $this->Form->control('email', [
                'label' => 'Votre Email',
                'type' => 'email',
                'required' => true,
                'placeholder' => 'jean@exemple.com',
                'container' => ['class' => 'form-group']
            ]) ?>

            <?= $this->Form->control('sujet', [
                'type' => 'select',
                'label' => 'Sujet',
                'options' => [
                    'Question générale' => 'Question générale',
                    'Support technique / Bug' => 'Support technique / Bug',
                    'Partenariat' => 'Partenariat',
                    'Autre' => 'Autre'
                ],
                'required' => true,
                'container' => ['class' => 'form-group']
            ]) ?>

            <?= $this->Form->control('message', [
                'type' => 'textarea',
                'label' => 'Message',
                'required' => true,
                'placeholder' => 'Comment pouvons-nous vous aider ?',
                'container' => ['class' => 'form-group']
            ]) ?>

            <?= $this->Form->button('Envoyer le message', ['type' => 'submit']) ?>

            <?= $this->Form->end() ?>
        </div>

    </div>
</main>
