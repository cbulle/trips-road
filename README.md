---

##  Trips & Roads - SAE Project

Plateforme collaborative de planification de voyages et de partage d'itinéraires.
Développé avec **CakePHP 5**, ce projet démontre une architecture robuste et l'intégration de services tiers .

---

## Description du projet  

Trips & Roads est un site web permettant aux utilisateurs de :  
- Créer et personnaliser leurs **Road Trips** (titre, étapes, dates, points d'intérêt).  
- **Partager** leurs parcours avec leurs amis ou les rendre **publics**.  
- Échanger via une **messagerie intégrée**.  
- Gérer un **profil utilisateur** (bio, photo, liste d’amis).  
- Découvrir les parcours de la communauté grâce à un flux public.  

L’application repose sur **OpenStreetMap** pour la cartographie et une architecture web moderne pour une expérience fluide et interactive.  

---


## Aperçu du Projet

Ce projet de SAE (Situation d'Apprentissage et d'Évaluation) consiste en une application web complète permettant aux utilisateurs de concevoir, personnaliser et partager des roadtrips. Au-delà du simple CRUD, l'application intègre un système de réseau social.

### Fonctionnalités Clés (High-Tech)


* **Moteur de Cartographie Interactif** : Intégration avancée de cartes pour la visualisation des étapes et des calculs de segments (distance/temps).
* **Social & Networking** :
* Système de gestion d'amitiés (demandes, acceptations, refus).
* Messagerie instantanée sécurisée entre utilisateurs.
* Gestion des favoris et partage public/privé des roadtrips.


* **Accessibilité & UX** : Gestion native du mode sombre et des types de daltonisme via un système de cookies et de préférences utilisateurs.
* **Sécurité des données** : Chiffrement des communications et système d'authentification robuste via le middleware CakePHP.

---

## Fonctionnalités principales  

 **Authentification & gestion des utilisateurs**  
- Connexion, inscription et gestion des comptes.  
- Amis et réseau social intégré (si possible).  

 **Gestion de Road Trips**  
- Création et modification d’itinéraires.  
- Ajout d’étapes, de photos et d’informations pratiques.  
- Partage privé (amis) ou public.  

 **Messagerie**  
- Conversations entre amis.  
- Notifications de nouveaux messages.  

 **Profil utilisateur**  
- Personnalisation du profil.  
- Liste d’amis et historique des Road Trips créés.  

 **Exploration**  
- Recherche de Road Trips.  
- Accès aux parcours publics.  

---


## Stack Technique

| Technologie | Utilisation |
| --- | --- |
| **HTML 5 / CSS 3** | Affichage graphique. |
| **PHP 8.2 / CakePHP 5** | Architecture MVC, ORM puissant et Sécurité. |
| **MySQL / MariaDB** | Stockage données. |
| **Leaflet / Google Maps** | Affichage cartographique et géolocalisation. |
| **JavaScript (ES6+)** | Manipulation du DOM, gestion asynchrone des modales et API Fetch. |
| **UML / GitLab** | Gestion de projet. |
---

## Architecture Logicielle (MVC)

Le projet suit les standards de l'industrie avec une séparation stricte des responsabilités. Voici un aperçu de la logique implémentée dans nos contrôleurs :

* **`RoadtripsController`** : Le cœur de l'application. Gère la logique complexe de création, le filtrage par visibilité et l'interfaçage avec l'API Gemini pour la génération automatique.
* **`MessagesController`** : Implémente une logique de conversation bidirectionnelle avec gestion des états de lecture.
* **`FriendshipsController`** : Gère les relations sociales avec des requêtes SQL optimisées pour éviter les doublons de demandes d'amis.
* **`UsersController`** : Gestion complète du cycle de vie utilisateur : inscription, authentification, édition de profil et préférences d'accessibilité.

---

## Focus sur la Sécurité & Qualité

Pour ce projet, nous avons mis un point d'honneur sur :

1. **Validation des données** : Utilisation massive des `Validator` de CakePHP pour empêcher les injections et les données erronées.
2. **Protection CSRF & Formulaires** : Composants natifs activés pour prévenir les attaques Cross-Site Request Forgery.
3. **Gestion des droits** : Utilisation du `beforeFilter` et du composant `Authentication` pour restreindre l'accès aux données privées (roadtrips non publiés, messages personnels).

---

## Diagrammes UML inclus dans le projet  

- **Diagrammes de séquence** (connexion, inscription, messagerie, création de Road Trip, profil).  
- **Diagramme de classes** (structure globale du système).  
- **Diagramme d’états** (définition et transitions des états du système).  

---


## Installation (Développement)

1. **Cloner le dépôt**
```bash
git clone https://iutbg-gitlab.iutbourg.univ-lyon1.fr/sae-but2/2025-26/openstreetmap-bde/road-trip.git
cd Code

```


2. **Installer les dépendances**
```bash
composer install
npm install

```


3. **Configuration**
* Renommer `config/app_local.example.php` en `config/app_local.php`.



4. **Migrations**
```bash
bin/cake migrations migrate

```

4. **Mise en route**
```bash
.bin/cake server

```


---

## L'Équipe (SAE)

Développeur : Bulle Céleste / Lambert Sasha / Bayssat Adrien / Poncet Lenny  

