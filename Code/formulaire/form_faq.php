<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PHPMailer-master/src/Exception.php';
require __DIR__ . '/PHPMailer-master/src/PHPMailer.php';
require __DIR__ . '/PHPMailer-master/src/SMTP.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['faq_error'] = ["Méthode non autorisée."];
    header('Location: /faq.php');
    exit;
}

$nom = isset($_POST['nom']) ? htmlspecialchars(trim($_POST['nom'])) : '';
$email = isset($_POST['email']) ? filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL) : '';
$sujet = isset($_POST['sujet']) ? htmlspecialchars(trim($_POST['sujet'])) : '';
$question = isset($_POST['question']) ? htmlspecialchars(trim($_POST['question'])) : '';

$errors = [];

if (empty($nom)) {
    $errors[] = "Le nom est obligatoire.";
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "L'email est invalide.";
}

if (empty($sujet)) {
    $errors[] = "Le sujet est obligatoire.";
}

if (empty($question)) {
    $errors[] = "Le message est obligatoire.";
}

if (!empty($errors)) {
    $_SESSION['faq_error'] = $errors;
    header('Location: /faq.php');
    exit;
}

$mail = new PHPMailer(true);

try {
    
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'tripsandroad@gmail.com';
    $mail->Password   = 'gnxsmalnudkijbys'; 
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

  
    $mail->setFrom('tripsandroad@gmail.com', 'Trips & Roads - FAQ');
    $mail->addAddress('tripsandroad@gmail.com');
    $mail->addReplyTo($email, $nom);
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

    $mail->send();

    $_SESSION['faq_success'] = "Votre message a bien été envoyé !";
    header('Location: /page_link/faq.php');
    exit;

} catch (Exception $e) {
    $_SESSION['faq_error'] = [
        "Erreur lors de l'envoi du message.",
        $mail->ErrorInfo
    ];
    header('Location: /page_link/faq.php');
    exit;
}
