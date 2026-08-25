-- Per-resident Data Survey Tool: immunization status, COVID-19 vaccine doses,
-- Schisto MDA status, usual daily food intake, exercise, and recreational
-- activity. One row per resident (resident_id is unique), kept as its own
-- table for the same reason as resident_household (010_resident_household.sql)
-- -- these fields don't belong on the core resident record.
-- Depends on residents (005_residents.sql).

CREATE TABLE IF NOT EXISTS `resident_data_survey` (
  `id` int NOT NULL AUTO_INCREMENT,
  `resident_id` int NOT NULL,
  `immunization_status` enum('FIC','INC','No Immunization') DEFAULT NULL COMMENT 'Immun. Status: FIC=Fully Immunized Child, INC=Incompletely Immunized',
  `covid_vaccine_status` enum('1st Dose','2nd Dose','Booster 1','Booster 2','Booster 3','None') DEFAULT NULL COMMENT 'COVID-19 Immun. (No. of Doses)',
  `schisto_mda_status` tinyint(1) DEFAULT NULL COMMENT 'Schisto MDA Status (Y/N)',
  `schisto_mda_date` date DEFAULT NULL COMMENT 'Schisto MDA: Date of Tx',
  `eats_breakfast` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Usual Daily Food Intake: Breakfast',
  `eats_lunch` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Usual Daily Food Intake: Lunch',
  `eats_snacks` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Usual Daily Food Intake: Snacks',
  `exercises` tinyint(1) DEFAULT NULL COMMENT 'Exercise (Y/N)',
  `exercise_frequency` varchar(50) DEFAULT NULL COMMENT 'Exercise: Frequency',
  `has_recreational_activity` tinyint(1) DEFAULT NULL COMMENT 'Recreational Activity (Y/N)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `resident_data_survey_resident_id_unique` (`resident_id`),
  CONSTRAINT `resident_data_survey_resident_id_foreign` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
