<?php
/**
 * @var \App\View\AppView $this
 * @var string $message
 * @var string $url
 */
use Cake\Core\Configure;

$this->setLayout('error');

if (Configure::read('debug')) :
    $this->setLayout('error');
    $this->assign('title', $message);
    $this->assign('templateName', 'error400.php');

    $this->start('file');
    echo $this->element('auto_table_warning');
    $this->end();
endif;
?>

<div class="error-container">
    <h2>Oups ! Page introuvable</h2>

    <p class="error">
        <strong>Erreur 404 : </strong>
        L'adresse <strong>'<?= h($url) ?>'</strong> n'existe pas ou a été déplacée. Vérifiez votre lien ou retournez à l'accueil.
    </p>

    <a href="<?= $this->Url->build('/') ?>" class="btn-back-home">
        &larr; Retour à l'accueil
    </a>
</div>
