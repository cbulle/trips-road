<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PHPMailer-master/src/Exception.php';
require __DIR__ . '/PHPMailer-master/src/PHPMailer.php';
require __DIR__ . '/PHPMailer-master/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1️⃣ Récupérer et sécuriser les données du formulaire
    $nom = isset($_POST['nom']) ? htmlspecialchars(trim($_POST['nom'])) : '';
    $email = isset($_POST['email']) ? filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL) : '';
    $sujet = isset($_POST['sujet']) ? htmlspecialchars(trim($_POST['sujet'])) : '';
    $question = isset($_POST['question']) ? htmlspecialchars(trim($_POST['question'])) : '';

    // 2️⃣ Validation simple des champs
    $errors = [];
    if (empty($nom)) $errors[] = "Le nom est obligatoire.";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "L'email est invalide.";
    if (empty($sujet)) $errors[] = "Le sujet est obligatoire.";
    if (empty($question)) $errors[] = "Le message est obligatoire.";

    if (!empty($errors)) {
        echo "<ul style='color:red;'>";
        foreach ($errors as $error) {
            echo "<li>$error</li>";
        }
        echo "</ul>";
        exit;
    }

    // 3️⃣ Préparer PHPMailer
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'localhost';   // MailHog écoute sur localhost
        $mail->Port = 1025;          // Port par défaut de MailHog
        $mail->SMTPAuth = false;     // Pas besoin d'authentification pour MailHog

        // Expéditeur
        $mail->setFrom('no-reply@tripsandroads.local', 'Trips & Roads - FAQ');

        // Destinataire : ton adresse MailHog (ou tripsandroads@gmail.com si tu testes en ligne)
        $mail->addAddress('tripsandroads@gmail.com'); 

        // Réponse vers l’utilisateur
        $mail->addReplyTo($email, $nom);

        // Contenu du mail
        $mail->isHTML(false);
        $mail->Subject = "FAQ - $sujet | Message de $nom";
        $mail->Body = 
"Un utilisateur a envoyé une question via la FAQ :

Nom : $nom
Email : $email
Sujet : $sujet

Message :
$question
";

        // 4️⃣ Envoyer le mail
        $mail->send();

        // 5️⃣ Message de confirmation
        echo "<h2 style='color:green;'>Votre message a bien été envoyé !</h2>";
        echo "<a href='/faq.php'>Retour à la FAQ</a>";

    } catch (Exception $e) {
        echo "<h3 style='color:red;'>Erreur lors de l'envoi du message.</h3>";
        echo "<p>Détails : {$mail->ErrorInfo}</p>";
    }
} else {
    echo "Méthode non autorisée.";
}
?>
