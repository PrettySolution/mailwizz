--
-- Update sql for MailWizz EMA from version 1.6.3 to 1.6.4
--

ALTER TABLE `customer` ADD `phone` VARCHAR(32) NULL AFTER `birth_date`;

ALTER TABLE `campaign_option`
    ADD `autoresponder_sent_campaign_id` INT( 11 ) NULL AFTER `autoresponder_open_campaign_id`,
    ADD KEY `fk_campaign_option_campaign5_idx` (`autoresponder_sent_campaign_id`),
    ADD CONSTRAINT `fk_campaign_option_campaign5` FOREIGN KEY (`autoresponder_sent_campaign_id`) REFERENCES `campaign` (`campaign_id`) ON DELETE SET NULL ON UPDATE NO ACTION;