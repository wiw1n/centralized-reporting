-- Per-resident household/family-profiling extension. One row per resident
-- (resident_id is unique), holding the household-context fields that don't
-- already exist directly on `residents` (relationship/position within the
-- household, present illness, pregnancy, and OPT Plus/school nutrition
-- indicators -- the same set captured per-member on the separate
-- households/household_members feature). Kept as its own table, rather than
-- more columns on `residents`, so future additions don't require repeatedly
-- widening the core resident record.
-- Depends on residents (005_residents.sql).

CREATE TABLE IF NOT EXISTS `resident_household` (
  `id` int NOT NULL AUTO_INCREMENT,
  `resident_id` int NOT NULL,
  `relationship_to_head` varchar(50) DEFAULT NULL,
  `ordinal_position` tinyint unsigned DEFAULT NULL COMMENT 'Ord. Position on the BHW Family Profiling Form',
  `is_surveyed` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Covered by the Family Profile Survey (BNS Form 1C item 3)',
  `has_hypertension` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Present illness: Hypertension (HPN)',
  `has_diabetes` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Present illness: Diabetes Mellitus (DM)',
  `has_asthma` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Present illness: Asthma',
  `other_illness` varchar(150) DEFAULT NULL COMMENT 'Present illness: other (ETC.)',
  `is_pregnant` tinyint(1) NOT NULL DEFAULT '0',
  `is_lactating` tinyint(1) NOT NULL DEFAULT '0',
  `gravida` tinyint unsigned DEFAULT NULL COMMENT 'Pregnancy: Gravida (G)',
  `para` tinyint unsigned DEFAULT NULL COMMENT 'Pregnancy: Para (P)',
  `lmp_date` date DEFAULT NULL COMMENT 'Pregnancy: Last Menstrual Period',
  `edc_date` date DEFAULT NULL COMMENT 'Pregnancy: Expected Date of Confinement',
  `tt_status` enum('TT1','TT2','TT3','TT4','TT5','Fully Immunized') DEFAULT NULL COMMENT 'Tetanus Toxoid immunization status',
  `opt_plus_measured` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Measured during OPT Plus (preschool children 0-59 months old)',
  `nutritional_status_weight_age` enum('Severely Underweight','Underweight','Normal','Overweight') DEFAULT NULL COMMENT 'Weight-for-age classification, OPT Plus',
  `nutritional_status_height_age` enum('Severely Stunted','Stunted','Normal','Tall') DEFAULT NULL COMMENT 'Height-for-age classification, OPT Plus',
  `nutritional_status_weight_height` enum('Severely Wasted','Wasted','Normal','Overweight','Obese') DEFAULT NULL COMMENT 'Weight-for-height/length classification, OPT Plus',
  `school_level` enum('Day Care','Kindergarten','Elementary') DEFAULT NULL COMMENT 'Current enrollment level for the nutrition profile school indicators',
  `school_type` enum('Public','Private') DEFAULT NULL,
  `school_weighed` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Weighed at the start of the school year (Kindergarten-Grade 6)',
  `school_nutritional_status` enum('Severely Wasted','Wasted','Normal','Overweight','Obese') DEFAULT NULL COMMENT 'Nutritional status from the start-of-school-year weighing',
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `resident_household_resident_id_unique` (`resident_id`),
  CONSTRAINT `resident_household_resident_id_foreign` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
