--
-- Update sql for MailWizz EMA from version 1.5.7 to 1.5.8
--

--
-- Alter tracking domain table
--
ALTER TABLE `tracking_domain` ADD `scheme` VARCHAR(50) NOT NULL DEFAULT 'http' AFTER `name`;