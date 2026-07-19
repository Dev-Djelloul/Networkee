<div align="center">

# ∞ Networkee

**Le réseau des professionnels du digital.**

Partager, apprendre et trouver des opportunités — un mini réseau social façon LinkedIn,
pensé pour les métiers du web : développeurs, chefs de projet, UX designers.

[Démo en ligne](https://networkee.up.railway.app) · [Signaler un bug](https://github.com/Dev-Djelloul/Networkee/issues)

</div>

---

## Sommaire

- [Aperçu](#aperçu)
- [Fonctionnalités](#fonctionnalités)
- [Stack technique](#stack-technique)
- [Architecture](#architecture)
- [Installation locale](#installation-locale)
- [Déploiement](#déploiement)
- [Base de données](#base-de-données)
- [Scripts de maintenance](#scripts-de-maintenance)
- [Structure du projet](#structure-du-projet)

---

## Aperçu

Networkee réunit les professionnels du digital dans un seul endroit pour publier des
posts, réagir, se suivre, et parcourir ou publier des offres d'emploi. L'interface
s'inspire de LinkedIn : fil d'actualité, cartes de profil au survol, composer média,
thème clair/sombre, et un panneau flottant « Accès rapide » présent sur toutes les pages.

Le projet tourne sur **MySQL en local** (XAMPP / phpMyAdmin) et sur **PostgreSQL en
production** (Railway) — le même code s'adapte automatiquement au moteur détecté.

## Fonctionnalités

### Réseau social
- **Fil d'actualité** paginé, avec posts texte, image ou vidéo.
- **Composer média** façon LinkedIn : aperçu compact, contrôle de taille côté client
  (40 Mo pour les images, 100 Mo pour les vidéos) avant tout envoi.
- **Réactions** : likes, commentaires, repartages, avec listes au survol.
- **Suivi** d'autres membres et carte de profil au survol d'un nom dans le fil.
- **Enregistrement** de posts et d'offres, regroupés sur une page « Favoris ».

### Emploi
- **Offres d'emploi** filtrables par type (CDI, CDD, Freelance, Alternance, Stage).
- **Candidatures** avec message, visibles par l'auteur de l'offre.
- **Gestion** de ses propres offres (menu « … » : copier le lien, enregistrer, supprimer).

### Compte & profil
- Inscription / connexion (mots de passe hachés via `password_hash`).
- **Réinitialisation de mot de passe** par lien à jeton (table `password_resets`).
- Profil éditable : titre, localisation, compétences, bio, badge *Open to work*, avatar.

### Notifications
- Notification à chaque suivi, like, commentaire, repartage ou candidature.
- **Marquage au clic** : une notification devient lue quand on l'ouvre et redirige vers
  le contenu concerné ; bouton « Tout marquer comme lu » pour vider d'un coup.

### Interface
- **Thème clair / sombre** mémorisé (`localStorage`).
- **Recherche** de membres et d'offres.
- Panneau flottant « Accès rapide » sur toutes les pages.

## Stack technique

| Couche | Technologie |
|---|---|
| Back-end | PHP 8.2 (PDO, requêtes préparées) |
| Base de données | MySQL 8 (local) / PostgreSQL (production) |
| Front-end | HTML, CSS (variables custom, thème), JavaScript vanilla + jQuery |
| Serveur (prod) | Nginx + PHP-FPM dans un conteneur Docker |
| Hébergement | [Railway](https://railway.app) |

Aucun framework ni gestionnaire de dépendances : le projet est volontairement minimal
et lisible.

## Architecture

Le point d'entrée de chaque écran est un fichier dans `pages/` (ou `main.php` pour
l'accueil). Les vues incluent des composants partagés depuis `includes/` :

- `head.php` — `<head>`, feuilles de style et scripts communs.
- `header.php` — barre de navigation.
- `footer.php` — pied de page, qui inclut lui-même…
- `quick-widget.php` — le panneau « Accès rapide », donc présent partout sans duplication.

La connexion à la base est centralisée dans [`config/database.php`](config/database.php) :
il choisit PostgreSQL si la variable d'environnement `PGHOST` existe (cas Railway),
sinon MySQL en local. Toute la logique métier réutilisable (notifications, likes,
avatars, recherche…) vit dans [`includes/helpers.php`](includes/helpers.php).

## Installation locale

### Prérequis
- [XAMPP](https://www.apachefriends.org/) (Apache + MySQL + PHP 8.2)
- Un navigateur récent

### Étapes

1. **Cloner le dépôt** dans le dossier web de XAMPP :
   ```bash
   cd /Applications/XAMPP/xamppfiles/htdocs   # macOS ; adapter selon l'OS
   git clone https://github.com/Dev-Djelloul/Networkee.git
   ```

2. **Démarrer** Apache et MySQL depuis le panneau XAMPP.

3. **Créer la base** `networkee` et importer le schéma via phpMyAdmin, ou en ligne de
   commande :
   ```bash
   mysql -u root -e "CREATE DATABASE networkee CHARACTER SET utf8mb4;"
   mysql -u root networkee < database/networkee.sql
   ```

4. **Vérifier les identifiants** dans [`config/database.php`](config/database.php)
   (par défaut : hôte `localhost`, base `networkee`, utilisateur `root`, mot de passe vide).

5. **Ouvrir** [http://localhost/Networkee/main.php](http://localhost/Networkee/main.php).

> Un jeu de données de démonstration est disponible pour tester rapidement :
> voir [Scripts de maintenance](#scripts-de-maintenance).

## Déploiement

Le déploiement se fait sur **Railway** à partir du [`Dockerfile`](Dockerfile)
(PHP-FPM + Nginx, `php:8.2-fpm-alpine`).

Au démarrage du conteneur, [`database/init.php`](database/init.php) crée les tables
manquantes en PostgreSQL à partir de [`database/init.pgsql.sql`](database/init.pgsql.sql).
Ce script est **idempotent** et s'exécute à chaque redémarrage sans détruire de données.

### Variables d'environnement (fournies par Railway)

| Variable | Rôle |
|---|---|
| `PGHOST` | Hôte PostgreSQL — sa présence bascule le code en mode production |
| `PGPORT` | Port (défaut `5432`) |
| `PGDATABASE` | Nom de la base |
| `PGUSER` | Utilisateur |
| `PGPASSWORD` | Mot de passe |

> **Important** — une modification de schéma n'est déployée que si elle figure dans
> `init.pgsql.sql`. Une table créée à la main en local ne franchit jamais la frontière
> vers la production. Les logs de démarrage listent les tables présentes ou manquantes.

## Base de données

Deux dialectes maintenus en parallèle, même schéma logique :

- [`database/networkee.sql`](database/networkee.sql) — MySQL (local).
- [`database/init.pgsql.sql`](database/init.pgsql.sql) — PostgreSQL (production, idempotent).

**Tables :** `users`, `posts`, `likes`, `reposts`, `saved_posts`, `comments`,
`follows`, `notifications`, `job_offers`, `job_applications`, `saved_jobs`,
`password_resets`.

Les textes saisis par les utilisateurs sont stockés **bruts** ; l'échappement HTML se
fait uniquement à l'affichage (`htmlspecialchars`). Cette règle évite le double
encodage — ne jamais échapper avant l'insertion.

## Scripts de maintenance

Tous dans `database/`, à lancer en ligne de commande.

### Jeu de données de démonstration
Génère une notification de chaque type (suivi, like, commentaire, repartage,
candidature) avec les données réelles derrière, pour tester le parcours complet.
```bash
php database/seed-demo.php email@exemple.com   # cible un compte par email ou id
php database/seed-demo.php --clean             # retire les comptes de démo
```
Comptes créés : Sophie, Camille, Malik (`@networkee.test`, mot de passe `demo1234`).

### Correction du double encodage
Répare les textes doublement encodés en base (`&#039;` visible à l'écran, etc.).
Idempotent.
```bash
php database/fix-double-encoding.php --dry-run   # aperçu, sans écrire
php database/fix-double-encoding.php             # applique
# en production :
railway run php database/fix-double-encoding.php
```

## Structure du projet

```
Networkee/
├── main.php                 # Page d'accueil (hero, stats, CTA)
├── index.php · loader.php   # Points d'entrée
├── Dockerfile               # Image de production (PHP-FPM + Nginx)
├── nginx.conf · uploads.ini # Config serveur (limites d'upload, timeouts)
├── config/
│   └── database.php         # Connexion : PostgreSQL (prod) ou MySQL (local)
├── includes/                # Composants partagés (header, footer, helpers, widget…)
├── pages/                   # Écrans : fil, profil, offres, notifs, favoris, auth…
├── scripts/                 # JS front (composer, thème, menus, hover, modales…)
├── styles/                  # Feuilles de style (thème clair/sombre)
├── icons/ · images/         # Ressources graphiques
├── uploads/                 # Médias envoyés par les utilisateurs
└── database/                # Schémas SQL + scripts de maintenance
```

---

<div align="center">

Réalisé par [Djelloul Abid](https://github.com/Dev-Djelloul).

</div>
