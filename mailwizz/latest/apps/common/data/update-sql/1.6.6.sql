--
-- Update sql for MailWizz EMA from version 1.6.5 to 1.6.6
--

ALTER TABLE `customer` ADD `twofa_enabled` ENUM('no','yes') NOT NULL DEFAULT 'no' AFTER `phone`;
ALTER TABLE `customer` ADD `twofa_secret` VARCHAR(64) NOT NULL DEFAULT '' AFTER `twofa_enabled`;
ALTER TABLE `customer` ADD `twofa_timestamp` INT(11) NOT NULL DEFAULT '0' AFTER `twofa_secret`;

ALTER TABLE `user` ADD `twofa_enabled` ENUM('no','yes') NOT NULL DEFAULT 'no' AFTER `removable`;
ALTER TABLE `user` ADD `twofa_secret` VARCHAR(64) NOT NULL DEFAULT '' AFTER `twofa_enabled`;
ALTER TABLE `user` ADD `twofa_timestamp` INT(11) NOT NULL DEFAULT '0' AFTER `twofa_secret`;