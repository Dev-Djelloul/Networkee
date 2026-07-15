-- Données de démonstration pour Networkee (MySQL)
-- À importer dans phpMyAdmin après avoir créé la base `networkee`.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Use DELETE instead of TRUNCATE to avoid FK constraint issues in phpMyAdmin
DELETE FROM `comments`;
DELETE FROM `likes`;
DELETE FROM `posts`;
DELETE FROM `users`;

SET FOREIGN_KEY_CHECKS = 1;

-- Reset AUTO_INCREMENT counters
ALTER TABLE `comments` AUTO_INCREMENT = 1;
ALTER TABLE `likes` AUTO_INCREMENT = 1;
ALTER TABLE `posts` AUTO_INCREMENT = 1;
ALTER TABLE `users` AUTO_INCREMENT = 1;

-- Utilisateurs (mots de passe hashés avec bcrypt)
-- alexandre@networkee.test  -> password : password
-- sophie@networkee.test     -> password : 123456
-- hugo@networkee.test       -> password : azerty
-- camille@networkee.test    -> password : networkee

INSERT INTO `users` (`id`, `username`, `email`, `password`, `profile_image`, `bio`, `created_at`) VALUES
(1, 'Alexandre', 'alexandre@networkee.test', '$2y$10$CqTfGjBD6V3bjUS9AwIH8uBJkfXKi/vgqsVPqni6yUV7ZpWDt9s7C', NULL, 'Passionné par les nouvelles tech et les voyages 🚀', '2026-07-10 18:00:00'),
(2, 'Sophie', 'sophie@networkee.test', '$2y$10$85AkzqXHPj0tsqzC8.M7/eVurrw/lEPh3O39YWfwsopP0tMtrfZ42', NULL, 'Amoureuse de la nature et de la photographie 📸🌲', '2026-07-11 12:30:00'),
(3, 'Hugo', 'hugo@networkee.test', '$2y$10$LTx4YNKbNinUIMEskty.hOglU5F4iHPRA91/TV.aaIC5dIAeBDzwO', NULL, 'Toujours partant pour un café ou une expédition urbaine ☕️', '2026-07-12 09:15:00'),
(4, 'Camille', 'camille@networkee.test', '$2y$10$Bsp/NCuMhD/BRp8bWIETDu.em6d7sXvZ5tdPQTxW/g1hZ2ba6S.sK', NULL, 'Design, code et conversations autour d’un thé 🍵', '2026-07-13 14:45:00');

INSERT INTO `posts` (`id`, `user_id`, `content`, `image`, `created_at`) VALUES
(1, 1, 'Salut tout le monde ! Trop content de vous retrouver sur Networkee 2.0 🚀 La nouvelle interface est vraiment dingue, non ? Plus claire, plus rapide... on adore !', NULL, '2026-07-15 08:30:00'),
(2, 2, 'Petite balade en forêt ce matin pour se ressourcer. Rien de tel pour commencer la semaine ! 🌲🍃', NULL, '2026-07-15 10:15:00'),
(3, 3, 'Qui est chaud pour un café cet aprem ? Je suis dans le 11ème, on se capte ? ☕️☀️', NULL, '2026-07-15 14:20:00'),
(4, 4, 'Je viens de finir un super workshop sur les interfaces modernes. Tellement inspirante la direction "Modern & Épuré" qu\'on a choisie ! ✨', NULL, '2026-07-15 16:45:00'),
(5, 1, 'Le weekend approche ! Des idées de sorties à partager ? Je cherche une expo sympa dans le centre 🖼', NULL, '2026-07-15 18:00:00');

INSERT INTO `comments` (`id`, `post_id`, `user_id`, `content`, `created_at`) VALUES
(1, 1, 2, 'Grave ! Ça change du vieux Bootstrap 😂 On revit.', '2026-07-15 08:45:00'),
(2, 1, 3, 'Clair, j\'adore les nouvelles couleurs, hyper clean.', '2026-07-15 09:10:00'),
(3, 2, 1, 'Magnifique ! Tu étais vers où ? La lumière est folle.', '2026-07-15 10:30:00'),
(4, 2, 4, 'La forêt est le meilleur antistress, bonne semaine !', '2026-07-15 11:00:00'),
(5, 3, 2, 'Carrément, je suis dans le coin ! On se dit 16h ?', '2026-07-15 14:35:00'),
(6, 4, 1, 'Trop bien, tu vas nous faire un retour ?', '2026-07-15 17:00:00'),
(7, 5, 3, 'Il y a une expo photo au Centre Pompidou, top !', '2026-07-15 18:20:00'),
(8, 5, 4, 'Oui ! Et c\'est gratuite ce weekend apparemment.', '2026-07-15 18:45:00');

INSERT INTO `likes` (`id`, `post_id`, `user_id`, `created_at`) VALUES
(1, 1, 2, '2026-07-15 08:50:00'),
(2, 1, 3, '2026-07-15 09:05:00'),
(3, 1, 4, '2026-07-15 09:30:00'),
(4, 2, 1, '2026-07-15 10:25:00'),
(5, 2, 3, '2026-07-15 11:15:00'),
(6, 3, 2, '2026-07-15 14:40:00'),
(7, 4, 1, '2026-07-15 17:05:00'),
(8, 4, 2, '2026-07-15 17:30:00'),
(9, 5, 3, '2026-07-15 18:10:00'),
(10, 5, 4, '2026-07-15 18:50:00');
