# Networkee

Un mini réseau social — a French-language PHP social network app with posts, likes, comments, and user profiles.

## Stack

- **Backend**: PHP 8.2 (built-in dev server)
- **Database**: Replit PostgreSQL (via `pdo_pgsql`)
- **Frontend**: Bootstrap 5 + vanilla JS/jQuery
- **Uploads**: stored locally in `uploads/`

## Running the app

The workflow `Start application` runs `php -S 0.0.0.0:5000` from the project root.

Entry point: `loader.php` → `main.php`

Pages live in `pages/`: `home.php`, `login.php`, `register.php`, `profile.php`, `edit-profile.php`, `logout.php`

## Database

Uses Replit's built-in PostgreSQL. Connection is configured via the `DATABASE_URL` environment variable in `config/database.php`.

Tables: `users`, `posts`, `likes`, `comments`

## Notes

- The original project was built for XAMPP/MySQL. It was migrated to PostgreSQL for Replit.
- All hardcoded `/networkee/` URL prefixes have been replaced with `/`.
- User-uploaded images are stored in `uploads/`.

## User preferences

(none yet)
