-- Adds "Micronutrient Supplementation / Deworming" to the Child life-stage
-- group of the Household Information card: deworming, Vitamin A, micronutrient
-- powder, ferrous sulfate, and multiple vitamins.
-- Depends on the child nutrition columns (019_household_child_nutrition.sql).

ALTER TABLE `resident_household`
  ADD COLUMN `child_mns_deworming` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Micronutrient Supplementation/Deworming: Deworming (Child)' AFTER `child_complementary_feeding`,
  ADD COLUMN `child_mns_vit_a` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Micronutrient Supplementation/Deworming: Vitamin A supplied (Child)' AFTER `child_mns_deworming`,
  ADD COLUMN `child_mns_micronutrient_powder` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Micronutrient Supplementation/Deworming: Micronutrient powder (Child)' AFTER `child_mns_vit_a`,
  ADD COLUMN `child_mns_ferrous_sulfate` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Micronutrient Supplementation/Deworming: Ferrous sulfate (Child)' AFTER `child_mns_micronutrient_powder`,
  ADD COLUMN `child_mns_multivitamins` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Micronutrient Supplementation/Deworming: Multiple vitamins (Child)' AFTER `child_mns_ferrous_sulfate`;
