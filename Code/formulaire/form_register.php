<?php
/** @var PDO $pdo */

$required = ['pseudo','name', 'firstname', 'email', 'password', 'confirm_password', 
             'address', 'postal', 'town', 'phone', 'birthdate'];

foreach ($required as $field) {
    if (empty($_POST[$field])) {
        die("<p>Veuillez remplir tous les champs obligatoires.</p>
            <p><a href='../login'>Retour</a></p>");
    }
}
$pseudo      = trim($_POST['pseudo']);
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
         <p><a href='/login'>Retour</a></p>");
}


$check = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = :email");
$check->execute(['email' => $email]);

if ($check->fetch()) {
    die("<p>Cet email est déjà utilisé.</p>
         <p><a href='/login'>Retour</a></p>");
}



$password_hash = password_hash($password, PASSWORD_DEFAULT);


$photo_profil = null; 
$dossier_upload = WEBROOT . 'uploads/pp/';

if (!is_dir($dossier_upload)) {
    mkdir($dossier_upload, 0777, true);
}

if (!empty($_FILES['image']['name'])) {

    $fichier_tmp = $_FILES['image']['tmp_name'];
    $fichier_nom = basename($_FILES['image']['name']);

    $check = getimagesize($fichier_tmp);
    if ($check === false) {
        die("Le fichier n'est pas une image valide.");
    }

    $extension = strtolower(pathinfo($fichier_nom, PATHINFO_EXTENSION));
    $extensions_autorisees = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (!in_array($extension, $extensions_autorisees)) {
        die("Format d'image non autorisé.");
    }

    $photo_profil = uniqid('pp_') . "." . $extension;

    $chemin_final = $dossier_upload . $photo_profil;

    if (!move_uploaded_file($fichier_tmp, $chemin_final)) {
        die("Erreur lors de l'upload de l'image.");
    }
}


$sql = "INSERT INTO utilisateurs 
        (pseudo, nom, prenom, email, mot_de_passe, adresse, postal, ville, tel, date_naissance, photo_profil)
        VALUES 
        (:pseudo, :nom, :prenom, :email, :mot_de_passe, :adresse, :postal, :ville, :tel, :date_naissance, :photo_profil)";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':pseudo'         => $pseudo,
    ':nom'            => $name,
    ':prenom'         => $firstname,
    ':email'          => $email,
    ':mot_de_passe'   => $password_hash,
    ':adresse'        => $address,
    ':postal'         => $postal,
    ':ville'          => $town,
    ':tel'            => $phone,
    ':date_naissance' => $birthdate,
    ':photo_profil'   => $photo_profil
]);


$_SESSION['utilisateur'] = [
    'pseudo'    => $pseudo,
    'id'       => $pdo->lastInsertId(),
    'nom'      => $name,
    'prenom'   => $firstname,
    'email'    => $email,
    'photo_profil' => $photo_profil,
    'ville'     =>$town
];

header('Location: /');
exit;
?>
