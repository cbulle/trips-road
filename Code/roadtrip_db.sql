-- Suppression des tables existantes (si elles existent)
DROP TABLE IF EXISTS signalements;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS commentaires;
DROP TABLE IF EXISTS messages;
DROP TABLE IF EXISTS amis;
DROP TABLE IF EXISTS roadtrip_points_interet;
DROP TABLE IF EXISTS points_interet;
DROP TABLE IF EXISTS sous_etape;
DROP TABLE IF EXISTS trajet;
DROP TABLE IF EXISTS roadtrip;
DROP TABLE IF EXISTS utilisateurs;

-- Table des utilisateurs
CREATE TABLE utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100),
    email VARCHAR(150) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    adresse VARCHAR(255) NOT NULL,
    postal INT NOT NULL,
    ville VARCHAR(255) NOT NULL,
    tel VARCHAR(20) NOT NULL,
    date_naissance DATE,
    photo_profil VARCHAR(255) DEFAULT NULL
);

-- Table des road trips
CREATE TABLE roadtrip (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    description TEXT,
    visibilite ENUM('public', 'amis', 'prive') DEFAULT 'public',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    id_utilisateur INT NOT NULL,
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id) ON DELETE CASCADE
);

-- Table des trajets
CREATE TABLE trajet (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero INT NOT NULL,
    titre VARCHAR(255) NOT NULL,
    depart VARCHAR(255) NOT NULL,
    arrivee VARCHAR(255) NOT NULL,
    date_trajet TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    mode_transport VARCHAR(50),
    road_trip_id INT NOT NULL,
    FOREIGN KEY (road_trip_id) REFERENCES roadtrip(id) ON DELETE CASCADE
);

-- Table des sous-étapes
CREATE TABLE sous_etape (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero INT NOT NULL,
    ville VARCHAR(255) NOT NULL,
    description TEXT,
    photos TEXT,
    trajet_id INT NOT NULL,
    FOREIGN KEY (trajet_id) REFERENCES trajet(id) ON DELETE CASCADE
);

-- Table des points d’intérêt
CREATE TABLE points_interet (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    description TEXT,
    latitude DECIMAL(10,7),
    longitude DECIMAL(10,7),
    categorie VARCHAR(100),
    id_createur INT,
    FOREIGN KEY (id_createur) REFERENCES utilisateurs(id) ON DELETE SET NULL
);

-- Table d’association road trip ↔ POI
CREATE TABLE roadtrip_points_interet (
    id_roadtrip INT,
    id_poi INT,
    PRIMARY KEY (id_roadtrip, id_poi),
    FOREIGN KEY (id_roadtrip) REFERENCES roadtrip(id) ON DELETE CASCADE,
    FOREIGN KEY (id_poi) REFERENCES points_interet(id) ON DELETE CASCADE
);

-- Table des amis
CREATE TABLE amis (
    id_utilisateur INT,
    id_ami INT,
    statut ENUM('en_attente', 'accepte', 'bloque', 'supprime') DEFAULT 'en_attente',
    date_relation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_utilisateur, id_ami),
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (id_ami) REFERENCES utilisateurs(id) ON DELETE CASCADE
);

-- Table de messagerie
CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_expediteur INT NOT NULL,
    id_destinataire INT NOT NULL,
    contenu TEXT NOT NULL,
    date_envoi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    lu BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (id_expediteur) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (id_destinataire) REFERENCES utilisateurs(id) ON DELETE CASCADE
);

-- Table des commentaires
CREATE TABLE commentaires (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_utilisateur INT NOT NULL,
    id_roadtrip INT DEFAULT NULL,
    id_poi INT DEFAULT NULL,
    texte TEXT NOT NULL,
    note INT CHECK (note BETWEEN 0 AND 5),
    date_commentaire TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (id_roadtrip) REFERENCES roadtrip(id) ON DELETE CASCADE,
    FOREIGN KEY (id_poi) REFERENCES points_interet(id) ON DELETE CASCADE
);

-- Table des notifications
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_utilisateur INT NOT NULL,
    message TEXT NOT NULL,
    lu BOOLEAN DEFAULT FALSE,
    date_notification TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id) ON DELETE CASCADE
);

-- Table des signalements
CREATE TABLE signalements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_signaleur INT NOT NULL,
    type_contenu ENUM('utilisateur', 'roadtrip', 'commentaire', 'poi') NOT NULL,
    id_cible INT NOT NULL,
    motif TEXT,
    date_signalement TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_signaleur) REFERENCES utilisateurs(id) ON DELETE CASCADE
);
