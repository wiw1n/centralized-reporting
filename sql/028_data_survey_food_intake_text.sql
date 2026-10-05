-- Adds a free-text "what food" field to each Usual Daily Food Intake meal.
-- Depends on 013_resident_data_survey.sql and 027_data_survey_dinner.sql.

ALTER TABLE `resident_data_survey`
  ADD COLUMN `eats_breakfast_food` varchar(255) DEFAULT NULL COMMENT 'Breakfast: foods eaten' AFTER `eats_breakfast`,
  ADD COLUMN `eats_lunch_food` varchar(255) DEFAULT NULL COMMENT 'Lunch: foods eaten' AFTER `eats_lunch`,
  ADD COLUMN `eats_dinner_food` varchar(255) DEFAULT NULL COMMENT 'Dinner: foods eaten' AFTER `eats_dinner`,
  ADD COLUMN `eats_snacks_food` varchar(255) DEFAULT NULL COMMENT 'Snacks: foods eaten' AFTER `eats_snacks`;
