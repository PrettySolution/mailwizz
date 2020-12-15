--
-- Update sql for MailWizz EMA from version 1.3.3 to 1.3.3.1
--

--
-- add new options
--
INSERT INTO `option` (`category`, `key`, `value`, `is_serialized`, `date_added`, `last_updated`) VALUES
('system.cron.send_campaigns', 'parallel_processes_per_campaign', 0x33, 0, '2014-02-16 22:39:58', '2014-02-16 22:39:58');

--
-- alter bounce servers table
--
ALTER TABLE `bounce_server`
  ADD `customer_id` INT(11) NULL DEFAULT NULL AFTER `server_id`,
  ADD INDEX `fk_bounce_server_customer1_idx` (`customer_id`),
  ADD CONSTRAINT `fk_bounce_server_customer1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- alter delivery servers table
--
ALTER TABLE `delivery_server`
  ADD `customer_id` INT(11) NULL DEFAULT NULL AFTER `server_id`,
  ADD INDEX `fk_delivery_server_customer1_idx` (`customer_id`),
  ADD CONSTRAINT `fk_delivery_server_customer1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- alter customers table
--
ALTER TABLE `customer` ADD `hourly_quota` INT NOT NULL DEFAULT '0' AFTER `timezone` ;

--
-- Table structure for table `delivery_server_usage_log`
--
CREATE TABLE IF NOT EXISTS `delivery_server_usage_log` (
  `log_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `server_id` int(11) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `delivery_for` char(15) NOT NULL DEFAULT 'system',
  `customer_countable` enum('yes','no') NOT NULL DEFAULT 'yes',
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`log_id`),
  KEY `fk_delivery_server_usage_log_delivery_server1_idx` (`server_id`),
  KEY `fk_delivery_server_usage_log_customer1_idx` (`customer_id`),
  KEY `server_date` (`server_id`,`date_added`),
  KEY `customer_countable_date` (`customer_id`,`customer_countable`,`date_added`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Constraints for table `delivery_server_usage_log`
--
ALTER TABLE `delivery_server_usage_log`
  ADD CONSTRAINT `fk_delivery_server_usage_log_delivery_server1` FOREIGN KEY (`server_id`) REFERENCES `delivery_server` (`server_id`) ON DELETE SET NULL ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_delivery_server_usage_log_customer1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`) ON DELETE SET NULL ON UPDATE NO ACTION;

--
-- Table structure for table `feedback_loop_server`
--
CREATE TABLE IF NOT EXISTS `feedback_loop_server` (
  `server_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) DEFAULT NULL,
  `hostname` varchar(150) NOT NULL,
  `username` varchar(150) NOT NULL,
  `password` varchar(150) NOT NULL,
  `service` enum('imap','pop3') NOT NULL DEFAULT 'imap',
  `port` int(5) NOT NULL DEFAULT '143',
  `protocol` enum('ssl','tls','notls') NOT NULL DEFAULT 'notls',
  `validate_ssl` enum('yes','no') NOT NULL DEFAULT 'no',
  `status` char(15) NOT NULL DEFAULT 'active',
  `date_added` datetime NOT NULL,
  `last_updated` datetime NOT NULL,
  PRIMARY KEY (`server_id`),
  KEY `fk_feedback_loop_server_customer1_idx` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Constraints for table `feedback_loop_server`
--
ALTER TABLE `feedback_loop_server`
  ADD CONSTRAINT `fk_feedback_loop_server_customer1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- alter campaign_track_unsubscribe table
--
ALTER TABLE `campaign_track_unsubscribe` ADD `note` VARCHAR(255) NULL AFTER `user_agent`;
-- --------------------------------------------------------