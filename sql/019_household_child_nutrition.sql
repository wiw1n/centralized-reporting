-- Adds the "Child (0 - 2 years old)" life-stage group to the Household
-- Information card on the resident form: immunization status, newborn
-- screening result, infant feeding (< 6 months), and complementary feeding
-- (6 months - 2 years old).
-- Depends on resident_household (010_resident_household.sql) and the WRA
-- columns (018_household_wra.sql).

ALTER TABLE `resident_household`
  ADD COLUMN `child_immunization_status` enum('Fully Immunized (FIC)','Completely Immunized (CIC)','Partially Immunized','Unimmunized') DEFAULT NULL COMMENT 'Immunization status (Child 0-2 years old)' AFTER `wra_nutritional_status`,
  ADD COLUMN `child_newborn_screening` enum('Yes','No') DEFAULT NULL COMMENT 'Newborn screening done (Child 0-2 years old)' AFTER `child_immunization_status`,
  ADD COLUMN `child_newborn_screening_result` varchar(255) DEFAULT NULL COMMENT 'Newborn screening result, if screened (Child 0-2 years old)' AFTER `child_newborn_screening`,
  ADD COLUMN `child_infant_feeding` enum('Exclusive Breastfeeding','Formula Feeding','Mixed Feeding','Food') DEFAULT NULL COMMENT 'Infant feeding practice, < 6 months (Child 0-2 years old)' AFTER `child_newborn_screening_result`,
  ADD COLUMN `child_complementary_feeding` enum('Breastfeeding + Food','Formula Feeding + Food','Mixed Feeding + Food','Food') DEFAULT NULL COMMENT 'Complementary feeding practice, 6 months - 2 years old (Child 0-2 years old)' AFTER `child_infant_feeding`;
