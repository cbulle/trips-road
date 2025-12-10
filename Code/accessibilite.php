<?php
require_once __DIR__ . '/modules/init.php';

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href= "/css/style.css">
    <title>Accessibilité</title>
</head>
<body>
    <?php include __DIR__ . "/modules/header.php" ?>
    <div class="cont_access">
        <form id="AccessForm" class="AccessForm" action = " " method = "post">
        <h2 id="login-title">Accessibilité </h2>
        <!-- Bouton Mode nuit    -->   
        <label for="btnSombre" id="btnSombreLabel">Mode sombre :</label>
        <div class="btnSombre">
            <label class="switch" for="checkboxSombre">
                <input type="checkbox" id="checkboxSombre" />
                <div class="slider round"></div>
            </label>
        </div>
        <!-- Bouton Malvoyant -->
        <label for="btnMalvoyant" id="btnMalvoyantLabel">Mode malvoyant :</label>
        <div class="btnMalvoyant">
            <label class="switch" for="checkboxMalvoyant">
                <input type="checkbox" id="checkboxMalvoyant" />
                <div class="slider round"></div>
            </label>
        </div>


<?php include_once __DIR__ . "/modules/aside.php"?>  
    <?php include __DIR__ . "/modules/footer.php" ?>
    <script src="/js/map.js"></script>
</body>
</html>