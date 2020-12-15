--
-- Update sql for MailWizz EMA from version 1.8.9 to 1.9.0
--

--
-- Table `customer`
--
ALTER TABLE `customer` ADD `inactive_at` DATETIME NULL DEFAULT NULL AFTER `last_login`;
