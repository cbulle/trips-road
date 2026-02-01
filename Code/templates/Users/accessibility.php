<?php
$this->assign('mainClass', 'accessibilite-container')
?>

<aside class="profil-sidebar">
    <div class="user-brief">
        <?php /*
        $photoProfil = !empty($_SESSION['utilisateur']['photo_profil']) ? htmlspecialchars($_SESSION['utilisateur']['photo_profil']) : "User.png";
        $photoUrl = (file_exists(WEBROOT . "/uploads/pp/$photoProfil")) ? "/uploads/pp/$photoProfil" : "img/User.png";
        */?>
        <div class="avatar-circle small" style="background-image: url('<?= "" ?>');"></div>
        <h3><?= "Test". ' ' . "Test" ?></h3>
    </div>

    <nav class="profil-nav">
        <ul>
            <li><a href="/explore">Mes Road-Trips</a></li>
            <li><a href="/view" class="">Paramètres du compte</a></li>
            <li><a href="/accessibility" class="active">Accessibilité</a></li>
            <li><a href="/logout" class="logout">Déconnexion</a></li>
        </ul>
    </nav>
</aside>


<section class="cont_access">
    <form id="AccessForm" class="AccessForm" action="" method="post">
        <h2 id="login-title">Accessibilité</h2>

        <label for="checkboxSombre">Mode sombre :</label>
        <div class="btnSombre">
            <label class="switch">
                <input type="checkbox" id="checkboxSombre"/>
                <div class="slider round"></div>
            </label>
        </div>

        <label for="checkboxMalvoyant">Mode malvoyant :</label>
        <div class="btnMalvoyant">
            <label class="switch">
                <input type="checkbox" id="checkboxMalvoyant"/>
                <div class="slider round"></div>
            </label>
        </div>

        <label for="checkboxD">Mode daltonien :</label>
        <div class="daltonism-options">
            <label for="protanopia">
                <input type="radio" name="daltonism-type" value="protanopia"
                       id="protanopia" <?php echo (isset($_COOKIE['typeDaltonien']) && $_COOKIE['typeDaltonien'] == 'protanopia') ? 'checked' : ''; ?>>
                Protanopie (Rouge/Vert)
            </label>
            <label for="deuteranopia">
                <input type="radio" name="daltonism-type" value="deuteranopia"
                       id="deuteranopia" <?php echo (isset($_COOKIE['typeDaltonien']) && $_COOKIE['typeDaltonien'] == 'deuteranopia') ? 'checked' : ''; ?>>
                Deutéranopie (Rouge/Vert)
            </label>
            <label for="tritanopia">
                <input type="radio" name="daltonism-type" value="tritanopia"
                       id="tritanopia" <?php echo (isset($_COOKIE['typeDaltonien']) && $_COOKIE['typeDaltonien'] == 'tritanopia') ? 'checked' : ''; ?>>
                Tritanopie (Bleu/Jaune)
            </label>
        </div>
        <div class="btnD">
            <label class="switch">
                <input type="checkbox" id="checkboxD" aria-label="Activer le mode daltonien">
                <span class="slider round"></span>
            </label>
        </div>


    </form>
</section>

