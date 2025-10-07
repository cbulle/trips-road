-- ===========================================
-- Base de données : roadtrip_db
-- Projet : Site de création et partage de Road Trips
-- ===========================================

-- 1️⃣ Création de la base
CREATE DATABASE IF NOT EXISTS roadtrip_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE roadtrip_db;

-- 2️⃣ Table des utilisateurs
CREATE TABLE utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100),
    email VARCHAR(150) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    photo_profil VARCHAR(255) DEFAULT NULL,
    bio TEXT,
    date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3️⃣ Table des road trips
CREATE TABLE roadtrip (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    description TEXT,
    etapes TEXT, -- Liste des étapes sous forme de texte (JSON ou séparées par des virgules)
    visibilite ENUM('public', 'amis', 'prive') DEFAULT 'public',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    id_utilisateur INT NOT NULL,
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id) ON DELETE CASCADE
);

-- 4️⃣ Table des points d’intérêt (POI)
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

-- 5️⃣ Table d’association entre road trip et points d’intérêt
CREATE TABLE roadtrip_points_interet (
    id_roadtrip INT,
    id_poi INT,
    PRIMARY KEY (id_roadtrip, id_poi),
    FOREIGN KEY (id_roadtrip) REFERENCES roadtrip(id) ON DELETE CASCADE,
    FOREIGN KEY (id_poi) REFERENCES points_interet(id) ON DELETE CASCADE
);

-- 6️⃣ Table des amis (relation entre utilisateurs)
CREATE TABLE amis (
    id_utilisateur INT,
    id_ami INT,
    statut ENUM('en_attente', 'accepte', 'bloque') DEFAULT 'en_attente',
    date_relation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_utilisateur, id_ami),
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (id_ami) REFERENCES utilisateurs(id) ON DELETE CASCADE
);

-- 7️⃣ Table de messagerie
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

-- 8️⃣ Table des commentaires et avis
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

-- 9️⃣ Table des notifications
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_utilisateur INT NOT NULL,
    message TEXT NOT NULL,
    lu BOOLEAN DEFAULT FALSE,
    date_notification TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id) ON DELETE CASCADE
);

-- 🔟 Table des signalements (pour modération)
CREATE TABLE signalements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_signaleur INT NOT NULL,
    type_contenu ENUM('utilisateur', 'roadtrip', 'commentaire', 'poi') NOT NULL,
    id_cible INT NOT NULL,
    motif TEXT,
    date_signalement TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_signaleur) REFERENCES utilisateurs(id) ON DELETE CASCADE
);

-- ✅ Données de test (facultatif)
INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, bio)
VALUES 
('Dupont', 'Julie', 'julie@example.com', 'test1234', 'Étudiante passionnée de voyage'),
('Martin', 'Jean', 'jean@example.com', 'test1234', 'Aventurier curieux');

INSERT INTO roadtrip (titre, description, etapes, visibilite, id_utilisateur)
VALUES
('Road Trip dans le Sud', 'Découverte de la Provence et de la Côte d’Azur', 'Marseille,Nice,Cannes', 'public', 1);

INSERT INTO points_interet (nom, description, latitude, longitude, categorie)
VALUES
('Tour Eiffel', 'Monument emblématique de Paris', 48.8584, 2.2945, 'Culture');

INSERT INTO roadtrip_points_interet (id_roadtrip, id_poi) VALUES (1, 1);
