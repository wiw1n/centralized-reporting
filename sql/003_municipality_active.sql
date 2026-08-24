-- Adds the "active municipality" switch used by System Settings.
-- Exactly one municipality is expected to be active at a time; this is
-- enforced in application code (Municipality_model::set_active_exclusive),
-- not by a DB constraint.
ALTER TABLE `address_municipality`
    ADD COLUMN IF NOT EXISTS `active` TINYINT(1) NOT NULL DEFAULT 0;
