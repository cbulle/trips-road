<?php
require_once __DIR__ . '/modules/init.php';

?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Road Trip Planner</title>
    <link rel="stylesheet" href="css/style.css"> 
</head>
<body>
   
<?php     
include_once __DIR__ . "/modules/header.php"
?>
        <div class="index_container">
            <h2>Bienvenue sur Trips & Roads !</h2>
            <p>Planifiez et partagez vos road trips facilement.</p>
            <a href="creationRoadTrip.php"><button type="submit">Créer un nouveau Road Trip</button></a>
        </div>
<?php     
include_once __DIR__ . "/modules/footer.php"
?>
</body>
</html>
