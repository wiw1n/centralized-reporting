-- Brings household_members in line with the printed BHW Family Profiling Form:
-- Religion, Ord. Position, Present Illness (HPN/DM/Asthma/Etc.), Pregnancy
-- details (Gravida/Para/LMP/EDC), and TT (Tetanus Toxoid) Status.
-- Depends on household_members (004_households_and_members.sql, 007_nutrition_profile.sql).

ALTER TABLE `household_members`
  ADD COLUMN `ordinal_position` TINYINT UNSIGNED DEFAULT NULL COMMENT 'Ord. Position on the BHW Family Profiling Form' AFTER `relationship_to_head`,
  ADD COLUMN `religion` VARCHAR(50) DEFAULT NULL AFTER `civil_status`,
  ADD COLUMN `has_hypertension` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Present illness: Hypertension (HPN)' AFTER `is_lactating`,
  ADD COLUMN `has_diabetes` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Present illness: Diabetes Mellitus (DM)' AFTER `has_hypertension`,
  ADD COLUMN `has_asthma` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Present illness: Asthma' AFTER `has_diabetes`,
  ADD COLUMN `other_illness` VARCHAR(150) DEFAULT NULL COMMENT 'Present illness: other (ETC.)' AFTER `has_asthma`,
  ADD COLUMN `gravida` TINYINT UNSIGNED DEFAULT NULL COMMENT 'Pregnancy: Gravida (G)' AFTER `other_illness`,
  ADD COLUMN `para` TINYINT UNSIGNED DEFAULT NULL COMMENT 'Pregnancy: Para (P)' AFTER `gravida`,
  ADD COLUMN `lmp_date` DATE DEFAULT NULL COMMENT 'Pregnancy: Last Menstrual Period' AFTER `para`,
  ADD COLUMN `edc_date` DATE DEFAULT NULL COMMENT 'Pregnancy: Expected Date of Confinement' AFTER `lmp_date`,
  ADD COLUMN `tt_status` ENUM('TT1','TT2','TT3','TT4','TT5','Fully Immunized') DEFAULT NULL COMMENT 'Tetanus Toxoid immunization status' AFTER `edc_date`;
