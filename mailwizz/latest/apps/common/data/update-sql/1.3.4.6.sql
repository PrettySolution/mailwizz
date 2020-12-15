--
-- Update sql for MailWizz EMA from version 1.3.4.5 to 1.3.4.6
--

INSERT INTO `tag_registry` (`tag_id`, `tag`, `description`, `date_added`, `last_updated`) VALUES
(NULL, '[SUBSCRIBER_DATE_ADDED]', NULL, '2014-06-23 00:00:00', '2014-06-23 00:00:00'),
(NULL, '[SUBSCRIBER_DATE_ADDED_LOCALIZED]', NULL, '2014-06-23 00:00:00', '2014-06-23 00:00:00'),
(NULL, '[DATE]', NULL, '2014-06-23 00:00:00', '2014-06-23 00:00:00'),
(NULL, '[DATETIME]', NULL, '2014-06-23 00:00:00', '2014-06-23 00:00:00');

ALTER TABLE `list_field_value` CHANGE `value` `value` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE `list_customer_notification` CHANGE `subscribe_to` `subscribe_to` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE `list_customer_notification` CHANGE `unsubscribe_to` `unsubscribe_to` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE `list_customer_notification` CHANGE `daily_to` `daily_to` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE `campaign_option` CHANGE `email_stats` `email_stats` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;

--
-- Table structure for table `tracking_domain`
--

CREATE TABLE IF NOT EXISTS `tracking_domain` (
  `domain_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `date_added` datetime NOT NULL,
  `last_updated` datetime NOT NULL,
  PRIMARY KEY (`domain_id`),
  KEY `fk_tracking_domain_customer1_idx` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

--
-- Constraints for table `tracking_domain`
--
ALTER TABLE `tracking_domain`
  ADD CONSTRAINT `fk_tracking_domain_customer1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Alter delivery servers table
--
ALTER TABLE `delivery_server` ADD `tracking_domain_id` INT(11) NULL AFTER `bounce_server_id`;
ALTER TABLE `delivery_server` ADD KEY `fk_delivery_server_tracking_domain1_idx` (`tracking_domain_id`);
ALTER TABLE `delivery_server` 
  ADD CONSTRAINT `fk_delivery_server_tracking_domain1` FOREIGN KEY (`tracking_domain_id`) REFERENCES `tracking_domain` (`domain_id`) ON DELETE SET NULL ON UPDATE NO ACTION;

ALTER TABLE `delivery_server` ADD `from_email` VARCHAR(150) NOT NULL AFTER `timeout`;
ALTER TABLE `delivery_server` ADD `from_name` VARCHAR(150) NOT NULL AFTER `from_email`;

--
-- Set the delivery servers as inactive
--
UPDATE `delivery_server` SET `status` = 'inactive' WHERE 1;

--
-- New indexes, very slow process, even prone to error...
--
ALTER TABLE `list_field_value` ADD KEY `field_id_value`(`field_id`,`value`);
ALTER TABLE `list_subscriber` ADD KEY `list_id_status`(`list_id`,`status`);
