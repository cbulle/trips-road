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
        <label for="btnSombre" id="btnLabel">Mode sombre :</label>
        <div class="btnSombre">
        <label class="switch" for="checkboxSombre">
            <input type="checkbox" id="checkboxSombre" />
            <div class="slider round"></div>
            
        </label>
</div>
<script src="js/map.js"></script>  

 <?php     
include_once __DIR__ . "/modules/aside.php"
?>  
    <?php include __DIR__ . "/modules/footer.php" ?>
</body>
</html>