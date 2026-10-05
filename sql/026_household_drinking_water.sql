-- Adds "Drinking Water Source" (multiple choice) to the Household Environment
-- group. Depends on 024_household_environment.sql.

ALTER TABLE `resident_household`
  ADD COLUMN `env_drink_rainwater` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Drinking water source: Rainwater',
  ADD COLUMN `env_drink_well` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Drinking water source: Well',
  ADD COLUMN `env_drink_water_truck` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Drinking water source: Private water trucks',
  ADD COLUMN `env_drink_faucet` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Drinking water source: Faucet (NAWASA)',
  ADD COLUMN `env_drink_refilling_station` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Drinking water source: Water refilling station',
  ADD COLUMN `env_drink_bottled` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Drinking water source: Bottled water';
