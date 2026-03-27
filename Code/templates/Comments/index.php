<?php
$this->assign('mainClass', 'comments-page');

/**
 * View displaying the list of comments for the connected user.
 *
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Comment> $comments
 */
?>

<div class="comments-container">
    <h1 class="page-title">Mes Commentaires</h1>

    <?php if ($comments->isEmpty()): ?>
        <div class="empty-state">
            <i class="material-icons">chat_bubble_outline</i>
            <p>Vous n'avez publié aucun commentaire pour le moment.</p>
            <?= $this->Html->link(
                'Découvrir des roadtrips',
                ['controller' => 'Roadtrips', 'action' => 'publicRoadtrips'],
                ['class' => 'btn-discover']
            ) ?>
        </div>
    <?php else: ?>
        <div class="comments-grid">
            <?php foreach ($comments as $comment): ?>
                <div class="comment-card">
                    <div class="comment-header">
                        <div class="comment-author">
                            <?php
                            $profilePic = $comment->user->profile_picture ?? $comment->user->avatar ?? null;
                            $imgSrc = !empty($profilePic) ? 'uploads/pp/' . h($profilePic) : 'User.png';
                            ?>
                            <?= $this->Html->image($imgSrc, ['class' => 'avatar-circle small', 'alt' => 'Photo de profil']) ?>
                            <strong><?= h($comment->user->username ?? 'Anonyme') ?></strong>
                        </div>

                        <div class="comment-rating">
                            <?php
                            $rating = (int)$comment->rating;
                            for ($i = 1; $i <= 5; $i++) {
                                echo '<span class="star ' . ($i <= $rating ? 'filled' : 'empty') . '">★</span>';
                            }
                            ?>
                        </div>
                    </div>

                    <div class="comment-meta">
                        <?php if (!empty($comment->roadtrip)): ?>
                            <span class="meta-tag">
                                <i class="material-icons">map</i> <?= h($comment->roadtrip->title) ?>
                            </span>
                        <?php endif; ?>

                        <?php if (!empty($comment->point_of_interest)): ?>
                            <span class="meta-tag">
                                <i class="material-icons">place</i> <?= h($comment->point_of_interest->name) ?>
                            </span>
                        <?php endif; ?>

                        <span class="meta-date">
                            <i class="material-icons">schedule</i> <?= $comment->created->i18nFormat('dd/MM/yyyy HH:mm') ?>
                        </span>
                    </div>

                    <div class="comment-body">
                        <?= nl2br(h($comment->body)) ?>
                    </div>

                    <div class="comment-actions">
                        <?= $this->Html->link(
                            '<i class="material-icons">visibility</i> Voir',
                            ['action' => 'view', $comment->id],
                            ['escape' => false, 'class' => 'action-btn view']
                        ) ?>

                        <?= $this->Html->link(
                            '<i class="material-icons">edit</i> Modifier',
                            ['action' => 'edit', $comment->id],
                            ['escape' => false, 'class' => 'action-btn edit']
                        ) ?>

                        <?= $this->Form->postLink(
                            '<i class="material-icons">delete</i> Supprimer',
                            ['action' => 'delete', $comment->id],
                            ['confirm' => 'Supprimer définitivement ce commentaire ?', 'escape' => false, 'class' => 'action-btn delete']
                        ) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="pagination-container">
            <ul class="pagination">
                <?= $this->Paginator->first('<<') ?>
                <?= $this->Paginator->prev('<') ?>
                <?= $this->Paginator->numbers() ?>
                <?= $this->Paginator->next('>') ?>
                <?= $this->Paginator->last('>>') ?>
            </ul>
        </div>
    <?php endif; ?>
</div>
