<?php
require_once __DIR__ . '/../modules/init.php';

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Contact- Trips & Road </title>
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
        <h1>Foire aux Questions</h1>

        <section class="faq">
            <h2>Questions fréquentes</h2>

            <div class="faq-item">
                <h3>1. Comment créer un road trip ?</h3>
                <p>Pour créer un road trip, vous devez d'abord vous inscrire sur notre site, puis utiliser notre interface intuitive pour planifier votre itinéraire, ajouter des points d'intérêt, et personnaliser votre voyage.</p>
            </div>

            <div class="faq-item">
                <h3>2. Est-ce que je peux partager mon road trip ?</h3>
                <p>Oui, une fois votre road trip créé, vous pouvez le partager avec d'autres utilisateurs de la plateforme. Il vous suffit de choisir l'option de partage dans les paramètres de votre road trip.</p>
            </div>

            <div class="faq-item">
                <h3>3. Comment puis-je accéder à la carte des points d'intérêt ?</h3>
                <p>Vous pouvez accéder à la carte des points d'intérêt en cliquant sur l'onglet "Carte" dans le menu principal. Vous y trouverez tous les lieux recommandés pour vos road trips.</p>
            </div>
             <section class="ask-question">
            <h2>Posez votre propre question</h2>

            <?php if (isset($message)): ?>
                <p class="confirmation-message"><?= $message ?></p>
            <?php endif; ?>

            <form action="/../formulaire/form_faq.php" method="POST">

                 <label for="nom">Nom :</label>
                <input type="text" name="nom" id="nom" value="<?= isset($nom) ? $nom : '' ?>" required>

                <label for="email">Email :</label>
                <input type="email" name="email" id="email" value="<?= isset($email) ? $email : '' ?>" required>

                <label for="sujet">Sujet :</label>
                <input type="text" name="sujet" id="sujet" value="<?= isset($sujet) ? $sujet : '' ?>" required>

                <label for="question">Votre question :</label>
                <textarea name="question" id="question" rows="4" required><?= isset($question) ? $question : '' ?></textarea>

                <button type="submit">Poser la question</button>
            </form>
        </section>
    </div>

            
</main>


<script src = "/../js/map.js" ></script>
<?php     
include_once __DIR__ . "/../modules/footer.php";
?>
<script>
<?php
if (isset($_SESSION['faq_success'])) {
    $msg = addslashes($_SESSION['faq_success']);
    echo "alert('{$msg}');";
    unset($_SESSION['faq_success']);
}

if (isset($_SESSION['faq_error'])) {
    $msg = addslashes(implode("\n", $_SESSION['faq_error']));
    echo "alert('Erreur:\\n{$msg}');";
    unset($_SESSION['faq_error']);
}
?>
</script>
</body>
</html>