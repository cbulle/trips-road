<h1>Commentaire #<?= $comment->id ?></h1>

<p><strong>Auteur :</strong> <?= h($comment->user->username ?? '') ?></p>
<p><strong>Roadtrip :</strong> <?= h($comment->roadtrip->title ?? '') ?></p>
<p><strong>Point d’intérêt :</strong> <?= h($comment->point_of_interest->name ?? '') ?></p>

<hr>

<p><?= nl2br(h($comment->content)) ?></p>

<hr>

<?= $this->Html->link('Modifier', ['action' => 'edit', $comment->id]) ?>
<?= $this->Html->link('Retour', ['action' => 'myRoadtrips']) ?>
