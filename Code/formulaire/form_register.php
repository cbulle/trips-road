<?php
require_once __DIR__ . '/bd/lec_bd.php'; 



if (!empty($_POST['email']) && !empty($_POST['name']) && !empty($_POST['password']) && !empty($_POST['address'])) {
    
    
    $name     = trim($_POST['name'] );
    $firstname = trim($_POST['firstname'] );
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);
    $address  = trim($_POST['address']);
    $postal   = trim($_POST['postal'] );
    $town     = trim($_POST['town'] );
    $phone    = trim($_POST['phone']);
    $birthdate = trim($_POST['birthdate']);
    $image_nom   = null;

    if (!empty($_FILES['image']['name'])) {
        $dossier = __DIR__ . '/uploads/';
        if (!is_dir($dossier)) {
            mkdir($dossier, 0777, true);
        }

        $image_nom = basename($_FILES['image']['name']);
        $chemin = $dossier . $image_nom;

        if (!move_uploaded_file($_FILES['image']['tmp_name'], $chemin)) {
            $image_nom = null; 
        }
    }





    $password_hash = password_hash($password, PASSWORD_DEFAULT);


    $sql = "INSERT INTO users (email, name, lastname, password, town, postal, address , image ,  active, updated)
            VALUES (:email, :name, :lastname, :password, :town, :postal, :address, :image , :active, :updated)";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':email'    => $email,
        ':name'     => $name,
        ':lastname' => $lastname,
        ':password' => $password_hash,
        ':town'     => $town,
        ':postal'   => $postal,
        ':address'  => $address,
         
        
    ]);

    header('Location: /index.php');

}else{
die("Veuillez remplir tous les champs obligatoires. <a href='abonnez-vous.php'>Retour</a>");

}
?>
