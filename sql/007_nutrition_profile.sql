-- Barangay Nutrition Profile (BNS Form No. 1C) support.
-- Adds the anthropometric/enrollment data needed to compute the form's
-- indicators from household_members, plus the handful of barangay-level
-- facts (puroks, day care/elementary school counts) that aren't derived
-- from any resident/household record.
-- Depends on households/household_members (004_households_and_members.sql)
-- and the pre-existing address_barangay table.

ALTER TABLE `household_members`
  ADD COLUMN `is_pregnant` TINYINT(1) NOT NULL DEFAULT '0' AFTER `is_4ps_beneficiary`,
  ADD COLUMN `is_lactating` TINYINT(1) NOT NULL DEFAULT '0' AFTER `is_pregnant`,
  ADD COLUMN `opt_plus_measured` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Measured during OPT Plus (preschool children 0-59 months old)' AFTER `is_lactating`,
  ADD COLUMN `nutritional_status_weight_age` ENUM('Severely Underweight','Underweight','Normal','Overweight') DEFAULT NULL COMMENT 'Weight-for-age classification, OPT Plus' AFTER `opt_plus_measured`,
  ADD COLUMN `nutritional_status_height_age` ENUM('Severely Stunted','Stunted','Normal','Tall') DEFAULT NULL COMMENT 'Height-for-age classification, OPT Plus' AFTER `nutritional_status_weight_age`,
  ADD COLUMN `nutritional_status_weight_height` ENUM('Severely Wasted','Wasted','Normal','Overweight','Obese') DEFAULT NULL COMMENT 'Weight-for-height/length classification, OPT Plus' AFTER `nutritional_status_height_age`,
  ADD COLUMN `school_level` ENUM('Day Care','Kindergarten','Elementary') DEFAULT NULL COMMENT 'Current enrollment level for the nutrition profile school indicators' AFTER `nutritional_status_weight_height`,
  ADD COLUMN `school_type` ENUM('Public','Private') DEFAULT NULL AFTER `school_level`,
  ADD COLUMN `school_weighed` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Weighed at the start of the school year (Kindergarten-Grade 6)' AFTER `school_type`,
  ADD COLUMN `school_nutritional_status` ENUM('Severely Wasted','Wasted','Normal','Overweight','Obese') DEFAULT NULL COMMENT 'Nutritional status from the start-of-school-year weighing' AFTER `school_weighed`;

ALTER TABLE `households`
  ADD COLUMN `is_surveyed` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Covered by the Family Profile Survey (BNS Form 1C item 3)' AFTER `remarks`;

ALTER TABLE `address_barangay`
  ADD COLUMN `total_puroks` INT NOT NULL DEFAULT '0',
  ADD COLUMN `day_care_centers_public` INT NOT NULL DEFAULT '0',
  ADD COLUMN `day_care_centers_private` INT NOT NULL DEFAULT '0',
  ADD COLUMN `elementary_schools_public` INT NOT NULL DEFAULT '0',
  ADD COLUMN `elementary_schools_private` INT NOT NULL DEFAULT '0';
