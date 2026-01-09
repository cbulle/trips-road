<?php
require_once __DIR__ . '/../include/init.php';
// Inclure PHPMailer (ajustez le chemin selon votre dossier réel)
require_once __DIR__ . '/PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer-master/src/SMTP.php';
require_once __DIR__ . '/PHPMailer-master/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['email'])) {
    $email = $_POST['email'];

    
    $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        $token = bin2hex(random_bytes(32)); 
        $token_hash = hash('sha256', $token); 
        $expiry = date('Y-m-d H:i:s', time() + 60 * 30); 

        $update = $pdo->prepare("UPDATE utilisateurs SET reset_token_hash = ?, reset_expires_at = ? WHERE id = ?");
        $update->execute([$token_hash, $expiry, $user['id']]);

        $mail = new PHPMailer(true);
        try {
           
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com'; 
            $mail->SMTPAuth   = true;
            $mail->Username   = 'tripsandroad@gmail.com';
            $mail->Password   = 'gnxsmalnudkijbys';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            
            $mail->setFrom('no-reply@tripsandroads.com', 'Trips & Road');
            $mail->addAddress($email);
            
            $link = "http://localhost/road-trip/Code/reset_password.php?token=" . $token;
            
            $mail->isHTML(true);
            $mail->Subject = 'Reinitialisation de mot de passe';
            $mail->Body    = "Cliquez ici pour changer votre mot de passe : <a href='$link'>$link</a>";

            $mail->send();
            echo "Email envoyé. Vérifiez votre boîte de réception.";
        } catch (Exception $e) {
            echo "Erreur lors de l'envoi : {$mail->ErrorInfo}";
        }
    } else {
        echo "Si cet email existe, un lien a été envoyé.";
    }
}