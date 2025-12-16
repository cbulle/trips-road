<?php
require_once __DIR__ . '/../modules/init.php';

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>À propos / Contact - Trips & Road</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/form.css">
    <link rel="stylesheet" href="../css/page_link.css">
</head>
<body>
   
<?php     
include_once __DIR__ . "/../modules/header.php";
?>

<main>
    <div class="cgu-container">
        <section class="about-section">
            <h1>À propos de Trips & Roads</h1>
            <p>Bienvenue sur Trips & Roads, la plateforme dédiée à la planification de vos road trips ! Nous vous offrons une interface simple et intuitive pour planifier vos voyages, découvrir des points d’intérêt, et partager vos itinéraires avec d'autres passionnés de voyage.</p>
            <p>Que vous soyez un voyageur expérimenté ou un débutant, notre plateforme vous aidera à créer des road trips inoubliables, tout en vous fournissant des outils pour personnaliser vos trajets selon vos préférences.</p>
            <p>Notre mission : faciliter la planification de vos aventures sur la route, tout en vous permettant de partager vos expériences avec la communauté.</p>
        </section>

        <section class="contact-section">
            <h2>Contactez-nous</h2>

            <?php if (isset($successMessage)): ?>
                <p class="success-message"><?= $successMessage ?></p>
            <?php elseif (isset($errorMessage)): ?>
                <p class="error-message"><?= $errorMessage ?></p>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <ul class="error-messages">
                    <?php foreach ($errors as $error): ?>
                        <li><?= $error ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <form action="/../formulaire/form_faq.php" method="POST">

                 <label for="nom">Nom :</label>
                <input type="text" name="nom" id="nom" value="<?= isset($nom) ? $nom : '' ?>" required>

                <label for="email">Email :</label>
                <input type="email" name="email" id="email" value="<?= isset($email) ? $email : '' ?>" required>

                <label for="sujet">Sujet :</label>
                <input type="text" name="sujet" id="sujet" value="<?= isset($sujet) ? $sujet : '' ?>" required>

                <label for="question">Votre Message</label>
                <textarea name="question" id="question" rows="4" required><?= isset($question) ? $question : '' ?></textarea>

                <button type="submit">Envoyer votre message</button>
            </form>
        </section>
    </div>
</main>


<script src = "/../js/map.js" ></script>
<?php     
include_once __DIR__ . "/../modules/footer.php";
?>

</body>
</html>
