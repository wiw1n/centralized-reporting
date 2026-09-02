-- Adds "Type of Resident" (Resident / Non-Resident) to the per-resident
-- household profile, captured in the Household Information card of the
-- resident form.
-- Depends on resident_household (010_resident_household.sql).

ALTER TABLE `resident_household`
  ADD COLUMN `type_of_resident` enum('Resident','Non-Resident') DEFAULT NULL COMMENT 'Type of Resident (Household Information)' AFTER `resident_id`;
