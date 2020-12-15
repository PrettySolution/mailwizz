--
-- Update sql for MailWizz EMA from version 1.3.5.5 to 1.3.5.6
--

ALTER TABLE `list` ADD `subscriber_404_redirect` VARCHAR(255) NULL AFTER `welcome_email`;
ALTER TABLE `list` ADD `meta_data` BLOB NULL DEFAULT NULL AFTER `subscriber_404_redirect`;