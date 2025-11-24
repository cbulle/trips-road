<?php
require_once __DIR__ . '/../modules/init.php';
header('Content-Type: application/json');
include __DIR__ . '/../bd/lec_bd.php';

try {
    // Décodage JSON reçu
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) throw new Exception('Requête JSON invalide');

    // Extraction des données attendues
    $titre = $data['titre'] ?? '';
    $description = $data['description'] ?? '';
    $visibilite = $data['visibilite'] ?? '';
    $villes = $data['villes'] ?? [];
    $trajets = $data['trajets'] ?? [];

    // Activation du mode exception pour PDO
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Vérification utilisateur connecté dans session
    if (isset($_SESSION['utilisateur']) && isset($_SESSION['utilisateur']['id'])) {
        $id_utilisateur = $_SESSION['utilisateur']['id'];
    } else {
        echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté']);
        exit;
    }

    // Insertion roadtrip
    $stmt = $pdo->prepare("INSERT INTO roadtrip (titre, description, visibilite, id_utilisateur) VALUES (?, ?, ?, ?)");
    $stmt->execute([$titre, $description, $visibilite, $id_utilisateur]);
    $roadTripId = $pdo->lastInsertId();

    $i = 0;
    foreach ($trajets as $trajet) {
        $depart = $trajet['depart'] ?? '???';
        $arrivee = $trajet['arrivee'] ?? '???';
        $mode = $trajet['mode'] ?? 'Voiture';

        // Insertion trajet
        $stmt = $pdo->prepare("INSERT INTO trajet (numero, titre, depart, arrivee, mode_transport, road_trip_id) VALUES (?, ?, ?, ?, ?, ?)");
        $titreTrajet = "$depart → $arrivee";
        $stmt->execute([$i, $titreTrajet, $depart, $arrivee, $mode, $roadTripId]);
        $trajetId = $pdo->lastInsertId();

        $j = 0;
        foreach ($trajet['sousEtapes'] ?? [] as $sousEtape) {
            $nom = $sousEtape['nom'] ?? '???';
            $remarque = $sousEtape['remarque'] ?? '';
            $heure = $sousEtape['heure'] ?? '';
            $typeTransport = $sousEtape['type_transport'] ?? 'par défaut'; // Valeur par défaut si non définie

            $stmt = $pdo->prepare("INSERT INTO sous_etape (numero, ville, description,trajet_id, type_transport) VALUES (?, ?, ?, ?, ?)");
            if (!$stmt->execute([$j, $nom, $remarque, $trajetId, $typeTransport])) {
                $errorInfo = $stmt->errorInfo();
                throw new Exception("Erreur lors de l'insertion de la sous-étape $j du trajet $i : " . implode(' ', $errorInfo));
            }
            $j++;
        }
        $i++;
    }

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}