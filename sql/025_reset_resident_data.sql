-- Wipes ALL resident data so the database starts fresh:
--   residents and every per-resident section table (personal, contact,
--   work/education, government IDs, program flags, remarks, household /
--   family profile incl. house coordinates and environment, data survey).
--
-- KEEPS: users, roles, user_area_assignments, address_* tables (incl. barangay
--        coordinates / puroks / school counts), report_templates and any
--        system config.
--
-- WARNING: irreversible. Take a backup first (mysqldump) if unsure.
-- TRUNCATE also resets AUTO_INCREMENT so the next resident gets id 1.

SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE `resident_personal`;
TRUNCATE TABLE `resident_contact`;
TRUNCATE TABLE `resident_work_education`;
TRUNCATE TABLE `resident_government_ids`;
TRUNCATE TABLE `resident_program_flags`;
TRUNCATE TABLE `resident_remarks`;
TRUNCATE TABLE `resident_household`;
TRUNCATE TABLE `resident_data_survey`;
TRUNCATE TABLE `residents`;

SET FOREIGN_KEY_CHECKS = 1;
