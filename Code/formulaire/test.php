<?php

use PHPMailer\PHPMailer\PHPMailer;

require __DIR__ . '/PHPMailer-master/src/Exception.php';
require __DIR__ . '/PHPMailer-master/src/PHPMailer.php';
require __DIR__ . '/PHPMailer-master/src/SMTP.php';

$mail = new PHPMailer(true);

$mail->SMTPDebug = 2;
$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = 'tripsandroad@gmail.com';
$mail->Password = 'gnxsmalnudkijbys';
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 587;

$mail->setFrom('tripsandroad@gmail.com', 'Test');
$mail->addAddress('tripsandroad@gmail.com');

$mail->Subject = 'Test SMTP Gmail';
$mail->Body    = 'Si tu reçois ce mail, SMTP fonctionne.';

$mail->send();

echo 'MAIL ENVOYÉ';
