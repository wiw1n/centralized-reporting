-- Adds the "Household Environment" group to the Household Information card
-- (below Senior Citizen): toilet type, water source, electricity, vehicle,
-- house ownership/type, lot occupancy, food production activity, pets,
-- iodized salt / fortified-food usage, date accomplished and remarks.
-- Depends on resident_household (010_resident_household.sql).

ALTER TABLE `resident_household`
  ADD COLUMN `env_toilet_type` enum('Water Sealed','Open Pit','Others','None') DEFAULT NULL COMMENT 'Type of toilet facility (WS, OP, O, N)',
  ADD COLUMN `env_toilet_other` varchar(100) DEFAULT NULL COMMENT 'Toilet facility, if Others',
  ADD COLUMN `env_water_source` enum('Pipe','Well','Spring') DEFAULT NULL COMMENT 'Type of water source (P, W, S)',
  ADD COLUMN `env_electricity` enum('Owner','Sharer') DEFAULT NULL COMMENT 'Electricity',
  ADD COLUMN `env_vehicle_tricycle` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Vehicle: Tricycle',
  ADD COLUMN `env_vehicle_four_wheels` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Vehicle: Four wheels',
  ADD COLUMN `env_vehicle_motorcycle` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Vehicle: Motorcycle',
  ADD COLUMN `env_vehicle_other` varchar(100) DEFAULT NULL COMMENT 'Vehicle: others, please specify',
  ADD COLUMN `env_house_ownership` enum('Owner','Sharer') DEFAULT NULL COMMENT 'House',
  ADD COLUMN `env_house_type` enum('Light Materials','Concrete','Semi Concrete') DEFAULT NULL COMMENT 'Type of house',
  ADD COLUMN `env_lot_occupancy` enum('Owner','Rented','Sharer','Illegal Settler') DEFAULT NULL COMMENT 'Lot occupancy',
  ADD COLUMN `env_food_vegetable_garden` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Food production: Vegetable garden (VG)',
  ADD COLUMN `env_food_poultry_livestock` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Food production: Poultry/Livestock (P/L)',
  ADD COLUMN `env_food_fishpond` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Food production: Fishpond (FP)',
  ADD COLUMN `env_pets_dogs` smallint unsigned DEFAULT NULL COMMENT 'Number of pet dogs',
  ADD COLUMN `env_pets_cats` smallint unsigned DEFAULT NULL COMMENT 'Number of pet cats',
  ADD COLUMN `env_uses_iodized_salt` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'HH using iodized salt',
  ADD COLUMN `env_sells_iodized_salt` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'HH selling iodized salt',
  ADD COLUMN `env_uses_ifr` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'HH using IFR (iron-fortified rice)',
  ADD COLUMN `env_sffp` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'SFFP - Selling fortified food/products',
  ADD COLUMN `env_uvf` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'UVF - Using Vitamin A fortified flour',
  ADD COLUMN `env_uvo` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'UVO - Using Vitamin A fortified oil',
  ADD COLUMN `env_date_accomplished` date DEFAULT NULL COMMENT 'Date accomplished',
  ADD COLUMN `env_remarks` varchar(255) DEFAULT NULL COMMENT 'Household environment remarks';
