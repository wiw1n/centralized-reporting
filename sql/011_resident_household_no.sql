-- Household number for the per-resident household/family-profiling extension.
-- Residents sharing the same household_no belong to the same household;
-- relationship_to_head (already on this table) distinguishes the Head from
-- the rest of the household's members.
-- Depends on resident_household (010_resident_household.sql).

ALTER TABLE `resident_household`
  ADD COLUMN `household_no` varchar(30) DEFAULT NULL AFTER `resident_id`,
  ADD KEY `resident_household_household_no_index` (`household_no`);
