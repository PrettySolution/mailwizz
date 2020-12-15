--
-- Update sql for MailWizz EMA from version 1.4.3 to 1.4.4
--

--
-- Alter table
--
ALTER TABLE `campaign_option` ADD `processed_count` INT(11) NOT NULL DEFAULT '-1' AFTER `share_reports_mask_email_addresses`;

--
-- Create new table
--
CREATE TABLE IF NOT EXISTS `campaign_complain_log` (
  `log_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `campaign_id` int(11) NOT NULL,
  `subscriber_id` int(11) NOT NULL,
  `message` varchar(255) NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`log_id`),
  UNIQUE KEY `cid_sid` (`campaign_id`, `subscriber_id`),
  KEY `fk_campaign_complain_log_campaign1_idx` (`campaign_id`),
  KEY `fk_campaign_complain_log_list_subscriber1_idx` (`subscriber_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Alter table
--
ALTER TABLE `campaign_complain_log`
  ADD CONSTRAINT `fk_campaign_complain_log_campaign1` FOREIGN KEY (`campaign_id`) REFERENCES `campaign` (`campaign_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_campaign_complain_log_list_subscriber1` FOREIGN KEY (`subscriber_id`) REFERENCES `list_subscriber` (`subscriber_id`) ON DELETE CASCADE ON UPDATE NO ACTION;
  
-- 
-- Alter table 
-- 
ALTER TABLE `delivery_server` ADD `daily_quota` INT(11) NOT NULL DEFAULT '0' AFTER `hourly_quota`;

-- 
-- Alter table 
-- 
ALTER TABLE `campaign_option` CHANGE `url_tracking` `url_tracking` ENUM('yes','no') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'yes';

-- 
-- Alter table 
-- 
ALTER TABLE `campaign_template` ADD `meta_data` LONGBLOB NULL DEFAULT NULL AFTER `minify`;
ALTER TABLE `customer_email_template` ADD `meta_data` LONGBLOB NULL DEFAULT NULL AFTER `minify`;

-- -----------------------------------------------------
-- Table structure for table `customer_suppression_list`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `customer_suppression_list` (
  `list_id` INT NOT NULL AUTO_INCREMENT,
  `list_uid` CHAR(13) NOT NULL,
  `customer_id` INT(11) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `date_added` DATETIME NOT NULL,
  `last_updated` DATETIME NOT NULL,
  PRIMARY KEY (`list_id`),
  INDEX `fk_customer_suppression_list_customer2_idx` (`customer_id`),
  UNIQUE INDEX `list_uid_UNIQUE` (`list_uid`),
  CONSTRAINT `fk_customer_suppression_list_customer2`
  FOREIGN KEY (`customer_id`)
  REFERENCES `customer` (`customer_id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
  ENGINE = InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

-- -----------------------------------------------------
-- Table structure for table `customer_suppression_list_email`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `customer_suppression_list_email` (
  `email_id` INT NOT NULL AUTO_INCREMENT,
  `email_uid` CHAR(13) NOT NULL,
  `list_id` INT NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `date_added` DATETIME NOT NULL,
  `last_updated` DATETIME NOT NULL,
  PRIMARY KEY (`email_id`),
  UNIQUE INDEX `email_uid_UNIQUE` (`email_uid`),
  INDEX `fk_customer_suppression_list_email_customer_suppression_lis_idx` (`list_id`),
  INDEX `list_id_email` (`list_id`,`email`),
  CONSTRAINT `fk_customer_suppression_list_email_customer_suppression_list1`
  FOREIGN KEY (`list_id`)
  REFERENCES `customer_suppression_list` (`list_id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
  ENGINE = InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

--
-- Insert into start pages
-- 
INSERT INTO `start_page` (`page_id`, `application`, `route`, `icon`, `icon_color`, `heading`, `content`, `date_added`, `last_updated`) VALUES
  (NULL, 'customer', 'suppression_lists/index', 'glyphicon-ban-circle', '', 'Manage your suppression lists', 'Create your own suppression lists where you can import email addresses that will never receive emails from you.<br />\nYou will be able to select these lists to be used in various places, such as when sending a campaign.', '2017-09-25 11:40:28', '2017-09-25 11:40:28');

-- -----------------------------------------------------
-- Table structure for table `customer_suppression_list_to_campaign`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `customer_suppression_list_to_campaign` (
  `list_id` INT NOT NULL,
  `campaign_id` INT(11) NOT NULL,
  PRIMARY KEY (`campaign_id`, `list_id`),
  INDEX `fk_customer_suppression_list_to_campaign_list_idx` (`list_id`),
  INDEX `fk_customer_suppression_list_to_campaign_campaign_idx` (`campaign_id`),
  CONSTRAINT `fk_customer_suppression_list_to_campaign_list`
  FOREIGN KEY (`list_id`)
  REFERENCES `customer_suppression_list` (`list_id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_customer_suppression_list_to_campaign_campaign`
  FOREIGN KEY (`campaign_id`)
  REFERENCES `campaign` (`campaign_id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
  ENGINE = InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

--
-- Insert into list_field_type
-- 
INSERT INTO `list_field_type` (`type_id`, `name`, `identifier`, `class_alias`, `description`, `date_added`, `last_updated`) VALUES
  (NULL, 'Checkbox List', 'checkboxlist', 'customer.components.field-builder.checkboxlist.FieldBuilderTypeCheckboxlist', 'Checkbox List', '2017-09-27 19:35:12', '2017-09-27 19:35:12'),
  (NULL, 'Radio List', 'radiolist', 'customer.components.field-builder.radiolist.FieldBuilderTypeRadiolist', 'Radio List', '2017-09-27 19:35:12', '2017-09-27 19:35:12');

