<?php
require_once 'connexion.php'; 
$titre = $_POST['titre'] ?? '';
$description = $_POST['description'] ?? '';
$etapes = $_POST['etapes'] ?? '';
$visibilite = $_POST['visibilite'] ?? 'public';

if (empty($titre)) {
    die("❌ Le titre du road trip est obligatoire !");
}

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

    echo "Road Trip enregistré avec succès ! <br>";
    echo "<a href='../frontend/creerRoad.php'>Retour</a>";

} catch (PDOException $e) {
    echo "❌ Erreur lors de l'enregistrement : " . $e->getMessage();
}
?>
