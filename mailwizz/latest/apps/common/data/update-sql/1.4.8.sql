--
-- Update sql for MailWizz EMA from version 1.4.7 to 1.4.8
--

--
-- Alter table
--
ALTER TABLE `campaign_option` ADD `delivery_success_count` INT(11) NOT NULL DEFAULT '-1' AFTER `processed_count`;
ALTER TABLE `campaign_option` ADD `delivery_error_count` INT(11) NOT NULL DEFAULT '-1' AFTER `delivery_success_count`;
ALTER TABLE `campaign_option` ADD `industry_processed_count` INT(11) NOT NULL DEFAULT '-1' AFTER `delivery_error_count`;

