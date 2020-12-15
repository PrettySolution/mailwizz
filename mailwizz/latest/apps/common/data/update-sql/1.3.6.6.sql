--
-- Update sql for MailWizz EMA from version 1.3.6.5 to 1.3.6.6
--

--
-- Alter the campaign_option table
--
ALTER TABLE `campaign_option` ADD `tracking_domain_id` INT(11) NULL;
ALTER TABLE `campaign_option` ADD KEY `fk_campaign_option_campaign4_idx` (`tracking_domain_id`);
ALTER TABLE `campaign_option` ADD CONSTRAINT `fk_campaign_option_campaign4` FOREIGN KEY (`tracking_domain_id`) REFERENCES `tracking_domain` (`domain_id`) ON DELETE SET NULL ON UPDATE NO ACTION;
