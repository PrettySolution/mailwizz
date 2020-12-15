--
-- Update sql for MailWizz EMA from version 1.3.4.2 to 1.3.4.3
--

-- create the campaign group table
CREATE TABLE IF NOT EXISTS `campaign_group` (
  `group_id` int(11) NOT NULL AUTO_INCREMENT,
  `group_uid` char(13) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `date_added` datetime NOT NULL,
  `last_updated` datetime NOT NULL,
  PRIMARY KEY (`group_id`),
  UNIQUE KEY `group_uid` (`group_uid`),
  KEY `fk_campaign_group_customer1_idx` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

-- add the fk to the table
ALTER TABLE `campaign_group`
  ADD CONSTRAINT `fk_campaign_group_customer1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE ON UPDATE NO ACTION;

-- create campaign open action subscriber table
CREATE TABLE IF NOT EXISTS `campaign_open_action_subscriber` (
  `action_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `campaign_id` int(11) NOT NULL,
  `list_id` int(11) NOT NULL,
  `action` char(5) NOT NULL DEFAULT 'copy',
  `date_added` datetime NOT NULL,
  `last_updated` datetime NOT NULL,
  PRIMARY KEY (`action_id`),
  KEY `fk_campaign_open_action_subscriber_campaign1_idx` (`campaign_id`),
  KEY `fk_campaign_open_action_subscriber_list1_idx` (`list_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- add the fk to the table
ALTER TABLE `campaign_open_action_subscriber`
  ADD CONSTRAINT `fk_campaign_open_action_subscriber_campaign1` FOREIGN KEY (`campaign_id`) REFERENCES `campaign` (`campaign_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_campaign_open_action_subscriber_list1` FOREIGN KEY (`list_id`) REFERENCES `list` (`list_id`) ON DELETE CASCADE ON UPDATE NO ACTION;

-- alter the campaign table
ALTER TABLE `campaign` 
    ADD `group_id` int(11) DEFAULT NULL AFTER `segment_id`, 
    ADD `type` char(15) NOT NULL DEFAULT 'regular' AFTER `group_id`,
    ADD KEY `fk_campaign_campaign_group1_idx` (`group_id`),
    ADD KEY `type` (`type`),
    ADD CONSTRAINT `fk_campaign_campaign_group1` FOREIGN KEY (`group_id`) REFERENCES `campaign_group` (`group_id`) ON DELETE SET NULL ON UPDATE NO ACTION;

-- campaign option
ALTER TABLE `campaign_option` 
    ADD `autoresponder_event` char(20) NOT NULL DEFAULT 'AFTER-SUBSCRIBE' AFTER `plain_text_email`,
    ADD `autoresponder_time_unit` varchar(6) NOT NULL DEFAULT 'day' AFTER `autoresponder_event`, 
    ADD `autoresponder_time_value` int(11) NOT NULL DEFAULT '0' AFTER `autoresponder_time_unit`, 
    ADD `autoresponder_include_imported` ENUM('yes','no') NOT NULL DEFAULT 'no' AFTER `autoresponder_time_value`;

-- create the customer group table
CREATE TABLE IF NOT EXISTS `customer_group` (
  `group_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `is_default` enum('yes','no') NOT NULL DEFAULT 'no',
  `date_added` datetime NOT NULL,
  `last_updated` datetime NOT NULL,
  PRIMARY KEY (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

-- create the customer group option table
CREATE TABLE IF NOT EXISTS `customer_group_option` (
  `option_id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL,
  `code` varchar(255) NOT NULL,
  `is_serialized` tinyint(1) NOT NULL DEFAULT '0',
  `value` longblob,
  `date_added` datetime NOT NULL,
  `last_updated` datetime NOT NULL,
  PRIMARY KEY (`option_id`),
  KEY `fk_customer_group_option_customer_group1_idx` (`group_id`),
  KEY `group_code` (`group_id`,`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- alter the table and add the constraints
ALTER TABLE `customer_group_option`
  ADD CONSTRAINT `fk_customer_group_option_customer_group1` FOREIGN KEY (`group_id`) REFERENCES `customer_group` (`group_id`) ON DELETE CASCADE ON UPDATE NO ACTION;
  
-- alter customer table to add the group_id key
ALTER TABLE `customer` 
    ADD `group_id` INT(11) NULL DEFAULT NULL AFTER `customer_uid`,
    ADD KEY `fk_customer_customer_group1_idx` (`group_id`),
    ADD CONSTRAINT `fk_customer_customer_group1` FOREIGN KEY (`group_id`) REFERENCES `customer_group`(`group_id`) ON DELETE SET NULL ON UPDATE NO ACTION;

-- custom field
INSERT INTO `list_field_type` (`type_id` ,`name` ,`identifier` ,`class_alias` ,`description` ,`date_added` ,`last_updated`) VALUES 
(NULL, 'Dropdown', 'dropdown', 'customer.components.field-builder.dropdown.FieldBuilderTypeDropdown', 'Dropdown', '2013-09-01 14:26:26', '2013-09-01 14:26:29');

-- customer confirmation key
ALTER TABLE `customer` ADD `confirmation_key` CHAR(40) NULL AFTER `removable`;

-- add the customer quota mark
CREATE TABLE IF NOT EXISTS `customer_quota_mark` (
  `mark_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`mark_id`),
  KEY `fk_customer_quota_mark_customer1_idx` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- and the constraint
ALTER TABLE `customer_quota_mark`
  ADD CONSTRAINT `fk_customer_quota_mark_customer1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE ON UPDATE NO ACTION;
  
-- delivery server, bounce server, feedback loop server locked status
ALTER TABLE `delivery_server` ADD `locked` ENUM('yes', 'no') NOT NULL DEFAULT 'no' AFTER `confirmation_key`;
ALTER TABLE `bounce_server` ADD `locked` ENUM('yes', 'no') NOT NULL DEFAULT 'no' AFTER `validate_ssl`;
ALTER TABLE `feedback_loop_server` ADD `locked` ENUM('yes', 'no') NOT NULL DEFAULT 'no' AFTER `validate_ssl`;
  
-- campaign template change, prone to error...?
ALTER TABLE `campaign_template` 
    DROP KEY `template_id`,
    DROP FOREIGN KEY `fk_campaign_template1`, 
    CHANGE `template_id` `customer_template_id` INT(11) NULL,
    ADD KEY `fk_customer_email_template1_idx`(`customer_template_id`),
    ADD CONSTRAINT `fk_customer_email_template1` FOREIGN KEY(`customer_template_id`) REFERENCES `customer_email_template`(`template_id`) ON DELETE SET NULL ON UPDATE NO ACTION;

ALTER TABLE `campaign_template` 
    DROP PRIMARY KEY, 
    DROP FOREIGN KEY `fk_campaign_template_campaign1`,
    CHANGE `campaign_id` `campaign_id` INT(11) NOT NULL;

ALTER TABLE `campaign_template` 
    ADD `template_id` INT(11) NOT NULL AUTO_INCREMENT FIRST,
    ADD PRIMARY KEY (`template_id`),
    ADD KEY `fk_campaign_template_campaign1_idx`(`campaign_id`),
    ADD CONSTRAINT `fk_campaign_template_campaign1` FOREIGN KEY(`campaign_id`) REFERENCES `campaign`(`campaign_id`) ON DELETE CASCADE ON UPDATE NO ACTION;

-- create subscriber campaign click action table
CREATE TABLE IF NOT EXISTS `campaign_template_url_action_subscriber` (
  `url_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `campaign_id` int(11) NOT NULL,
  `list_id` int(11) NOT NULL,
  `template_id` int(11) NOT NULL,
  `url` text NOT NULL,
  `action` char(5) NOT NULL DEFAULT 'copy',
  `date_added` datetime NOT NULL,
  `last_updated` datetime NOT NULL,
  PRIMARY KEY (`url_id`),
  KEY `fk_campaign_template_url_action_subscriber_campaign_t_idx` (`template_id`),
  KEY `fk_campaign_template_url_action_subscriber_list1_idx` (`list_id`),
  KEY `fk_campaign_template_url_action_subscriber_campaign1_idx` (`campaign_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- and the constaints
ALTER TABLE `campaign_template_url_action_subscriber`
  ADD CONSTRAINT `fk_campaign_template_url_action_subscriber_campaign_tem1` FOREIGN KEY (`template_id`) REFERENCES `campaign_template` (`template_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_campaign_template_url_action_subscriber_list1` FOREIGN KEY (`list_id`) REFERENCES `list` (`list_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_campaign_template_url_action_subscriber_campaign1` FOREIGN KEY (`campaign_id`) REFERENCES `campaign` (`campaign_id`) ON DELETE CASCADE ON UPDATE NO ACTION;

-- this is prone to error if high number of subscribers so run it last
ALTER TABLE `list_subscriber` CHANGE `source` `source` ENUM('web','api','import') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'web';