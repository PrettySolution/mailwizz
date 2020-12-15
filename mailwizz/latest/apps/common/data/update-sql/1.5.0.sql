--
-- Update sql for MailWizz EMA from version 1.4.9 to 1.5.0
--

--
-- Alter table
--
ALTER TABLE `campaign` CHANGE `from_name` `from_name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
ALTER TABLE `list_default` CHANGE `from_name` `from_name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;

--
-- Alter campaign_abuse_report table
--
ALTER TABLE `campaign_abuse_report` ADD `ip_address` VARCHAR(15) NULL AFTER `log`, ADD `user_agent` VARCHAR(255) NULL AFTER `ip_address`;

--
-- Alter customer_suppression_list_email table
--
ALTER TABLE `customer_suppression_list_email` DROP `email_uid`, DROP `date_added`, DROP `last_updated`;
ALTER TABLE `customer_suppression_list_email` ADD `email_md5` CHAR(32) NULL DEFAULT NULL AFTER `email`;
ALTER TABLE `customer_suppression_list_email` CHANGE `email` `email` VARCHAR(150) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;

--
-- Alter customer_suppression_list_email drop and add index
--
ALTER TABLE `customer_suppression_list_email` 
  DROP INDEX `list_id_email`, 
  ADD INDEX `email` (`email`),
  ADD INDEX `email_md5` (`email_md5`),
  ADD INDEX `list_id_email_md5` (`list_id`, `email_md5`);
  
--
-- Update customer_suppression_list_email table
--
UPDATE `customer_suppression_list_email` SET `email_md5` = `email`;
UPDATE `customer_suppression_list_email` SET `email_md5` = MD5(`email_md5`) WHERE `email_md5` NOT REGEXP '^[a-f0-9]{32}$';
UPDATE `customer_suppression_list_email` SET `email` = NULL WHERE `email` REGEXP '^[a-f0-9]{32}$';