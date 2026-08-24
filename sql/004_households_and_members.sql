-- Households and household members (base profiling unit, one level below barangay).
-- Depends on address_barangay (base address schema) and users (002_roles_users_assignments.sql).

CREATE TABLE IF NOT EXISTS `households` (
  `id` int NOT NULL AUTO_INCREMENT,
  `barangay_id` int NOT NULL,
  `household_no` varchar(30) NOT NULL,
  `purok_sitio` varchar(100) DEFAULT NULL,
  `address_line` varchar(150) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `remarks` tinytext,
  `archive` tinyint(1) NOT NULL DEFAULT '0',
  `created_by` int DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `households_barangay_household_no_unique` (`barangay_id`, `household_no`),
  KEY `households_barangay_id_foreign` (`barangay_id`),
  KEY `households_created_by_foreign` (`created_by`),
  CONSTRAINT `households_barangay_id_foreign` FOREIGN KEY (`barangay_id`) REFERENCES `address_barangay` (`id`),
  CONSTRAINT `households_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `household_members` (
  `id` int NOT NULL AUTO_INCREMENT,
  `household_id` int NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `suffix` varchar(20) DEFAULT NULL,
  `relationship_to_head` varchar(50) NOT NULL,
  `sex` enum('Male','Female') NOT NULL,
  `birthdate` date NOT NULL,
  `civil_status` varchar(30) NOT NULL,
  `occupation` varchar(150) DEFAULT NULL,
  `educational_attainment` varchar(50) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `is_pwd` tinyint(1) NOT NULL DEFAULT '0',
  `is_senior_citizen` tinyint(1) NOT NULL DEFAULT '0',
  `is_solo_parent` tinyint(1) NOT NULL DEFAULT '0',
  `is_4ps_beneficiary` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `household_members_household_id_foreign` (`household_id`),
  CONSTRAINT `household_members_household_id_foreign` FOREIGN KEY (`household_id`) REFERENCES `households` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
