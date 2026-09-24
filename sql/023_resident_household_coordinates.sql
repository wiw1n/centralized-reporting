-- Map coordinates for a household's house. Stored per resident row (same
-- pattern as household_no itself), but the app keeps every resident sharing
-- a household_no + barangay in sync with the same coordinates -- see
-- Resident_household_model::save().
-- Depends on resident_household (010_resident_household.sql, 011_resident_household_no.sql).

ALTER TABLE `resident_household`
  ADD COLUMN `latitude` decimal(10,7) DEFAULT NULL AFTER `household_no`,
  ADD COLUMN `longitude` decimal(10,7) DEFAULT NULL AFTER `latitude`;
