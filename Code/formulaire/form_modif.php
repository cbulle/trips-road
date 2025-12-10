<?php
require_once __DIR__ . '/../modules/init.php';
require_once __DIR__ . '/../bd/lec_bd.php';

if (!isset($_SESSION['utilisateur'])) {
    header("Location: ../id.php");
    exit;
}

$user = $_SESSION['utilisateur'];

$user['photo_profil'] = $user['photo_profil'] ?? '';

if (empty($_POST['name']) || empty($_POST['firstname']) || empty($_POST['email'])) {
    die("Veuillez remplir tous les champs obligatoires. <a href='../profil.php'>Retour</a>");
}

$pseudo      = trim($_POST['pseudo']);
$name        = trim($_POST['name']);
$firstname   = trim($_POST['firstname']);
$email       = trim($_POST['email']);
$address     = trim($_POST['address'] ?? "");
$postal      = trim($_POST['postal'] ?? "");
$town        = trim($_POST['town'] ?? "");
$phone       = trim($_POST['phone'] ?? "");
$birthdate   = trim($_POST['birthdate'] ?? "");


$new_password = $_POST['password'] ?? "";
$confirm_password = $_POST['confirm_password'] ?? "";
$password_hash = null;


if (!empty($new_password)) {
    if ($new_password !== $confirm_password) {
        die("Les mots de passe ne correspondent pas. <a href='../profil.php'>Retour</a>");
    }
    $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
}


$upload_dir = __DIR__ . '/../uploads/pp/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$new_photo = $user['photo_profil']; 

if (isset($_FILES['image']) && !empty($_FILES['image']['name'])) {

    if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        die("Erreur lors de l'upload de l'image. Code erreur: " . $_FILES['image']['error']);
    }

    $tmp   = $_FILES['image']['tmp_name'];
    $nameF = basename($_FILES['image']['name']);
    $ext   = strtolower(pathinfo($nameF, PATHINFO_EXTENSION));

    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (!in_array($ext, $allowed)) {
        die("Extension d’image non autorisée.");
    }

    if (getimagesize($tmp) === false) {
        die("Le fichier envoyé n'est pas une image valide.");
    }

    $filename = uniqid("pp_") . "." . $ext;
    $pathFinal = $upload_dir . $filename;

    if (!move_uploaded_file($tmp, $pathFinal)) {
        die("Erreur lors de l’upload de la nouvelle image.");
    }

    if (!empty($user['photo_profil'])) {
        $oldFile = $upload_dir . $user['photo_profil'];
        if (file_exists($oldFile) && is_file($oldFile)) {
            @unlink($oldFile); 
        }
    }

    $new_photo = $filename;
}

$sql = "UPDATE utilisateurs SET 

        pseudo = :pseudo,
        nom = :nom,
        prenom = :prenom,
        email = :email,
        adresse = :adresse,
        postal = :postal,
        ville = :ville,
        tel = :tel,
        date_naissance = :date_naissance,
        photo_profil = :photo_profil";

if ($password_hash) {
    $sql .= ", mot_de_passe = :mot_de_passe";
}

$sql .= " WHERE id = :id";

$stmt = $pdo->prepare($sql);

$params = [
    ':pseudo'           => $pseudo,
    ':nom'            => $name,
    ':prenom'         => $firstname,
    ':email'          => $email,
    ':adresse'        => $address,
    ':postal'         => $postal,
    ':ville'          => $town,
    ':tel'            => $phone,
    ':date_naissance' => $birthdate,
    ':photo_profil'   => $new_photo,
    ':id'             => $user['id']
];

if ($password_hash) {
    $params[':mot_de_passe'] = $password_hash;
}

$stmt->execute($params);

$_SESSION['utilisateur'] = [
    'pseudo'         => $pseudo,
    'id'             => $user['id'],
    'nom'            => $name,
    'prenom'         => $firstname,
    'email'          => $email,
    'adresse'        => $address,
    'postal'         => $postal,
    'ville'          => $town,
    'tel'            => $phone,
    'date_naissance' => $birthdate,
    'photo_profil'   => $new_photo
];

header("Location: ../profil.php?updated=1");
exit;
