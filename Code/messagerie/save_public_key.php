<?php
// ========================================
// FICHIER 1: formulaire/save_public_key.php
// Enregistre la clé publique d'un utilisateur
// ========================================
?>
<?php
require_once __DIR__ . '/../include/init.php';
include_once __DIR__ . '/../bd/lec_bd.php';
i/init.
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

// Vérifier authentification
if (!isset($_SESSION['utilisateur']['id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'Not authenticated'
    ]);
    exit;
}

// Récupérer les données POST
$data = json_decode(file_get_contents('php://input'), true);
$publicKey = $data['publicKey'] ?? null;

// Validation
if (!$publicKey) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'No public key provided'
    ]);
    exit;
}

// Valider le format de la clé (base64, 44 caractères)
if (!preg_match('/^[A-Za-z0-9+\/]{43}=$/', $publicKey)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Invalid public key format'
    ]);
    exit;
}

try {
    // Sauvegarder la clé publique
    $stmt = $pdo->prepare("
        UPDATE utilisateur 
        SET public_key = ? 
        WHERE id = ?
    ");
    $stmt->execute([$publicKey, $_SESSION['utilisateur']['id']]);
    
    // Log pour debug
    error_log("Clé publique sauvegardée pour utilisateur " . $_SESSION['utilisateur']['id']);
    
    echo json_encode([
        'success' => true,
        'message' => 'Public key saved successfully'
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    error_log("Erreur save_public_key: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'error' => 'Database error'
    ]);
}
?>