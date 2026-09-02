-- Adds the "Women of Reproductive Age (15-49 years old)" life-stage group to the
-- Household Information card on the resident form: micronutrient supplementation,
-- family planning method / facility / application status, Pap smear, and
-- nutritional status.
-- Depends on resident_household (010_resident_household.sql) and the life-stage
-- nutrition columns (017_household_lifestage_nutrition.sql).

ALTER TABLE `resident_household`
  ADD COLUMN `wra_mns_iron_folic` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Micronutrient Supplementation: Iron + Folic Acid (WRA 15-49)' AFTER `senior_nutritional_status`,
  ADD COLUMN `wra_mns_calcium_carbonate` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Micronutrient Supplementation: Calcium Carbonate (WRA 15-49)' AFTER `wra_mns_iron_folic`,
  ADD COLUMN `wra_mns_mms` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Micronutrient Supplementation: Multiple Micro-nutrient Supplement (WRA 15-49)' AFTER `wra_mns_calcium_carbonate`,
  ADD COLUMN `wra_fp_method` varchar(100) DEFAULT NULL COMMENT 'Family Planning method used (WRA 15-49)' AFTER `wra_mns_mms`,
  ADD COLUMN `wra_fp_facility_of_buying` varchar(150) DEFAULT NULL COMMENT 'Facility where the FP commodity is obtained (WRA 15-49)' AFTER `wra_fp_method`,
  ADD COLUMN `wra_fp_status_of_application` enum('New Acceptor','Current User','Changing Method','Changing Clinic','Dropout','Restart') DEFAULT NULL COMMENT 'FP status of application (WRA 15-49)' AFTER `wra_fp_facility_of_buying`,
  ADD COLUMN `wra_papsmear_done` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Pap smear done (WRA 15-49)' AFTER `wra_fp_status_of_application`,
  ADD COLUMN `wra_papsmear_result` varchar(255) DEFAULT NULL COMMENT 'Pap smear result free text (WRA 15-49)' AFTER `wra_papsmear_done`,
  ADD COLUMN `wra_nutritional_status` enum('Underweight','Normal','Overweight','Obese Class I','Obese Class II') DEFAULT NULL COMMENT 'Nutritional status (WRA 15-49)' AFTER `wra_papsmear_result`;
