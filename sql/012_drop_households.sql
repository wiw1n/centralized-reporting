-- Removes the standalone Households module (superseded by the per-resident
-- household/family-profiling extension on `residents` + `resident_household`,
-- see 010_resident_household.sql and 011_resident_household_no.sql).
-- Depends on households and household_members (004_households_and_members.sql,
-- 009_household_members_resident_link.sql).

DROP TABLE IF EXISTS `household_members`;
DROP TABLE IF EXISTS `households`;
