<?php
require_once __DIR__ . '/../include/init.php';

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


            <form action="" method="POST">
                <div class="form-group">
                    <label for="nom">Votre Nom</label>
                    <input type="text" id="nom" name="nom" required placeholder="Jean Dupont">
                </div>

                <div class="form-group">
                    <label for="email">Votre Email</label>
                    <input type="email" id="email" name="email" required placeholder="jean@exemple.com">
                </div>

                <div class="form-group">
                    <label for="sujet">Sujet</label>
                    <select id="sujet" name="sujet" required>
                        <option value="Question générale">Question générale</option>
                        <option value="Support technique / Bug">Support technique / Bug</option>
                        <option value="Partenariat">Partenariat</option>
                        <option value="Autre">Autre</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="message">Message</label>
                    <textarea id="message" name="message" required placeholder="Comment pouvons-nous vous aider ?"></textarea>
                </div>

                <button type="submit">Envoyer le message</button>
            </form>
        </div>

    </div>
</main>


<script src = "/../js/recherche.js" ></script>
<?php     
include_once __DIR__ . "/../modules/footer.php";
?>

</body>
</html>
