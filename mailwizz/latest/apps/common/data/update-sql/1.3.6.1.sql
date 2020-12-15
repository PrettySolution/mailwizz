--
-- Update sql for MailWizz EMA from version 1.3.6.0 to 1.3.6.1
--

--
-- Table delivery_server
--
ALTER TABLE `delivery_server` ADD `must_confirm_delivery` ENUM('yes','no') NOT NULL DEFAULT 'no' AFTER `force_reply_to`;

--
-- Table campaign_delivery_log
--
ALTER TABLE `campaign_delivery_log` ADD `delivery_confirmed` ENUM('yes','no') NOT NULL DEFAULT 'yes' AFTER `email_message_id`;
ALTER TABLE `campaign_delivery_log` ADD `server_id` INT(11) NULL DEFAULT NULL AFTER `subscriber_id`;
ALTER TABLE `campaign_delivery_log` ADD INDEX `fk_campaign_delivery_log_delivery_server1_idx` (`server_id`);
ALTER TABLE `campaign_delivery_log` ADD CONSTRAINT `fk_campaign_delivery_log_delivery_server1` FOREIGN KEY (`server_id`) REFERENCES `delivery_server` (`server_id`) ON DELETE SET NULL ON UPDATE NO ACTION;

--
-- Table campaign_delivery_log_archive
--
ALTER TABLE `campaign_delivery_log_archive` ADD `delivery_confirmed` ENUM('yes','no') NOT NULL DEFAULT 'yes' AFTER `email_message_id`;
ALTER TABLE `campaign_delivery_log_archive` ADD `server_id` INT(11) NULL DEFAULT NULL AFTER `subscriber_id`;
ALTER TABLE `campaign_delivery_log_archive` ADD INDEX `fk_campaign_delivery_log_archive_delivery_server1_idx` (`server_id`);
ALTER TABLE `campaign_delivery_log_archive` ADD CONSTRAINT `fk_campaign_delivery_log_archive_delivery_server1` FOREIGN KEY (`server_id`) REFERENCES `delivery_server` (`server_id`) ON DELETE SET NULL ON UPDATE NO ACTION;

