-- Adds Medical History (free text) and Nutritional Status (BMI class) to the
-- Adult (20-59), Adolescent, and Senior Citizen life-stage groups of the
-- Household Information card on the resident form.
-- Depends on resident_household (010_resident_household.sql).

ALTER TABLE `resident_household`
  ADD COLUMN `adult_medical_history` varchar(255) DEFAULT NULL COMMENT 'Medical history free text (Adult 20-59 years old)' AFTER `school_nutritional_status`,
  ADD COLUMN `adult_nutritional_status` enum('Underweight','Normal','Overweight','Obese Class I','Obese Class II') DEFAULT NULL COMMENT 'Nutritional status (Adult 20-59 years old)' AFTER `adult_medical_history`,
  ADD COLUMN `adolescent_medical_history` varchar(255) DEFAULT NULL COMMENT 'Medical history free text (Adolescent)' AFTER `adult_nutritional_status`,
  ADD COLUMN `adolescent_nutritional_status` enum('Underweight','Normal','Overweight','Obese Class I','Obese Class II') DEFAULT NULL COMMENT 'Nutritional status (Adolescent)' AFTER `adolescent_medical_history`,
  ADD COLUMN `senior_medical_history` varchar(255) DEFAULT NULL COMMENT 'Medical history free text (Senior Citizen)' AFTER `adolescent_nutritional_status`,
  ADD COLUMN `senior_nutritional_status` enum('Underweight','Normal','Overweight','Obese Class I','Obese Class II') DEFAULT NULL COMMENT 'Nutritional status (Senior Citizen)' AFTER `senior_medical_history`;
