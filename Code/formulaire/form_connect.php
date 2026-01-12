<?php
require_once __DIR__ . '/../include/init.php';
require_once __DIR__ . '/../bd/lec_bd.php';

$error = null; 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!empty($_POST['email']) && !empty($_POST['password'])) {

        $email = $_POST['email'];
        $password = $_POST['password'];
        $remember = isset($_POST['remember']);  

        $sql = "SELECT * FROM utilisateurs WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['mot_de_passe'])) {   

           $_SESSION['utilisateur'] = [

            'pseudo' => $user['pseudo'],
            'id' => $user['id'],
            'nom' => $user['nom'],  
            'prenom' => $user['prenom'],
            'email' => $user['email'],
            'adresse' => $user['adresse'],
            'postal' => $user['postal'],
            'ville' => $user['ville'],
            'tel' => $user['tel'],
            'date_naissance' => $user['date_naissance'],
            'photo_profil' => $user['photo_profil']
        ];


            if (isset($_POST['remember_me'])) {
    // 1. Générer deux tokens aléatoires
    $selector = bin2hex(random_bytes(10)); // Sert d'ID public pour le cookie
    $validator = bin2hex(random_bytes(32)); // Sert de mot de passe pour le cookie
    
    // 2. Créer le cookie : format "selecteur:validateur"
    // Expire dans 30 jours
    setcookie("remember_me", $selector . ":" . $validator, time() + (86400 * 30), "/", "", true, true); 
    // Note: les derniers true, true activent Secure (HTTPS) et HttpOnly (anti-XSS)

    // 3. Stocker le hash du validateur en BDD
    $hashed_validator = hash('sha256', $validator);
    $expiry = date('Y-m-d H:i:s', time() + (86400 * 30));

    $ins = $pdo->prepare("INSERT INTO user_tokens (selector, hashed_validator, user_id, expires_at) VALUES (?, ?, ?, ?)");
    $ins->execute([$selector, $hashed_validator, $user['id'], $expiry]);
}

            header("Location: ../index.php");
            exit;
        } else {
            $error = "Email ou mot de passe incorrect.";
        }

    } else {
        $error = "Veuillez remplir tous les champs.";
    }
}

if (isset($_SESSION['user_id'])) {
    echo "<script>const USER_ID = " . $_SESSION['user_id'] . ";</script>";
} else {
    echo "<script>const USER_ID = null;</script>";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Connexion</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include __DIR__ . '/../modules/header.php'; ?>

<main>
    <h2>Connexion</h2>

    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <p><a href="../id.php">Réessayer</a></p>
</main>

<?php include __DIR__ . '/../modules/footer.php'; ?>

</body>
</html>
