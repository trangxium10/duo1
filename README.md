# DUO : Blog Personnel (Projet Étudiant)

Bienvenue sur le dépôt du projet **DUO**, un blog personnel conçu pour offrir une « *safe place* » aux étudiants étrangers souhaitant étudier en France. 

Ce projet a été réalisé dans le cadre de l'UE "Programmation Web et Introduction PHP" (Licence 2 MIASHS parcours MIAGE à l'Université Paris Nanterre) par :
- **Kissiedou Tyra**
- **Nguyen Thi Hoang Trang**

## 🌟 Concept
DUO est une plateforme permettant de partager des expériences, des histoires de vie (*lifestyle*) et des coups de cœur littéraires. L'objectif est de créer un espace bienveillant, ressourçant et riche en conseils pratiques pour aiguiller les étudiants à travers les défis émotionnels et logistiques de la vie étudiante dans un nouveau pays.

## 🛠️ Fonctionnalités Principales
- **Gestion de Contenu (CRUD) :** Interface d'administration réservée aux autrices pour créer, modifier, et supprimer des articles (avec upload d'images de couverture/thumbnails).
- **Contenu Premium (Paywall) :** Possibilité de restreindre l'accès de certains articles 🔒. La lecture des articles privés nécessite une inscription sur le site.
- **Catégorisation & Recherche :** Navigation fluide entre les thèmes "Livres" et "Lifestyle", complétée par un moteur de recherche en page d'accueil.
- **Espace Membre Sécurisé :** Inscription, connexion, et gestion du profil utilisateur. Les mots de passe sont hachés de manière cryptographique (SHA-256) pour garantir la sécurité des étudiants.

## 💻 Architecture Technique
Le site a été développé avec une approche dynamique reposant sur :
- **Frontend :** HTML5, Vanilla CSS3 (Design interactif, *cards*, palettes de couleurs pastel).
- **Backend :** PHP (Vanilla) pour la logique métier, la gestion des sessions, le traitement des formulaires et l'upload de fichiers.
- **Base de données :** MySQL (requêtes préparées PDO) structurée autour de deux tables principales : `utilisateurs` et `posts`.

## ⚙️ Installation en local
1. Clonez ce dépôt.
2. Placez le dossier du projet dans le répertoire de votre serveur local (ex: `htdocs` pour XAMPP ou `www` pour WAMP).
3. Importez la base de données : Créez une base de données nommée `do1` et exécutez votre script SQL (ou laissez le code PHP créer les tables dynamiquement s'il est configuré pour).
4. Accédez au site via `http://localhost/duo1/index.php`.

---
*Projet réalisé avec passion en Décembre 2025.*
