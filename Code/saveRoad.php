<?php
require_once 'connexion.php'; // inclusion de la connexion à la BDD

// Récupération des données envoyées par le formulaire
$titre = $_POST['titre'] ?? '';
$description = $_POST['description'] ?? '';
$etapes = $_POST['etapes'] ?? '';
$visibilite = $_POST['visibilite'] ?? 'public';

// (optionnel) L’ID de l’utilisateur connecté (ex: à gérer plus tard avec une session)
$id_utilisateur = 1;

// Vérification minimale
if (empty($titre)) {
    die("❌ Le titre du road trip est obligatoire !");
}

// Préparation et exécution de la requête
try {
    $sql = "INSERT INTO roadtrip (titre, description, etapes, visibilite, id_utilisateur)
            VALUES (:titre, :description, :etapes, :visibilite, :id_utilisateur)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':titre' => $titre,
        ':description' => $description,
        ':etapes' => $etapes,
        ':visibilite' => $visibilite,
        ':id_utilisateur' => $id_utilisateur
    ]);

    echo "✅ Road Trip enregistré avec succès ! <br>";
    echo "<a href='../frontend/creerRoad.php'>Retour</a>";

} catch (PDOException $e) {
    echo "❌ Erreur lors de l'enregistrement : " . $e->getMessage();
}
?>
