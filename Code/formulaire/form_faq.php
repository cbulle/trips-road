<?php
// Si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Récupérer et sécuriser les données envoyées
    $nom = htmlspecialchars($_POST['nom']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $sujet = htmlspecialchars($_POST['sujet']);
    $question = htmlspecialchars($_POST['question']);
    
    // Validation des champs
    $errors = [];
    
    // Vérifier si le nom est vide
    if (empty($nom)) {
        $errors[] = "Le nom est requis.";
    }

    // Vérifier si l'email est valide
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'email est invalide.";
    }

    // Vérifier si le sujet est vide
    if (empty($sujet)) {
        $errors[] = "Le sujet est requis.";
    }

    // Vérifier si la question est vide
    if (empty($question)) {
        $errors[] = "La question est requise.";
    }

    // Si aucune erreur, traiter le formulaire
    if (empty($errors)) {
        // Exemple d'enregistrement dans la base de données ou envoi par email
        // (Ajoute ta logique pour enregistrer la question ici)
        
        // Message de confirmation
        $message = "Merci pour votre question. Nous reviendrons vers vous bientôt.";
    }
}
?>