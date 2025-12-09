<?php
// Exemple de récupération de la ville après soumission du formulaire
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // La ville sélectionnée par l'utilisateur (sans faute de frappe !)
    $ville_choisie = $_POST['ville_destination']; 
    
    // Vous pouvez ensuite utiliser cette variable pour insérer l'étape dans votre base de données :
    // $stmt = $pdo->prepare("INSERT INTO etapes_roadtrip (ville) VALUES (?)");
    // $stmt->execute([$ville_choisie]);
    
    echo "L'étape $ville_choisie a été ajoutée à votre road trip !";
}
?>