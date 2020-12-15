--
-- Update sql for MailWizz EMA from version 1.7.8 to 1.7.9
--

--
-- Alter table
--
ALTER TABLE `campaign_option` ADD `bounces_count` INT(11) NOT NULL DEFAULT '-1' AFTER `industry_processed_count`;
ALTER TABLE `campaign_option` ADD `hard_bounces_count` INT(11) NOT NULL DEFAULT '-1' AFTER `bounces_count`;
ALTER TABLE `campaign_option` ADD `soft_bounces_count` INT(11) NOT NULL DEFAULT '-1' AFTER `hard_bounces_count`;
ALTER TABLE `campaign_option` ADD `internal_bounces_count` INT(11) NOT NULL DEFAULT '-1' AFTER `soft_bounces_count`;

ALTER TABLE `campaign_option` ADD `opens_count` INT(11) NOT NULL DEFAULT '-1' AFTER `internal_bounces_count`;
ALTER TABLE `campaign_option` ADD `unique_opens_count` INT(11) NOT NULL DEFAULT '-1' AFTER `opens_count`;

ALTER TABLE `campaign_option` ADD `clicks_count` INT(11) NOT NULL DEFAULT '-1' AFTER `unique_opens_count`;
ALTER TABLE `campaign_option` ADD `unique_clicks_count` INT(11) NOT NULL DEFAULT '-1' AFTER `clicks_count`;