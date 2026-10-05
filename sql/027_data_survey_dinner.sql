-- Adds "Dinner" to Usual Daily Food Intake. Depends on 013_resident_data_survey.sql.

ALTER TABLE `resident_data_survey`
  ADD COLUMN `eats_dinner` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Usual Daily Food Intake: Dinner' AFTER `eats_lunch`;
