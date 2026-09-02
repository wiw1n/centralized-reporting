-- Store the 4Ps ID Number instead of a plain yes/no flag on the resident record.
-- `is_4ps_beneficiary` becomes a nullable varchar: NULL = not a beneficiary,
-- any non-NULL value (including '') = beneficiary, with the string holding the
-- household's 4Ps ID Number when known.
-- Depends on residents (005_residents.sql).

ALTER TABLE `residents`
  MODIFY `is_4ps_beneficiary` varchar(50) DEFAULT NULL COMMENT '4Ps ID Number; NULL = not a beneficiary';

UPDATE `residents` SET `is_4ps_beneficiary` = NULL WHERE `is_4ps_beneficiary` = '0';
