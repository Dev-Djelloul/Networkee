-- Networkee — schéma + données de démonstration (PostgreSQL)
-- Idempotent : peut être exécuté à chaque démarrage sans casser les données.

-- ─── Tables ────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
    id            SERIAL PRIMARY KEY,
    username      VARCHAR(50)  NOT NULL,
    email         VARCHAR(100) NOT NULL UNIQUE,
    password      VARCHAR(255) NOT NULL,
    profile_image VARCHAR(255) DEFAULT 'default.png',
    bio           TEXT         DEFAULT NULL,
    job_title     VARCHAR(120) DEFAULT NULL,
    location      VARCHAR(100) DEFAULT NULL,
    skills        VARCHAR(500) DEFAULT NULL,
    open_to_work  SMALLINT     NOT NULL DEFAULT 0,
    created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS posts (
    id         SERIAL PRIMARY KEY,
    user_id    INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    content    TEXT    NOT NULL,
    image      VARCHAR(255) DEFAULT NULL,
    video      VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Ajout rétroactif de la colonne video sur les bases déjà initialisées avant son introduction.
ALTER TABLE posts ADD COLUMN IF NOT EXISTS video VARCHAR(255) DEFAULT NULL;

CREATE TABLE IF NOT EXISTS likes (
    id         SERIAL PRIMARY KEY,
    post_id    INTEGER NOT NULL REFERENCES posts(id) ON DELETE CASCADE,
    user_id    INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (post_id, user_id)
);

CREATE TABLE IF NOT EXISTS reposts (
    id         SERIAL PRIMARY KEY,
    post_id    INTEGER NOT NULL REFERENCES posts(id) ON DELETE CASCADE,
    user_id    INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (post_id, user_id)
);

CREATE TABLE IF NOT EXISTS saved_posts (
    id         SERIAL PRIMARY KEY,
    post_id    INTEGER NOT NULL REFERENCES posts(id) ON DELETE CASCADE,
    user_id    INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (post_id, user_id)
);

CREATE TABLE IF NOT EXISTS comments (
    id         SERIAL PRIMARY KEY,
    post_id    INTEGER NOT NULL REFERENCES posts(id) ON DELETE CASCADE,
    user_id    INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    content    TEXT    NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS follows (
    id          SERIAL PRIMARY KEY,
    follower_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    followed_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (follower_id, followed_id)
);

CREATE TABLE IF NOT EXISTS notifications (
    id         SERIAL PRIMARY KEY,
    user_id    INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    actor_id   INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    type       VARCHAR(20) NOT NULL,
    post_id    INTEGER DEFAULT NULL,
    is_read    SMALLINT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS job_offers (
    id          SERIAL PRIMARY KEY,
    user_id     INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    title       VARCHAR(150) NOT NULL,
    company     VARCHAR(120) NOT NULL,
    location    VARCHAR(100) DEFAULT NULL,
    type        VARCHAR(20)  NOT NULL DEFAULT 'CDI',
    description TEXT         NOT NULL,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS job_applications (
    id           SERIAL PRIMARY KEY,
    job_offer_id INTEGER NOT NULL REFERENCES job_offers(id) ON DELETE CASCADE,
    user_id      INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    message      TEXT DEFAULT NULL,
    created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (job_offer_id, user_id)
);

CREATE TABLE IF NOT EXISTS password_resets (
    id         SERIAL PRIMARY KEY,
    user_id    INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    token_hash VARCHAR(64) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- ─── Données de démonstration (seed only if empty) ──────────────────────────
-- mots de passe : Alexandre=password, Sophie=123456, Hugo=azerty, Camille=networkee
INSERT INTO users (id, username, email, password, profile_image, bio, created_at)
SELECT * FROM (VALUES
    (1, 'Alexandre', 'alexandre@networkee.test', '$2y$10$CqTfGjBD6V3bjUS9AwIH8uBJkfXKi/vgqsVPqni6yUV7ZpWDt9s7C', NULL, 'Passionné par les nouvelles tech et les voyages 🚀', TIMESTAMP '2026-07-10 18:00:00'),
    (2, 'Sophie', 'sophie@networkee.test', '$2y$10$85AkzqXHPj0tsqzC8.M7/eVurrw/lEPh3O39YWfwsopP0tMtrfZ42', NULL, 'Amoureuse de la nature et de la photographie 📸🌲', TIMESTAMP '2026-07-11 12:30:00'),
    (3, 'Hugo', 'hugo@networkee.test', '$2y$10$LTx4YNKbNinUIMEskty.hOglU5F4iHPRA91/TV.aaIC5dIAeBDzwO', NULL, 'Toujours partant pour un café ou une expédition urbaine ☕️', TIMESTAMP '2026-07-12 09:15:00'),
    (4, 'Camille', 'camille@networkee.test', '$2y$10$Bsp/NCuMhD/BRp8bWIETDu.em6d7sXvZ5tdPQTxW/g1hZ2ba6S.sK', NULL, 'Design, code et conversations autour d''un thé 🍵', TIMESTAMP '2026-07-13 14:45:00')
) AS v
WHERE NOT EXISTS (SELECT 1 FROM users);

INSERT INTO posts (id, user_id, content, image, created_at)
SELECT * FROM (VALUES
    (1, 1, 'Salut tout le monde ! Trop content de vous retrouver sur Networkee 2.0 🚀 La nouvelle interface est vraiment dingue, non ? Plus claire, plus rapide... on adore !', NULL, TIMESTAMP '2026-07-15 08:30:00'),
    (2, 2, 'Petite balade en forêt ce matin pour se ressourcer. Rien de tel pour commencer la semaine ! 🌲🍃', NULL, TIMESTAMP '2026-07-15 10:15:00'),
    (3, 3, 'Qui est chaud pour un café cet aprem ? Je suis dans le 11ème, on se capte ? ☕️☀️', NULL, TIMESTAMP '2026-07-15 14:20:00'),
    (4, 4, 'Je viens de finir un super workshop sur les interfaces modernes. Tellement inspirante la direction "Modern & Épuré" qu''on a choisie ! ✨', NULL, TIMESTAMP '2026-07-15 16:45:00'),
    (5, 1, 'Le weekend approche ! Des idées de sorties à partager ? Je cherche une expo sympa dans le centre 🖼', NULL, TIMESTAMP '2026-07-15 18:00:00')
) AS v
WHERE NOT EXISTS (SELECT 1 FROM posts);

INSERT INTO comments (id, post_id, user_id, content, created_at)
SELECT * FROM (VALUES
    (1, 1, 2, 'Grave ! Ça change du vieux Bootstrap 😂 On revit.', TIMESTAMP '2026-07-15 08:45:00'),
    (2, 1, 3, 'Clair, j''adore les nouvelles couleurs, hyper clean.', TIMESTAMP '2026-07-15 09:10:00'),
    (3, 2, 1, 'Magnifique ! Tu étais vers où ? La lumière est folle.', TIMESTAMP '2026-07-15 10:30:00'),
    (4, 2, 4, 'La forêt est le meilleur antistress, bonne semaine !', TIMESTAMP '2026-07-15 11:00:00'),
    (5, 3, 2, 'Carrément, je suis dans le coin ! On se dit 16h ?', TIMESTAMP '2026-07-15 14:35:00'),
    (6, 4, 1, 'Trop bien, tu vas nous faire un retour ?', TIMESTAMP '2026-07-15 17:00:00'),
    (7, 5, 3, 'Il y a une expo photo au Centre Pompidou, top !', TIMESTAMP '2026-07-15 18:20:00'),
    (8, 5, 4, 'Oui ! Et c''est gratuite ce weekend apparemment.', TIMESTAMP '2026-07-15 18:45:00')
) AS v
WHERE NOT EXISTS (SELECT 1 FROM comments);

INSERT INTO likes (id, post_id, user_id, created_at)
SELECT * FROM (VALUES
    (1, 1, 2, TIMESTAMP '2026-07-15 08:50:00'),
    (2, 1, 3, TIMESTAMP '2026-07-15 09:05:00'),
    (3, 1, 4, TIMESTAMP '2026-07-15 09:30:00'),
    (4, 2, 1, TIMESTAMP '2026-07-15 10:25:00'),
    (5, 2, 3, TIMESTAMP '2026-07-15 11:15:00'),
    (6, 3, 2, TIMESTAMP '2026-07-15 14:40:00'),
    (7, 4, 1, TIMESTAMP '2026-07-15 17:05:00'),
    (8, 4, 2, TIMESTAMP '2026-07-15 17:30:00'),
    (9, 5, 3, TIMESTAMP '2026-07-15 18:10:00')
) AS v
WHERE NOT EXISTS (SELECT 1 FROM likes);

INSERT INTO job_offers (id, user_id, title, company, location, type, description, created_at)
SELECT * FROM (VALUES
    (1, 1, 'Développeur·se Full-Stack', 'Networkee', 'Paris', 'CDI', 'Rejoins notre équipe produit pour construire la prochaine génération du réseau. Stack PHP / JS moderne.', TIMESTAMP '2026-07-15 09:00:00'),
    (2, 4, 'UX/UI Designer', 'Studio Épuré', 'Lyon', 'Freelance', 'Mission de 3 mois pour refondre une app mobile. Portfolio orienté design system apprécié.', TIMESTAMP '2026-07-15 11:30:00'),
    (3, 2, 'Chef·fe de projet digital', 'GreenLeaf', 'Remote', 'CDD', 'Pilotage de projets web pour des clients engagés dans la transition écologique.', TIMESTAMP '2026-07-15 15:00:00')
) AS v
WHERE NOT EXISTS (SELECT 1 FROM job_offers);

-- ─── Réaligner les séquences après insertion d'IDs explicites ────────────────
SELECT setval(pg_get_serial_sequence('users', 'id'),      COALESCE((SELECT MAX(id) FROM users), 1),      true);
SELECT setval(pg_get_serial_sequence('posts', 'id'),      COALESCE((SELECT MAX(id) FROM posts), 1),      true);
SELECT setval(pg_get_serial_sequence('likes', 'id'),      COALESCE((SELECT MAX(id) FROM likes), 1),      true);
SELECT setval(pg_get_serial_sequence('comments', 'id'),   COALESCE((SELECT MAX(id) FROM comments), 1),   true);
SELECT setval(pg_get_serial_sequence('job_offers', 'id'), COALESCE((SELECT MAX(id) FROM job_offers), 1), true);
