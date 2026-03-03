<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Comment> $comments
 */
?>

<h1>Commentaires</h1>

<?= $this->Html->link(
    'Ajouter un commentaire',
    ['action' => 'add'],
    ['class' => 'button']
) ?>



<?php foreach ($comments as $comment): ?>
    <div class="comment-card">

        <div class="comment-header">
            <div class="comment-author">

                <?= $this->Html->image(
                    !empty($comment->user->avatar)
                        ? 'uploads/pp/' . h($comment->user->avatar)
                        : 'User.png',
                    ['class' => 'avatar-circle small',
                        'alt' => 'Photo de profil'
                    ]
                ) ?>

                <strong><?= h($comment->user->username ?? 'Anonyme') ?></strong>
            </div>

            <div class="comment-rating">
                <?php
                for ($i = 1; $i <= 5; $i++) {
                    echo $i <= $comment->rating ? '⭐' : '☆';
                }
                ?>
            </div>
        </div>

        <div class="comment-meta">
            <?php if ($comment->roadtrip): ?>
                <span>Roadtrip : <?= h($comment->roadtrip->title) ?></span>
            <?php endif; ?>

            <?php if ($comment->point_of_interest): ?>
                <span>Point d’intérêt : <?= h($comment->point_of_interest->name) ?></span>
            <?php endif; ?>

            <span class="comment-date">
                <?= $comment->created->i18nFormat('dd/MM/yyyy HH:mm') ?>
            </span>
        </div>

        <div class="comment-body">
            <?= nl2br(h($comment->body)) ?>
        </div>

        <div class="comment-actions">
            <?= $this->Html->link('Voir', ['action' => 'view', $comment->id]) ?>
            <?= $this->Html->link('Modifier', ['action' => 'edit', $comment->id]) ?>
            <?= $this->Form->postLink(
                'Supprimer',
                ['action' => 'delete', $comment->id],
                ['confirm' => 'Supprimer ce commentaire ?']
            ) ?>
        </div>

    </div>
<?php endforeach; ?>

<?= $this->Paginator->numbers() ?>
