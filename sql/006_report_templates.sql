-- Saved custom report templates: lets a user store a chosen set of resident
-- report fields + filters under a name and re-run it later instead of
-- rebuilding the report from scratch every time.
-- Depends on users (002_roles_users_assignments.sql).

CREATE TABLE IF NOT EXISTS `report_templates` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `name` varchar(150) NOT NULL,
  `fields` text NOT NULL,
  `filters` text NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `report_templates_user_id_foreign` (`user_id`),
  CONSTRAINT `report_templates_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
