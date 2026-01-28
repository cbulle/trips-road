<?php
require_once __DIR__ . '/../include/init.php'; 

header('Content-Type: application/json');

header('Content-Type: application/json');

// 1. Vérifier qu'on a bien reçu le jeton
if (!isset($_POST['credential'])) {
    echo json_encode(['success' => false, 'message' => 'Aucun jeton reçu']);
    exit;
}

$id_token = $_POST['credential'];
$client_id = "995499019090-anmh1d4m4obifri1fs9egue2p5417f8h.apps.googleusercontent.com";

// 2. Vérifier le jeton auprès de Google (Sécurité)
$url = "https://oauth2.googleapis.com/tokeninfo?id_token=" . $id_token;
$json = file_get_contents($url);
$payload = json_decode($json, true);

if (!$payload || isset($payload['error'])) {
    echo json_encode(['success' => false, 'message' => 'Jeton Google invalide']);
    exit;
}

// 3. Vérifier que c'est bien NOTRE application
if ($payload['aud'] !== $client_id) {
    echo json_encode(['success' => false, 'message' => 'Client ID incorrect']);
    exit;
}

// 4. Récupérer les infos de l'utilisateur
$google_id = $payload['sub'];
$email = $payload['email'];
$nom = $payload['family_name'] ?? 'Inconnu';
$prenom = $payload['given_name'] ?? 'Inconnu';
$photo = $payload['picture'] ?? '';

// 5. Vérifier si l'utilisateur existe déjà en BDD
try {
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // --- CAS 1 : IL EXISTE DÉJÀ ---
        // On met à jour son Google ID et sa photo si besoin
        $update = $pdo->prepare("UPDATE utilisateurs SET google_id = :gid, photo_profil = :photo WHERE id = :id");
        // Note: Pour la photo, on stocke l'URL Google directement si vous le souhaitez, 
        // ou on laisse l'ancienne. Ici je mets à jour l'URL si elle est vide ou différente.
        if (empty($user['photo_profil']) || strpos($user['photo_profil'], 'http') !== 0) {
             // Optionnel : ne pas écraser la photo si l'utilisateur en a mis une perso
        }
        $update->execute(['gid' => $google_id, 'photo' => $photo, 'id' => $user['id']]);

        // Connexion
        $_SESSION['utilisateur'] = $user;
        // On remet la photo à jour dans la session au cas où
        $_SESSION['utilisateur']['photo_profil'] = $photo; 
        
        echo json_encode(['success' => true]);

    } else {
        // --- CAS 2 : C'EST UN NOUVEAU ---
        // On l'inscrit
        $insert = $pdo->prepare("INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, google_id, photo_profil, date_inscription) VALUES (:nom, :prenom, :email, NULL, :gid, :photo, NOW())");
        $insert->execute([
            'nom' => $nom,
            'prenom' => $prenom,
            'email' => $email,
            'gid' => $google_id,
            'photo' => $photo
        ]);

        // On récupère son ID pour le connecter
        $newUserId = $pdo->lastInsertId();
        
        // On crée la session
        $_SESSION['utilisateur'] = [
            'id' => $newUserId,
            'nom' => $nom,
            'prenom' => $prenom,
            'email' => $email,
            'photo_profil' => $photo,
            'role' => 'user' // Si vous gérez les rôles
        ];

        echo json_encode(['success' => true]);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur BDD: ' . $e->getMessage()]);
}
?>