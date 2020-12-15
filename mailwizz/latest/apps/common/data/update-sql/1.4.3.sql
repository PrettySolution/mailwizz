--
-- Update sql for MailWizz EMA from version 1.4.2 to 1.4.3
--

--
-- Alter the campaign_option table
--
ALTER TABLE `campaign_option` ADD `autoresponder_time_min_hour` CHAR(2) NULL AFTER `autoresponder_include_current`;
ALTER TABLE `campaign_option` ADD `autoresponder_time_min_minute` CHAR(2) NULL AFTER `autoresponder_time_min_hour`;