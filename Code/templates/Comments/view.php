<?php
$this->assign('mainClass', 'comment-view-page');

/**
 * Page affichant les détails complets d'un commentaire spécifique.
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Comment $comment L'entité du commentaire à afficher
 */

?>

<div class="comment-view-container">
    <div class="comment-detail-card">

        <div class="card-header">
            <h2>Détails de l'avis #<?= $comment->id ?></h2>
            <?php if (isset($comment->rating)): ?>
                <div class="comment-rating-large">
                    <?php
                    $rating = (int)$comment->rating;
                    for ($i = 1; $i <= 5; $i++) {
                        echo '<span class="star ' . ($i <= $rating ? 'filled' : 'empty') . '">★</span>';
                    }
                    ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="card-body">

            <div class="meta-infos-grid">
                <div class="info-box">
                    <?php
                    // On récupère l'utilisateur depuis l'entité $comment
                    $user = $comment->user;
                    ?>

                    <?php if (!empty($comment->user->profile_picture)): ?>
                        <img src="<?= $this->Url->build('/uploads/pp/' . h($comment->user->profile_picture)) ?>" class="ami-photo">                    <?php else: ?>
                        <div class="ami-placeholder">
                            <?= strtoupper(substr($user->first_name ?? 'A', 0, 1)) ?>
                        </div>
                    <?php endif; ?>

                    <div class="info-text">
                        <span class="info-label">Auteur</span>
                        <span class="info-value"><?= h($user->username ?? 'Anonyme') ?></span>
                    </div>
                </div>

                <?php if (!empty($comment->roadtrip)): ?>
                    <div class="info-box">
                        <i class="material-icons">map</i>
                        <div class="info-text">
                            <span class="info-label">Roadtrip associé</span>
                            <span class="info-value"><?= h($comment->roadtrip->title) ?></span>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($comment->point_of_interest)): ?>
                    <div class="info-box">
                        <i class="material-icons">place</i>
                        <div class="info-text">
                            <span class="info-label">Point d'intérêt</span>
                            <span class="info-value"><?= h($comment->point_of_interest->name) ?></span>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="info-box">
                    <i class="material-icons">schedule</i>
                    <div class="info-text">
                        <span class="info-label">Date de publication</span>
                        <span class="info-value"><?= $comment->created ? $comment->created->i18nFormat('dd/MM/yyyy à HH:mm') : 'Inconnue' ?></span>
                    </div>
                </div>
            </div>

            <div class="comment-content-box">
                <i class="material-icons quote-icon">format_quote</i>
                <div class="content-text">
                    <?= nl2br(h($comment->body ?? $comment->content)) ?>
                </div>
            </div>

        </div>

        <div class="card-actions">
            <?= $this->Html->link(
                '<i class="material-icons">arrow_back</i> Retour à mes avis',
                ['action' => 'index'],
                ['escape' => false, 'class' => 'action-btn btn-back']
            ) ?>

            <?= $this->Html->link(
                '<i class="material-icons">edit</i> Modifier',
                ['action' => 'edit', $comment->id],
                ['escape' => false, 'class' => 'action-btn btn-edit']
            ) ?>
        </div>

    </div>
</div>
