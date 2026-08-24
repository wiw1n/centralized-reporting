-- Roles, Users, and Area Assignments
-- Depends on existing address_region / address_province / address_municipality / address_barangay tables.

CREATE TABLE IF NOT EXISTS `roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `label` varchar(100) NOT NULL,
  `description` tinytext,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `roles` (`name`, `label`, `description`) VALUES
('super_admin', 'Super Admin', 'Full system access including user management, addresses, and all data.'),
('admin', 'Admin', 'Manages addresses and profiling data. No user management access.'),
('encoder', 'Encoder', 'Data entry restricted to their assigned region/province/municipality/barangay.');

CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `role_id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `last_login` datetime DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `archive` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_username_unique` (`username`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_role_id_foreign` (`role_id`),
  CONSTRAINT `users_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `user_area_assignments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `scope_type` enum('region','province','municipality','barangay') NOT NULL,
  `region_id` int DEFAULT NULL,
  `province_id` int DEFAULT NULL,
  `municipality_id` int DEFAULT NULL,
  `barangay_id` int DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uaa_user_id_foreign` (`user_id`),
  KEY `uaa_region_id_foreign` (`region_id`),
  KEY `uaa_province_id_foreign` (`province_id`),
  KEY `uaa_municipality_id_foreign` (`municipality_id`),
  KEY `uaa_barangay_id_foreign` (`barangay_id`),
  CONSTRAINT `uaa_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `uaa_region_id_foreign` FOREIGN KEY (`region_id`) REFERENCES `address_region` (`id`),
  CONSTRAINT `uaa_province_id_foreign` FOREIGN KEY (`province_id`) REFERENCES `address_province` (`id`),
  CONSTRAINT `uaa_municipality_id_foreign` FOREIGN KEY (`municipality_id`) REFERENCES `address_municipality` (`id`),
  CONSTRAINT `uaa_barangay_id_foreign` FOREIGN KEY (`barangay_id`) REFERENCES `address_barangay` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
