-- Short prefixes used to build a resident's ID Number.
-- A resident's ID Number reads {municipality prefix}-{barangay prefix}-{sequence},
-- e.g. PAL-SJO-0001. Both prefixes are optional; when blank the generator falls
-- back to the existing `code`, then to a Bxx/Mxx placeholder.
-- Depends on the base address schema (address_municipality, address_barangay)
-- and residents (005_residents.sql).

ALTER TABLE `address_municipality`
  ADD COLUMN `prefix` varchar(10) DEFAULT NULL COMMENT 'Resident ID Number prefix' AFTER `code`;

ALTER TABLE `address_barangay`
  ADD COLUMN `prefix` varchar(10) DEFAULT NULL COMMENT 'Resident ID Number prefix' AFTER `code`;

-- Widen resident_no to fit the {muni}-{brgy}-{seq} identifier.
ALTER TABLE `residents`
  MODIFY `resident_no` varchar(40) NOT NULL;
