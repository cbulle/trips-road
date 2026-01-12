<?php
require_once __DIR__ . '/../include/init.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Conditions Générales d'Utilisation - Trips & Road </title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/page_link.css">
</head>
<body>
   
<?php     
include_once __DIR__ . "/../modules/header.php";
?>

<main>
    <div class="cgu-container">
        <h1>Conditions Générales d'Utilisation</h1>

        <p class="last-update">Dernière mise à jour : <?php echo date("d/m/Y"); ?></p>

        <section>
            <h2>1. Préambule et Objet</h2>
            <p>Les présentes Conditions Générales d'Utilisation (ci-après "CGU") ont pour objet de définir les modalités de mise à disposition des services du site <strong>"Trips & Roads"</strong> et les conditions d'utilisation du Service par l'Utilisateur.</p>
            <p>Ce projet a été réalisé dans un cadre pédagogique (IUT Lyon 1). L'accès et l'utilisation du site impliquent l'acceptation sans réserve des présentes CGU par l'Utilisateur.</p>
        </section>

        <section>
            <h2>2. Accès aux Services</h2>
            <p>Le site est accessible gratuitement à tout Utilisateur disposant d'un accès à Internet. Le site propose deux niveaux d'accès :</p>
            <ul>
                <li><strong>Visiteur :</strong> Consultation des cartes et des Road Trips publics.</li>
                <li><strong>Membre inscrit :</strong> Création de trajets, sauvegarde, accès à la messagerie, ajout d'amis et publication d'avis.</li>
            </ul>
            <p>L'éditeur met en œuvre tous les moyens pour assurer un accès de qualité au service 24h/24, 7j/7, mais ne peut être tenu responsable de tout dysfonctionnement du réseau ou des serveurs.</p>
        </section>

        <section>
            <h2>3. Responsabilité de l'Utilisateur</h2>
            <p>L'Utilisateur est seul responsable de l'utilisation qu'il fait des informations présentes sur le site. Il s'engage notamment à :</p>
            <ul>
                <li>Garder ses identifiants de connexion (email et mot de passe) confidentiels.</li>
                <li>Ne pas publier de contenus illicites, haineux, diffamatoires ou portant atteinte à la vie privée d'autrui via les fonctionnalités sociales (Chat, Commentaires, Description de trajet).</li>
                <li>Ne pas perturber le fonctionnement du site par des logiciels malveillants ou des attaques informatiques.</li>
            </ul>
        </section>

        <section>
            <h2>4. Propriété Intellectuelle et Contenus</h2>
            <p><strong>Contenu du Site :</strong> La structure générale, le graphisme, le code source et les logos de "Trips & Roads" sont la propriété exclusive de l'équipe de développement (Groupe 10 - IUT Lyon 1).</p>
            <p><strong>Contenu Utilisateur :</strong> L'Utilisateur conserve la propriété intellectuelle des photos et textes qu'il publie. Cependant, en publiant un contenu sur le site (ex: photo de Road Trip), l'Utilisateur concède à "Trips & Roads" le droit non exclusif de représenter et reproduire ce contenu sur le site.</p>
        </section>

        <section>
            <h2>5. Cartographie et Données de Navigation</h2>
            <p>Les services de cartographie et d'itinéraire reposent sur des données <strong>OpenStreetMap</strong> et l'API <strong>OSRM</strong>.</p>
            <p><strong>Avertissement important :</strong> Les itinéraires sont fournis à titre indicatif. "Trips & Roads" ne garantit pas l'exactitude, la fiabilité ou l'exhaustivité des données de navigation (travaux, sens interdits récents, météo). L'Utilisateur reste seul maître de son véhicule et doit respecter le Code de la route en toutes circonstances. La responsabilité de l'éditeur ne saurait être engagée en cas d'accident ou d'infraction.</p>
        </section>

        <section>
            <h2>6. Données Personnelles (RGPD)</h2>
            <p>Conformément au Règlement Général sur la Protection des Données, nous collectons certaines informations nécessaires au fonctionnement du service (Nom, Email, Trajets). Pour en savoir plus sur la gestion de vos données et vos droits, veuillez consulter notre <a href="politique.php" style="color:#BF092F;">Politique de Confidentialité</a>.</p>
        </section>

        <section>
            <h2>7. Modération et Sanctions</h2>
            <p>L'administration se réserve le droit de supprimer, sans préavis, tout contenu (message, trajet, photo) ne respectant pas les présentes CGU. En cas de manquements répétés ou graves, le compte de l'Utilisateur pourra être suspendu ou supprimé définitivement.</p>
        </section>

        <section>
            <h2>8. Liens Hypertextes</h2>
            <p>Le site peut contenir des liens vers des sites tiers. Nous n'exerçons aucun contrôle sur ces sites et déclinons toute responsabilité quant à leur contenu ou leurs pratiques.</p>
        </section>

        <section>
            <h2>9. Droit Applicable</h2>
            <p>Les présentes CGU sont soumises au droit français. En cas de litige, et à défaut d'accord amiable, la compétence est attribuée aux tribunaux compétents du ressort de la Cour d'Appel de Lyon.</p>
        </section>
    </div>
</main>


<?php     
include_once __DIR__ . "/../modules/footer.php";
?>
<script src = "/../js/recherche.js" ></script>
</body>
</html>
