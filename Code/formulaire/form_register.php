<?php
require_once __DIR__ . '/../bd/lec_bd.php'; 
session_start();


$required = ['name', 'firstname', 'email', 'password', 'confirm_password', 
             'address', 'postal', 'town', 'phone', 'birthdate'];

foreach ($required as $field) {
    if (empty($_POST[$field])) {
        die("<p>Veuillez remplir tous les champs obligatoires.</p>
            <p><a href='../id.php'>Retour</a></p>");
    }
}

$name        = trim($_POST['name']);
$firstname   = trim($_POST['firstname']);
$email       = trim($_POST['email']);
$password    = trim($_POST['password']);
$confirm     = trim($_POST['confirm_password']);
$address     = trim($_POST['address']);
$postal      = trim($_POST['postal']);
$town        = trim($_POST['town']);
$phone       = trim($_POST['phone']);
$birthdate   = trim($_POST['birthdate']);


if ($password !== $confirm) {
    die("<p>Les mots de passe ne correspondent pas.</p>
         <p><a href='../id.php'>Retour</a></p>");
}


$check = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = :email");
$check->execute(['email' => $email]);

if ($check->fetch()) {
    die("<p>Cet email est déjà utilisé.</p>
         <p><a href='../id.php'>Retour</a></p>");
}



$password_hash = password_hash($password, PASSWORD_DEFAULT);


$image_name = null;

if (!empty($_FILES['image']['name'])) {

    $upload_dir = __DIR__ . '/../img/';

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $tmp_name = $_FILES['image']['tmp_name'];
    $original_name = basename($_FILES['image']['name']);

    $image_name = time() . "_" . $original_name;
    $path = $upload_dir . $image_name;

    if (!move_uploaded_file($tmp_name, $path)) {
        $image_name = null; 
    }
}


$sql = "INSERT INTO utilisateurs 
        (nom, prenom, email, mot_de_passe, adresse, postal, ville, tel, date_naissance, photo_profil)
        VALUES 
        (:nom, :prenom, :email, :mot_de_passe, :adresse, :postal, :ville, :tel, :date_naissance, :photo_profil)";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':nom'            => $name,
    ':prenom'         => $firstname,
    ':email'          => $email,
    ':mot_de_passe'   => $password_hash,
    ':adresse'        => $address,
    ':postal'         => $postal,
    ':ville'          => $town,
    ':tel'            => $phone,
    ':date_naissance' => $birthdate,
    ':photo_profil'   => $image_name
]);


$_SESSION['utilisateur'] = [
    'id'       => $pdo->lastInsertId(),
    'nom'      => $name,
    'prenom'   => $firstname,
    'email'    => $email
];

header('Location: /index.php');
exit;
?>
