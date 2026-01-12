<?php
require_once __DIR__ . '/../include/init.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Cookies - Trips & Road</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/page_link.css">
</head>
<body>
   
<?php     
include_once __DIR__ . "/../modules/header.php";
?>

<main>
    <div class="cgu-container">
        <h1>Gestion des Cookies</h1>

        <section>
            <h2>1. Qu'est-ce qu'un cookie ?</h2>
            <p>Un cookie est un petit fichier texte déposé sur votre terminal (ordinateur, tablette ou mobile) lors de la visite d'un site web. Il permet au site de mémoriser certaines informations vous concernant, comme votre statut de connexion ou vos préférences d'affichage, pour une durée limitée.</p>
        </section>

        <section>
            <h2>2. Pourquoi utilisons-nous des cookies ?</h2>
            <p>Chez <strong>Trips & Road</strong>, nous utilisons des cookies pour des raisons purement techniques et fonctionnelles. Nous ne faisons <strong>pas de publicité ciblée</strong> et ne revendons pas vos données de navigation.</p>
            <p>Nos cookies servent principalement à :</p>
            <ul>
                <li>Vous maintenir connecté lorsque vous naviguez de page en page.</li>
                <li>Sécuriser votre compte (éviter qu'une autre personne n'utilise votre session).</li>
                <li>Vous reconnaître lors de votre prochaine visite (si vous avez coché "Se souvenir de moi").</li>
            </ul>
        </section>

        <section>
            <h2>3. Liste des cookies utilisés</h2>
            <table class="cookie-table">
                <thead>
                    <tr>
                        <th>Nom du Cookie</th>
                        <th>Type / Finalité</th>
                        <th>Durée de vie</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>PHPSESSID</strong></td>
                        <td><strong>Essentiel.</strong> Identifiant unique de votre session. Permet de savoir que vous êtes connecté lorsque vous changez de page.</td>
                        <td>Fin de la session (fermeture du navigateur)</td>
                    </tr>
                    <tr>
                        <td><strong>remember_me</strong></td>
                        <td><strong>Fonctionnel.</strong> Permet la reconnexion automatique sans ressaisir le mot de passe (si l'option a été cochée).</td>
                        <td>30 jours</td>
                    </tr>
                    <tr>
                        <td><strong>Cache / LocalStorage</strong></td>
                        <td><strong>Performance.</strong> Nous stockons temporairement les données cartographiques (OpenStreetMap) pour accélérer l'affichage de la carte.</td>
                        <td>Variable</td>
                    </tr>
                </tbody>
            </table>
        </section>

        <section>
            <h2>4. Cookies tiers (Partenaires)</h2>
            <p>Notre site utilise des services externes pour l'affichage des cartes et le calcul d'itinéraires. Ces services peuvent déposer leurs propres cookies techniques :</p>
            <ul>
                <li><strong>OpenStreetMap & Leaflet :</strong> Utilisés pour l'affichage visuel des cartes.</li>
                <li><strong>Nominatim & OSRM :</strong> Utilisés pour la recherche de lieux et le calcul de trajets.</li>
            </ul>
        </section>

        <section>
            <h2>5. Comment gérer vos préférences ?</h2>
            <p>La plupart des navigateurs acceptent les cookies par défaut. Vous pouvez toutefois configurer votre navigateur pour refuser les cookies ou supprimer ceux déjà installés. Voici comment faire selon votre navigateur :</p>
            <ul>
                <li><a href="https://support.google.com/chrome/answer/95647?hl=fr" target="_blank">Google Chrome</a></li>
                <li><a href="https://support.mozilla.org/fr/kb/protection-renforcee-contre-pistage-firefox-ordinateur" target="_blank">Mozilla Firefox</a></li>
                <li><a href="https://support.apple.com/fr-fr/guide/safari/sfri11471/mac" target="_blank">Safari</a></li>
                <li><a href="https://support.microsoft.com/fr-fr/microsoft-edge/supprimer-les-cookies-dans-microsoft-edge-63947406-40ac-c3b8-57b9-2a946a29ae09" target="_blank">Microsoft Edge</a></li>
            </ul>
            <p><em>Attention : Le refus des cookies techniques (comme PHPSESSID) vous empêchera de vous connecter à votre compte Membre.</em></p>
        </section>

        <section>
            <h2>6. Contact</h2>
            <p>Pour toute question relative à notre utilisation des cookies, vous pouvez nous contacter via notre <a href="contact.php" style="color:#BF092F;">formulaire de contact</a> ou consulter notre <a href="politique.php" style="color:#BF092F;">Politique de Confidentialité</a>.</p>
        </section>
    </div>
</main>

<script src = "/../js/recherche.js" ></script>
<?php     
include_once __DIR__ . "/../modules/footer.php";
?>

</body>
</html>
