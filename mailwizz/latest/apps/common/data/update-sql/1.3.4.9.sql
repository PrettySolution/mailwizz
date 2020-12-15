--
-- Update sql for MailWizz EMA from version 1.3.4.8 to 1.3.4.9
--

--
-- Table structure for table `delivery_server_to_customer_group`
--

CREATE TABLE IF NOT EXISTS `delivery_server_to_customer_group` (
  `server_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  PRIMARY KEY (`server_id`,`group_id`),
  KEY `fk_delivery_server_to_customer_group_customer_group1_idx` (`group_id`), 
  KEY `fk_delivery_server_to_customer_group_delivery_server1_idx` (`server_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Constraints for table `delivery_server_to_customer_group`
--
ALTER TABLE `delivery_server_to_customer_group`
    ADD CONSTRAINT `fk_delivery_server_to_customer_group_delivery_server1` FOREIGN KEY (`server_id`) REFERENCES `delivery_server` (`server_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
    ADD CONSTRAINT `fk_delivery_server_to_customer_group_customer_group1` FOREIGN KEY (`group_id`) REFERENCES `customer_group` (`group_id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Alter statement for table `list`
--
ALTER TABLE `list` ADD `display_name` VARCHAR(100) NOT NULL AFTER `name`;
UPDATE `list` set `display_name` = `name` WHERE 1;

--
-- Table structure for table `campaign_delivery_log_archive`
--

CREATE TABLE IF NOT EXISTS `campaign_delivery_log_archive` (
  `log_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `campaign_id` int(11) NOT NULL,
  `subscriber_id` int(11) NOT NULL,
  `message` text NULL,
  `processed` enum('yes','no') NOT NULL DEFAULT 'no',
  `retries` int(1) NOT NULL DEFAULT '0',
  `max_retries` int(1) NOT NULL DEFAULT '3',
  `email_message_id` varchar(255) NULL,
  `status` char(15) NOT NULL DEFAULT 'success',
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`log_id`),
  KEY `fk_campaign_delivery_log_archive_list_subscriber1_idx` (`subscriber_id`),
  KEY `fk_campaign_delivery_log_archive_campaign1_idx` (`campaign_id`),
  KEY `sub_proc_status` (`subscriber_id`,`processed`,`status`),
  KEY `email_message_id` (`email_message_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Constraints for table `campaign_delivery_log_archive`
--
ALTER TABLE `campaign_delivery_log_archive`
  ADD CONSTRAINT `fk_campaign_delivery_log_archive_campaign1` FOREIGN KEY (`campaign_id`) REFERENCES `campaign` (`campaign_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_campaign_delivery_log_archive_list_subscriber1` FOREIGN KEY (`subscriber_id`) REFERENCES `list_subscriber` (`subscriber_id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Alter statement for table `campaign`
--
ALTER TABLE `campaign` ADD `delivery_logs_archived` ENUM('yes','no') NOT NULL DEFAULT 'no' AFTER `send_at`;
ALTER TABLE `campaign` ADD KEY `status_delivery_logs_archived_campaign_id` (`status`, `delivery_logs_archived`, `campaign_id`);