<?php
require_once __DIR__ . '/lec_bd.php'; // Votre connexion $pdo

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$userId = $_SESSION['utilisateur']['id'] ?? null;

// --- REQUÊTE SQL INTELLIGENTE ---
// Elle récupère :
// 1. Les road trips Publics
// 2. Vos propres road trips
// 3. Les road trips "Amis" SI une relation 'accepte' existe dans la table 'amis'
$sql = '
    SELECT DISTINCT r.*, u.pseudo 
    FROM roadtrip r
    JOIN utilisateurs u ON r.id_utilisateur = u.id
    WHERE 
        r.visibilite = "public"
        
        OR (r.id_utilisateur = :userId)
        
        OR (
            r.visibilite = "amis" 
            AND EXISTS (
                SELECT 1 FROM amis a 
                WHERE a.statut = "accepte" 
                AND (
                    (a.id_utilisateur = :userId AND a.id_ami = r.id_utilisateur)
                    OR 
                    (a.id_utilisateur = r.id_utilisateur AND a.id_ami = :userId)
                )
            )
        )
    ORDER BY r.date_creation DESC
';

$stmt = $pdo->prepare($sql);

if ($userId !== null) {
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
} else {

    $zero = 0;
    $stmt->bindParam(':userId', $zero, PDO::PARAM_INT);
}

$stmt->execute();
$roadtrips = $stmt->fetchAll(PDO::FETCH_ASSOC);

$response = [
    "userId" => $userId, 
    "roadtrips" => $roadtrips
];

header('Content-Type: application/json');
echo json_encode($response);
exit;
?>