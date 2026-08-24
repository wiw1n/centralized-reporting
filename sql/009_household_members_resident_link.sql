-- Links household_members back to the master residents registry so an
-- encoder can look up a person who is already profiled (as a resident, or
-- as a member of another household) instead of re-typing a duplicate entry.
-- Depends on household_members (004_households_and_members.sql) and
-- residents (005_residents.sql).

ALTER TABLE `household_members`
  ADD COLUMN `resident_id` INT DEFAULT NULL COMMENT 'Resident record this member was looked up/linked from, if any' AFTER `household_id`,
  ADD KEY `household_members_resident_id_foreign` (`resident_id`),
  ADD CONSTRAINT `household_members_resident_id_foreign` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE SET NULL;
