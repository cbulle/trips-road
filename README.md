## Qualité du Code & Bonnes Pratiques

#### Le nommage
* **Ce qu'on a fait :** Standardisation et internationalisation du code, et prévention des conflits CSS.
* **Comment on l'a fait :** Nous avons traduit l'intégralité du code métier en anglais. Côté front-end, nous avons renommé les classes génériques pour éviter qu'elles n'héritent des styles globaux de CakePHP.
* **Où :** Dans le fichier `MessagesController.php` (utilisation de noms comme `sendMessage` et `friendId`), et dans `messagerie.css` et `view.php` où la classe `.message` a été remplacée par `.chat-bulle` pour cibler précisément le composant.

#### Commentaires / Docs
* **Ce qu'on a fait :** Nettoyage du code et mise en place de blocs de documentation stricts. Mise en place d'une documentation que vous pouvez retrouver ici : https://cbulle.github.io/trips-road/ contient les explications de toute les fonction et aussi le guide d'installation.
* **Comment on l'a fait :** Nous avons supprimé les commentaires redondants qui paraphrasaient le code et formaté les en-têtes de classes et de méthodes (PHPDoc) pour indiquer clairement les paramètres, les retours et les exceptions.
* **Où :** Dans `MessagesController.php` (ajout des balises `@param` et `@throws`) et dans l'entité `Message.php` (définition des balises `@property`).

#### La programmation défensive
* **Ce qu'on a fait :** Anticipation des comportements non autorisés et prévention des crashs d'interface.
* **Comment on l'a fait :** Mise en place de clauses de garde au début des fonctions pour bloquer les actions illogiques, et vérification de l'existence des variables avant l'affichage côté vue.
* **Où :** Dans `MessagesController.php` (méthode `start`), avec la levée d'une `ForbiddenException` si l'utilisateur essaie de se parler à lui-même ou s'ils ne sont pas amis. Dans `view.php`, avec la condition `if (!empty($friend))` avant le rendu de l'interface.

#### Gestion des Erreurs
* **Ce qu'on a fait :** Tolérance aux pannes (Fail-safe) et amélioration de l'UX lors des crashs.
* **Comment on l'a fait :** Nous avons capturé l'erreur liée à la corruption potentielle des données binaires en base pour renvoyer une chaîne de secours. Les pages d'erreurs 400 et 500 ont également été repensées et épurées de leurs éléments défectueux (icônes).
* **Où :** Dans `Message.php`, où la méthode `_getContent()` retourne `[Erreur de déchiffrement]` ou `[Message illisible]` en cas d'échec. Dans `error400.php`, `error500.php` et `error.css`, via la création d'un design centré et la suppression des icônes Material natives.

#### Débogage
* **Ce qu'on a fait :** Nettoyage des traces de développement pour la production.
* **Comment on l'a fait :** Suppression systématique de toutes les fonctions de debug utilisées pendant la phase de création.
* **Où :** Nettoyage général, par exemple avec le retrait du `dd($event)` commenté dans la méthode `beforeSave()` du fichier `MessagesTable.php`.

#### L'architecture
* **Ce qu'on a fait :** Respect du pattern MVC ("Fat Model, Skinny Controller") et isolation des composants d'interface.
* **Comment on l'a fait :** Le contrôleur a été allégé de toute la logique de cryptographie, qui a été déléguée au modèle. La logique d'affichage complexe de la barre latérale a été séparée dans une View Cell indépendante.
* **Où :** Dans l'entité `Message.php`, avec l'encapsulation du chiffrement via le mutateur `_setBody()` et l'accesseur virtuel `_getContent()`. Dans `MessageCell.php`, qui gère désormais l'extraction de la liste des amis et le tri chronologique.

#### Concepts de programmation
* **Ce qu'on a fait :** Exploitation avancée de l'ORM et application du principe DRY (Don't Repeat Yourself).
* **Comment on l'a fait :** Nous naviguons à travers les relations d'objets (associations) plutôt que de faire des requêtes manuelles ou d'utiliser répétitivement `fetchTable()`. Nous avons également mutualisé les composants de l'interface.
* **Où :** Dans `MessagesTable.php` avec la définition des relations `belongsTo` (`Senders`, `Recipients`), et dans `MessagesController.php` en récupérant l'utilisateur via `$this->Messages->Senders->get($userId)`. Le composant `MessageCell` est réutilisé dans `index.php` et `view.php`.