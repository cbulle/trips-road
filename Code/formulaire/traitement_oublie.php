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

        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $basePath = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/');
        $link = $protocol . '://' . $host . $basePath . '/page_link/reset_password.php?token=' . $token;

        $mail = new PHPMailer(true);
        try {
           
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com'; 
            $mail->SMTPAuth   = true;
            $mail->Username   = 'tripsandroad@gmail.com';
            $mail->Password   = 'gnxsmalnudkijbys';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->CharSet    = 'UTF-8';

            
           $mail->setFrom('no-reply@tripsandroads.com', 'Trips & Roads');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Réinitialisation de mot de passe';
            $mail->Body    = "Pour changer votre mot de passe, <a href='$link'>cliquez ici</a>.";

            $mail->send();

            // On définit le message de succès
            $titreMessage = "Email envoyé !";
            $message = "Vérifiez votre boîte de réception (et vos spams). Le lien est valide 30 minutes.";
            $typeAlert = "success";

        } catch (Exception $e) {
            $titreMessage = "Erreur technique";
            $message = "L'envoi a échoué : " . htmlspecialchars($mail->ErrorInfo);
            $typeAlert = "error";
        }
    } else {
        // Message générique de sécurité
        $titreMessage = "Email envoyé !";
        $message = "Si cette adresse est enregistrée, vous recevrez un lien de réinitialisation.";
        $typeAlert = "success";
    }
} else {
    // Si accès direct sans POST, redirection
    header("Location: ../page_link/oublie.php");
    exit;
}
// Fin du traitement PHP, on passe à l'affichage HTML pur
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?php echo $titreMessage; ?> - Trips & Road</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .alert-box {
            max-width: 500px;
            margin: 100px auto;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            background: white;
        }
        .success { border-left: 5px solid #2ecc71; }
        .error { border-left: 5px solid #e74c3c; }
        h2 { margin-top: 0; color: #333; }
        .btn-retour {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #BF092F;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .btn-retour:hover { background-color: #a00727; }
    </style>
</head>
<body>

    <?php include_once __DIR__ . "/../modules/header.php"; ?>

    <main>
        <div class="alert-box <?php echo $typeAlert; ?>">
            <h2><?php echo htmlspecialchars($titreMessage); ?></h2>
            <p><?php echo htmlspecialchars($message); ?></p>
            
            <a href="../index.php" class="btn-retour">Retour à l'accueil</a>
        </div>
    </main>

    <?php include_once __DIR__ . "/../modules/footer.php"; ?>

</body>
</html>