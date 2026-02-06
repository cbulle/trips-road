<?php
// On charge l'environnement (BDD via init.php)
require_once __DIR__ . '/../include/init.php';

header('Content-Type: application/json');

// --- ETAPE 1 : Récupération et Vérification du Token Google ---

if (!isset($_POST['credential'])) {
    echo json_encode(['success' => false, 'message' => 'Aucun jeton reçu']);
    exit;
}

$id_token = $_POST['credential'];
// Votre Client ID (celui qui finit par apps.googleusercontent.com)
$client_id = "995499019090-anmh1d4m4obifri1fs9egue2p5417f8h.apps.googleusercontent.com";

// Appel à l'API Google pour décrypter le token
$url = "https://oauth2.googleapis.com/tokeninfo?id_token=" . $id_token;
$json = @file_get_contents($url);
$payload = json_decode($json, true);

if (!$payload || isset($payload['error'])) {
    echo json_encode(['success' => false, 'message' => 'Jeton Google invalide']);
    exit;
}

if ($payload['aud'] !== $client_id) {
    echo json_encode(['success' => false, 'message' => 'Client ID incorrect']);
    exit;
}

// --- ETAPE 2 : Définition des variables (C'est ce qu'il vous manquait !) ---
$google_id = $payload['sub'];
$email = $payload['email'];
$nom = $payload['family_name'] ?? 'Inconnu';
$prenom = $payload['given_name'] ?? 'Inconnu';
$photo = $payload['picture'] ?? '';

// --- ETAPE 3 : Interaction Base de Données ---

try {
    // On utilise $pdo qui vient de init.php -> lec_bd.php
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // UTILISATEUR EXISTANT : Mise à jour
        $sql = "UPDATE utilisateurs SET google_id = :gid";
        $params = ['gid' => $google_id, 'id' => $user['id']];

        // On ne met à jour la photo que si nécessaire
        if (empty($user['photo_profil']) || strpos($user['photo_profil'], 'http') === 0) {
            $sql .= ", photo_profil = :photo";
            $params['photo'] = $photo;
        }

        $sql .= " WHERE id = :id";
        $update = $pdo->prepare($sql);
        $update->execute($params);

        if (isset($params['photo'])) {
            $user['photo_profil'] = $photo;
        }

        $_SESSION['utilisateur'] = $user;
        echo json_encode(['success' => true]);

    } else {
        // NOUVEL UTILISATEUR : Inscription
        // Assurez-vous que la colonne date_inscription existe dans votre BDD (voir étape précédente)
        $insert = $pdo->prepare("INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, google_id, photo_profil, date_inscription) VALUES (:nom, :prenom, :email, NULL, :gid, :photo, NOW())");

        $insert->execute([
            'nom' => $nom,
            'prenom' => $prenom,
            'email' => $email,
            'gid' => $google_id,
            'photo' => $photo
        ]);

        $newUserId = $pdo->lastInsertId();

        $_SESSION['utilisateur'] = [
            'id' => $newUserId,
            'nom' => $nom,
            'prenom' => $prenom,
            'email' => $email,
            'photo_profil' => $photo,
        ];

        echo json_encode(['success' => true]);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur SQL : ' . $e->getMessage()]);
}
?>