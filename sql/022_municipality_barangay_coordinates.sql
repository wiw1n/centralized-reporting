-- Map coordinates for municipalities and barangays.
-- A municipality's coordinates center the map when picking a barangay's
-- coordinates (so the picker opens already zoomed into the right area).
-- Both are optional; set via the map picker on the create/edit forms.

ALTER TABLE `address_municipality`
  ADD COLUMN `latitude` decimal(10,7) DEFAULT NULL AFTER `description`,
  ADD COLUMN `longitude` decimal(10,7) DEFAULT NULL AFTER `latitude`;

ALTER TABLE `address_barangay`
  ADD COLUMN `latitude` decimal(10,7) DEFAULT NULL AFTER `description`,
  ADD COLUMN `longitude` decimal(10,7) DEFAULT NULL AFTER `latitude`;
