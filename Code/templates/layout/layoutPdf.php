<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= $this->fetch('title') ?></title>

    <style>
        <?= file_get_contents(WWW_ROOT . 'css' . DS . 'pdfStyle.css') ?>
    </style>

</head>
<body>
<?= $this->fetch('content') ?>

<div class="footer-pdf">
    Carnet de route généré le <?= date('d/m/Y') ?>
</div>

</body>
</html>
