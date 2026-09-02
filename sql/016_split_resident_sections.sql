-- Splits the wide `residents` table into one 1:1 child table per resident-form
-- section, finishing the pattern started by resident_household (010) and
-- resident_data_survey (013). After this migration `residents` keeps only
-- identity (name/sex/birthdate), the barangay anchor, and audit columns; every
-- other section of the resident form lives in its own table keyed by
-- resident_id, so future additions never widen the core resident record.
--
-- Existing data is copied into the new tables before the columns are dropped
-- from `residents`.
--
-- Depends on residents (005_residents.sql) and 4Ps ID number (014_4ps_id_number.sql).

-- --------------------------------------------------------------------------
-- 1. Personal Information
-- --------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `resident_personal` (
  `id` int NOT NULL AUTO_INCREMENT,
  `resident_id` int NOT NULL,
  `birthplace` varchar(150) DEFAULT NULL,
  `civil_status` varchar(30) NOT NULL,
  `religion` varchar(50) DEFAULT NULL,
  `citizenship` varchar(50) NOT NULL DEFAULT 'Filipino',
  `blood_type` varchar(5) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `resident_personal_resident_id_unique` (`resident_id`),
  CONSTRAINT `resident_personal_resident_id_foreign` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `resident_personal` (`resident_id`, `birthplace`, `civil_status`, `religion`, `citizenship`, `blood_type`, `created_at`, `updated_at`)
SELECT `id`, `birthplace`, `civil_status`, `religion`, `citizenship`, `blood_type`, COALESCE(`created_at`, NOW()), `updated_at`
FROM `residents`;

-- --------------------------------------------------------------------------
-- 2. Contact & Address
-- --------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `resident_contact` (
  `id` int NOT NULL AUTO_INCREMENT,
  `resident_id` int NOT NULL,
  `purok_sitio` varchar(100) DEFAULT NULL,
  `address_line` varchar(150) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `resident_contact_resident_id_unique` (`resident_id`),
  CONSTRAINT `resident_contact_resident_id_foreign` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `resident_contact` (`resident_id`, `purok_sitio`, `address_line`, `contact_number`, `email`, `created_at`, `updated_at`)
SELECT `id`, `purok_sitio`, `address_line`, `contact_number`, `email`, COALESCE(`created_at`, NOW()), `updated_at`
FROM `residents`;

-- --------------------------------------------------------------------------
-- 3. Occupation & Education
-- --------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `resident_work_education` (
  `id` int NOT NULL AUTO_INCREMENT,
  `resident_id` int NOT NULL,
  `occupation` varchar(150) DEFAULT NULL,
  `employer` varchar(150) DEFAULT NULL,
  `monthly_income` decimal(10,2) DEFAULT NULL,
  `educational_attainment` varchar(50) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `resident_work_education_resident_id_unique` (`resident_id`),
  CONSTRAINT `resident_work_education_resident_id_foreign` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `resident_work_education` (`resident_id`, `occupation`, `employer`, `monthly_income`, `educational_attainment`, `created_at`, `updated_at`)
SELECT `id`, `occupation`, `employer`, `monthly_income`, `educational_attainment`, COALESCE(`created_at`, NOW()), `updated_at`
FROM `residents`;

-- --------------------------------------------------------------------------
-- 4. Government IDs
-- --------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `resident_government_ids` (
  `id` int NOT NULL AUTO_INCREMENT,
  `resident_id` int NOT NULL,
  `national_id_no` varchar(50) DEFAULT NULL,
  `voters_id_no` varchar(50) DEFAULT NULL,
  `sss_no` varchar(50) DEFAULT NULL,
  `gsis_no` varchar(50) DEFAULT NULL,
  `pagibig_no` varchar(50) DEFAULT NULL,
  `philhealth_no` varchar(50) DEFAULT NULL,
  `tin_no` varchar(50) DEFAULT NULL,
  `yakap_no` varchar(50) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `resident_government_ids_resident_id_unique` (`resident_id`),
  CONSTRAINT `resident_government_ids_resident_id_foreign` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `resident_government_ids` (`resident_id`, `national_id_no`, `voters_id_no`, `sss_no`, `gsis_no`, `pagibig_no`, `philhealth_no`, `tin_no`, `created_at`, `updated_at`)
SELECT `id`, `national_id_no`, `voters_id_no`, `sss_no`, `gsis_no`, `pagibig_no`, `philhealth_no`, `tin_no`, COALESCE(`created_at`, NOW()), `updated_at`
FROM `residents`;

-- --------------------------------------------------------------------------
-- 5. Program Flags
-- --------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `resident_program_flags` (
  `id` int NOT NULL AUTO_INCREMENT,
  `resident_id` int NOT NULL,
  `is_pwd` tinyint(1) NOT NULL DEFAULT '0',
  `is_senior_citizen` tinyint(1) NOT NULL DEFAULT '0',
  `is_solo_parent` tinyint(1) NOT NULL DEFAULT '0',
  `is_4ps_beneficiary` varchar(50) DEFAULT NULL COMMENT '4Ps ID Number; NULL = not a beneficiary',
  `is_ofw` tinyint(1) NOT NULL DEFAULT '0',
  `is_indigenous` tinyint(1) NOT NULL DEFAULT '0',
  `indigenous_group` varchar(100) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `resident_program_flags_resident_id_unique` (`resident_id`),
  CONSTRAINT `resident_program_flags_resident_id_foreign` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `resident_program_flags` (`resident_id`, `is_pwd`, `is_senior_citizen`, `is_solo_parent`, `is_4ps_beneficiary`, `is_ofw`, `is_indigenous`, `indigenous_group`, `created_at`, `updated_at`)
SELECT `id`, `is_pwd`, `is_senior_citizen`, `is_solo_parent`, `is_4ps_beneficiary`, `is_ofw`, `is_indigenous`, `indigenous_group`, COALESCE(`created_at`, NOW()), `updated_at`
FROM `residents`;

-- --------------------------------------------------------------------------
-- 6. Remarks
-- --------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `resident_remarks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `resident_id` int NOT NULL,
  `remarks` tinytext,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `resident_remarks_resident_id_unique` (`resident_id`),
  CONSTRAINT `resident_remarks_resident_id_foreign` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `resident_remarks` (`resident_id`, `remarks`, `created_at`, `updated_at`)
SELECT `id`, `remarks`, COALESCE(`created_at`, NOW()), `updated_at`
FROM `residents`;

-- --------------------------------------------------------------------------
-- 7. Drop the moved columns from `residents`
-- --------------------------------------------------------------------------
ALTER TABLE `residents`
  DROP COLUMN `birthplace`,
  DROP COLUMN `civil_status`,
  DROP COLUMN `religion`,
  DROP COLUMN `citizenship`,
  DROP COLUMN `blood_type`,
  DROP COLUMN `purok_sitio`,
  DROP COLUMN `address_line`,
  DROP COLUMN `contact_number`,
  DROP COLUMN `email`,
  DROP COLUMN `occupation`,
  DROP COLUMN `employer`,
  DROP COLUMN `monthly_income`,
  DROP COLUMN `educational_attainment`,
  DROP COLUMN `national_id_no`,
  DROP COLUMN `voters_id_no`,
  DROP COLUMN `sss_no`,
  DROP COLUMN `gsis_no`,
  DROP COLUMN `pagibig_no`,
  DROP COLUMN `philhealth_no`,
  DROP COLUMN `tin_no`,
  DROP COLUMN `is_pwd`,
  DROP COLUMN `is_senior_citizen`,
  DROP COLUMN `is_solo_parent`,
  DROP COLUMN `is_4ps_beneficiary`,
  DROP COLUMN `is_ofw`,
  DROP COLUMN `is_indigenous`,
  DROP COLUMN `indigenous_group`,
  DROP COLUMN `remarks`;
