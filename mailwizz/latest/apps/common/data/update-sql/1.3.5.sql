--
-- Update sql for MailWizz EMA from version 1.3.4.9 to 1.3.5
--

--
-- Tag insertion into registry
--

INSERT INTO `tag_registry` (`tag_id`, `tag`, `description`, `date_added`, `last_updated`) VALUES 
(NULL, '[RANDOM_CONTENT]', NULL, '2014-11-18 00:00:00', '2014-11-18 00:00:00'),
(NULL, '[CAMPAIGN_REPORT_ABUSE_URL]', NULL, '2014-11-18 00:00:00', '2014-11-18 00:00:00');

--
-- Delivery server table
--
ALTER TABLE `delivery_server` 
    ADD `use_for` CHAR(15) NOT NULL DEFAULT 'all' AFTER `locked`, 
    ADD `use_queue` ENUM('yes','no') NOT NULL DEFAULT 'no' AFTER `use_for`,
    ADD `signing_enabled` ENUM('yes','no') NOT NULL DEFAULT 'yes' AFTER `use_queue`,
    ADD `force_from` VARCHAR(50) NOT NULL DEFAULT 'never' AFTER `signing_enabled`;

--
-- User group table
--
CREATE TABLE IF NOT EXISTS `user_group` (
  `group_id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `date_added` DATETIME NOT NULL,
  `last_updated` DATETIME NOT NULL,
  PRIMARY KEY (`group_id`), 
  UNIQUE KEY `name_UNIQUE` (`name`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

-- 
-- User group route access table
-- 
CREATE TABLE IF NOT EXISTS `user_group_route_access` (
  `route_id` INT NOT NULL AUTO_INCREMENT,
  `group_id` INT NOT NULL,
  `route`  VARCHAR(255) NOT NULL,
  `access` ENUM('allow','deny') NOT NULL DEFAULT 'allow',
  `date_added` DATETIME NULL,
  PRIMARY KEY (`route_id`),
  KEY `fk_user_group_route_access_user_group1_idx` (`group_id`),
  KEY `group_route_access` (`group_id`, `route`, `access`),
  CONSTRAINT `fk_user_group_route_access_user_group1`
    FOREIGN KEY (`group_id`)
    REFERENCES `user_group` (`group_id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB DEFAULT CHARSET=utf8;

--
-- User table
--
ALTER TABLE `user` 
    ADD `group_id` INT NULL DEFAULT NULL AFTER `user_uid`, 
    ADD KEY `fk_user_user_group1_idx` (`group_id`),
    ADD CONSTRAINT `fk_user_user_group1` FOREIGN KEY (`group_id`) REFERENCES `user_group`(`group_id`) ON DELETE SET NULL ON UPDATE NO ACTION;
    
--
-- Customer email template table
--
ALTER TABLE `customer_email_template` ADD `sort_order` INT(11) NOT NULL DEFAULT 0 AFTER `minify`;

--
-- Bounce and fbl server table
--
ALTER TABLE `bounce_server` ADD `email` varchar(100) NULL AFTER `password`;
ALTER TABLE `feedback_loop_server` ADD `email` varchar(100) NULL AFTER `password`;

--
-- Campaign table
--
ALTER TABLE `campaign` ADD `started_at` DATETIME NULL AFTER `send_at`, ADD `finished_at` DATETIME NULL AFTER `started_at`;

--
-- List segment operator table
--

INSERT INTO `list_segment_operator` (`operator_id`, `name`, `slug`, `date_added`, `last_updated`) VALUES
(NULL, 'not starts with', 'not-starts', '2015-01-06 16:06:01', '2015-01-06 16:06:01'),
(NULL, 'not ends with', 'not-ends', '2015-01-06 16:06:01', '2015-01-06 16:06:01');

--
-- List field type table
--
INSERT INTO `list_field_type` (`type_id`, `name`, `identifier`, `class_alias`, `description`, `date_added`, `last_updated`) VALUES 
(NULL, 'Date', 'date', 'customer.components.field-builder.date.FieldBuilderTypeDate', 'Date', '2014-05-27 14:26:26', '2014-05-27 00:00:00'),
(NULL, 'Datetime', 'datetime', 'customer.components.field-builder.datetime.FieldBuilderTypeDatetime', 'Datetime', '2014-05-27 14:26:26', '2014-05-27 00:00:00'),
(NULL, 'Textarea', 'textarea', 'customer.components.field-builder.textarea.FieldBuilderTypeTextarea', 'Textarea', '2014-05-27 14:26:26', '2014-05-27 00:00:00');

--
-- Table `list_subscriber_action`
--
CREATE TABLE IF NOT EXISTS `list_subscriber_action` (
  `action_id` INT NOT NULL AUTO_INCREMENT,
  `source_list_id` INT(11) NOT NULL,
  `source_action` CHAR(15) NOT NULL DEFAULT 'subscribe',
  `target_list_id` INT(11) NOT NULL,
  `target_action` CHAR(15) NOT NULL DEFAULT 'unsubscribe',
  PRIMARY KEY (`action_id`),
  INDEX `fk_list_subscriber_action_list1_idx` (`source_list_id`),
  INDEX `fk_list_subscriber_action_list2_idx` (`target_list_id`),
  CONSTRAINT `fk_list_subscriber_action_list1`
    FOREIGN KEY (`source_list_id`)
    REFERENCES `list` (`list_id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_list_subscriber_action_list2`
    FOREIGN KEY (`target_list_id`)
    REFERENCES `list` (`list_id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB DEFAULT CHARSET=utf8;

--
-- Campaign option
--
ALTER TABLE `campaign_option` 
    ADD `regular_open_unopen_action` CHAR(10) NULL AFTER `email_stats`,
    ADD `regular_open_unopen_campaign_id` INT(11) NULL AFTER `regular_open_unopen_action`,
    ADD KEY `fk_campaign_option_campaign3_idx` (`regular_open_unopen_campaign_id`),
    ADD CONSTRAINT `fk_campaign_option_campaign3` FOREIGN KEY (`regular_open_unopen_campaign_id`) REFERENCES `campaign` (`campaign_id`) ON DELETE SET NULL ON UPDATE NO ACTION;

--
-- Campaign abuse report
--  
CREATE TABLE IF NOT EXISTS `campaign_abuse_report` (
  `report_id` INT NOT NULL AUTO_INCREMENT,
  `customer_id` INT(11) NULL,
  `campaign_id` INT(11) NULL,
  `list_id` INT(11) NULL,
  `subscriber_id` INT(11) NULL,
  `customer_info` VARCHAR(255) NOT NULL,
  `campaign_info` VARCHAR(255) NOT NULL,
  `list_info` VARCHAR(255) NOT NULL,
  `subscriber_info` VARCHAR(255) NOT NULL,
  `reason` VARCHAR(255) NOT NULL,
  `log` VARCHAR(255) NULL DEFAULT NULL,
  `date_added` DATETIME NOT NULL,
  `last_updated` DATETIME NOT NULL,
  PRIMARY KEY (`report_id`),
  INDEX `fk_campaign_abuse_report_campaign1_idx` (`campaign_id`),
  INDEX `fk_campaign_abuse_report_customer1_idx` (`customer_id`),
  INDEX `fk_campaign_abuse_report_list1_idx` (`list_id`),
  INDEX `fk_campaign_abuse_report_list_subscriber1_idx` (`subscriber_id`),
  CONSTRAINT `fk_campaign_abuse_report_campaign1` FOREIGN KEY (`campaign_id`) REFERENCES `campaign` (`campaign_id`) ON DELETE SET NULL ON UPDATE NO ACTION,
  CONSTRAINT `fk_campaign_abuse_report_customer1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`) ON DELETE SET NULL ON UPDATE NO ACTION,
  CONSTRAINT `fk_campaign_abuse_report_list1` FOREIGN KEY (`list_id`) REFERENCES `list` (`list_id`) ON DELETE SET NULL ON UPDATE NO ACTION,
  CONSTRAINT `fk_campaign_abuse_report_list_subscriber1` FOREIGN KEY (`subscriber_id`) REFERENCES `list_subscriber` (`subscriber_id`) ON DELETE SET NULL ON UPDATE NO ACTION)
ENGINE = InnoDB DEFAULT CHARSET=utf8;

--
-- Guest fail attempt
--
CREATE TABLE IF NOT EXISTS `guest_fail_attempt` (
  `attempt_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `ip_address_hash` char(32) NOT NULL,
  `user_agent` varchar(255) NOT NULL,
  `place` varchar(255) NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`attempt_id`),
  KEY `ip_hash_date` (`ip_address_hash`, `date_added`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;