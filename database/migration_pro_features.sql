-- Migration : fonctionnalités professionnelles Networkee
-- À exécuter dans phpMyAdmin sur la base `networkee`

-- 1. Colonnes pro sur les utilisateurs
ALTER TABLE `users`
  ADD COLUMN `job_title`    VARCHAR(120)  DEFAULT NULL AFTER `bio`,
  ADD COLUMN `location`     VARCHAR(100)  DEFAULT NULL AFTER `job_title`,
  ADD COLUMN `skills`       VARCHAR(500)  DEFAULT NULL AFTER `location`,
  ADD COLUMN `open_to_work` TINYINT(1)   NOT NULL DEFAULT 0 AFTER `skills`;

-- 2. Table des offres d'emploi
CREATE TABLE `job_offers` (
  `id`          INT(11)      NOT NULL AUTO_INCREMENT,
  `user_id`     INT(11)      NOT NULL,
  `title`       VARCHAR(150) NOT NULL,
  `company`     VARCHAR(120) NOT NULL,
  `location`    VARCHAR(100) DEFAULT NULL,
  `type`        ENUM('CDI','CDD','Freelance','Alternance','Stage') NOT NULL DEFAULT 'CDI',
  `description` TEXT         NOT NULL,
  `created_at`  TIMESTAMP    NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `job_offers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
