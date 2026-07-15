# Networkee

Un mini réseau social francophone — partage de posts, likes, commentaires et profils.

## Stack

- **Backend**: PHP 8.2 (serveur de développement intégré)
- **Database**: PostgreSQL Replit (via `pdo_pgsql`)
- **Frontend**: CSS custom moderne, police Inter, icônes SVG inline
- **Uploads**: stockées localement dans `uploads/`

## Design actuel

Style **Modern & Épuré** intégré depuis le mockup approuvé sur le Canvas :
- Navbar blanche avec effet `backdrop-blur`, logo "N" stylisé
- Fond gris très clair (`#f8fafc`), cartes blanches avec ombres douces
- Accent teal (`#0d9488`) pour les boutons, liens et états actifs
- Avatars générés automatiquement avec initiales et gradients
- Formulaires d'auth centrés, profil avec stats, fil avec composer et commentaires

## Lancer l'application

Workflow : `Start application` → `php -S 0.0.0.0:5000`

Point d'entrée : `loader.php` → `main.php`

Pages principales dans `pages/` :
- `home.php` — le fil de posts
- `profile.php` — profil et création de posts
- `edit-profile.php` — modification de la bio et de la photo
- `login.php` / `register.php` — authentification AJAX
- `logout.php` — déconnexion

## Base de données

Utilise la base PostgreSQL intégrée de Replit. La connexion se fait via les variables `PGHOST`, `PGPORT`, `PGDATABASE`, `PGUSER`, `PGPASSWORD` dans `config/database.php`.

Tables : `users`, `posts`, `likes`, `comments`

Le fichier `database/schema.pgsql.sql` permet de recréer le schéma sur une base vierge.

## Helpers UI

`includes/helpers.php` fournit :
- `renderAvatar($username, $size)` — avatars avec initiales et gradient
- `renderIcon($name, $size)` — icônes SVG inline (heart, message, image, smile, send, more, chevrons)
- `timeAgo($date)` — dates relatives en français
- `hasUserLikedPost($postId, $userId, $pdo)`

## Préférences utilisateur

(none yet)
