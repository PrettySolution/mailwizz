--
-- Update sql for MailWizz EMA from version 1.3.4.4 to 1.3.4.5
--

ALTER TABLE `price_plan` CHANGE `price` `price` DECIMAL(15,4) NOT NULL DEFAULT '0.0000';

ALTER TABLE `price_plan_order` 
    CHANGE `subtotal` `subtotal` DECIMAL(15,4) NOT NULL DEFAULT '0.0000',
    CHANGE `discount` `discount` DECIMAL(15,4) NOT NULL DEFAULT '0.0000',
    CHANGE `total` `total` DECIMAL(15,4) NOT NULL DEFAULT '0.0000';
    
ALTER TABLE `price_plan_promo_code` 
    CHANGE `discount` `discount` DECIMAL(15,4) NOT NULL DEFAULT '0.0000',
    CHANGE `total_amount` `total_amount` DECIMAL(15,4) NOT NULL DEFAULT '0.0000';
    
INSERT INTO `list_field_type` (`type_id`, `name`, `identifier`, `class_alias`, `description`, `date_added`, `last_updated`) VALUES 
(3, 'Multiselect', 'multiselect', 'customer.components.field-builder.multiselect.FieldBuilderTypeMultiselect', 'Multiselect', '2014-05-27 14:26:26', '2014-05-27 00:00:00');

ALTER TABLE `currency` ADD UNIQUE KEY `code_UNIQUE` (`code`);

--
-- Table structure for table `tax`
--

CREATE TABLE IF NOT EXISTS `tax` (
  `tax_id` int(11) NOT NULL AUTO_INCREMENT,
  `country_id` int(11) DEFAULT NULL,
  `zone_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `percent` decimal(4,2) NOT NULL DEFAULT '0.00',
  `is_global` enum('yes','no') NOT NULL DEFAULT 'no',
  `status` char(15) NOT NULL,
  `date_added` datetime NOT NULL,
  `last_updated` datetime NOT NULL,
  PRIMARY KEY (`tax_id`),
  KEY `fk_tax_zone1_idx` (`zone_id`),
  KEY `fk_tax_country1_idx` (`country_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

ALTER TABLE `tax`
  ADD CONSTRAINT `fk_tax_country1` FOREIGN KEY (`country_id`) REFERENCES `country` (`country_id`) ON DELETE SET NULL ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_tax_zone1` FOREIGN KEY (`zone_id`) REFERENCES `zone` (`zone_id`) ON DELETE SET NULL ON UPDATE NO ACTION;

-- --------------------------------------------------------


ALTER TABLE `price_plan_order` 
    ADD `tax_id` INT(11) NULL AFTER `promo_code_id`,
    ADD `tax_percent` DECIMAL(4,2) NOT NULL DEFAULT 0.00 AFTER `subtotal`,
    ADD `tax_value` DECIMAL(15,4) NOT NULL DEFAULT 0.0000 AFTER `tax_percent`,
    ADD KEY `fk_price_plan_order_tax1_idx` (`tax_id` ASC),
    ADD CONSTRAINT `fk_price_plan_order_tax1` FOREIGN KEY (`tax_id`) REFERENCES `tax` (`tax_id`) ON DELETE SET NULL ON UPDATE NO ACTION;

ALTER TABLE `delivery_server`
  DROP `hourly_sent`,
  DROP `custom_from_header`,
  DROP `last_sent`;
  
--
-- Table structure for table `price_plan_order_note`
--

CREATE TABLE IF NOT EXISTS `price_plan_order_note` (
  `note_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `customer_id` int(11) NULL,
  `user_id` int(11) NULL,
  `note` varchar(255) NOT NULL,
  `date_added` datetime NOT NULL,
  `last_updated` datetime NOT NULL,
  PRIMARY KEY (`note_id`),
  KEY `fk_price_plan_order_note_price_plan_order1_idx` (`order_id`),
  KEY `fk_price_plan_order_note_customer1_idx` (`customer_id`),
  KEY `fk_price_plan_order_note_user1_idx` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

ALTER TABLE `price_plan_order_note`
  ADD CONSTRAINT `fk_price_plan_order_note_price_plan_order1` FOREIGN KEY (`order_id`) REFERENCES `price_plan_order` (`order_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_price_plan_order_note_customer1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`) ON DELETE SET NULL ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_price_plan_order_note_user1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE SET NULL ON UPDATE NO ACTION;

-- --------------------------------------------------------

--
-- Table structure for table `delivery_server_domain_policy`
--

CREATE TABLE IF NOT EXISTS `delivery_server_domain_policy` (
  `domain_id` int(11) NOT NULL AUTO_INCREMENT,
  `server_id` int(11) NOT NULL,
  `domain` varchar(64) NOT NULL,
  `policy` char(15) NOT NULL DEFAULT 'allow',
  `date_added` datetime NOT NULL,
  `last_updated` datetime NOT NULL,
  PRIMARY KEY (`domain_id`),
  KEY `fk_delivery_server_domain_policy_delivery_server1_idx` (`server_id`),
  KEY `server_domain_policy` (`server_id`, `domain`, `policy`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

ALTER TABLE `delivery_server_domain_policy`
  ADD CONSTRAINT `fk_delivery_server_domain_policy_delivery_server1` FOREIGN KEY (`server_id`) REFERENCES `delivery_server` (`server_id`) ON DELETE CASCADE ON UPDATE NO ACTION;

-- --------------------------------------------------------

--
-- Table structure for table `campaign_temporary_source`
--

CREATE TABLE IF NOT EXISTS `campaign_temporary_source` (
  `source_id` int(11) NOT NULL AUTO_INCREMENT,
  `campaign_id` int(11) NOT NULL,
  `list_id` int(11) NOT NULL,
  `segment_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`source_id`),
  KEY `fk_campaign_temporary_source_campaign1_idx` (`campaign_id`),
  KEY `fk_campaign_temporary_source_list1_idx` (`list_id`),
  KEY `fk_campaign_temporary_source_list_segment1_idx` (`segment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

ALTER TABLE `campaign_temporary_source`
  ADD CONSTRAINT `fk_campaign_temporary_source_campaign1` FOREIGN KEY (`campaign_id`) REFERENCES `campaign` (`campaign_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_campaign_temporary_source_list1` FOREIGN KEY (`list_id`) REFERENCES `list` (`list_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_campaign_temporary_source_list_segment1` FOREIGN KEY (`segment_id`) REFERENCES `list_segment` (`segment_id`) ON DELETE CASCADE ON UPDATE NO ACTION;

-- --------------------------------------------------------


--
-- Table structure for table `transactional_email`
--

CREATE TABLE IF NOT EXISTS `transactional_email` (
  `email_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `email_uid` char(13) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `to_email` varchar(150) NOT NULL,
  `to_name` varchar(150) NOT NULL,
  `from_email` varchar(150) NOT NULL,
  `from_name` varchar(150) NOT NULL,
  `reply_to_email` varchar(150) NULL,
  `reply_to_name` varchar(150) NULL,
  `subject` varchar(255) NOT NULL,
  `body` longblob NOT NULL,
  `plain_text` longblob NOT NULL,
  `priority` tinyint(1) NOT NULL DEFAULT '5',
  `retries` tinyint(1) NOT NULL DEFAULT '0',
  `max_retries` tinyint(1) NOT NULL DEFAULT '3',
  `send_at` datetime NOT NULL,
  `status` char(15) NOT NULL DEFAULT 'unsent',
  `date_added` datetime NOT NULL,
  `last_updated` datetime NOT NULL,
  PRIMARY KEY (`email_id`),
  UNIQUE KEY `email_uid_UNIQUE` (`email_uid`),
  KEY `fk_transactional_email_customer1_idx` (`customer_id`),
  KEY `status_send_at_retries_max_retries` (`status`, `send_at`, `retries`, `max_retries`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

ALTER TABLE `transactional_email`
  ADD CONSTRAINT `fk_transactional_email_customer1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE ON UPDATE NO ACTION;

-- --------------------------------------------------------

--
-- Table structure for table `transactional_email_log`
--

CREATE TABLE IF NOT EXISTS `transactional_email_log` (
  `log_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `email_id` bigint(20) NOT NULL,
  `message` text NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`log_id`),
  KEY `fk_transactional_email_log_transactional_email1_idx` (`email_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

ALTER TABLE `transactional_email_log`
  ADD CONSTRAINT `fk_transactional_email_log_transactional_email1` FOREIGN KEY (`email_id`) REFERENCES `transactional_email` (`email_id`) ON DELETE CASCADE ON UPDATE NO ACTION;

-- --------------------------------------------------------

--
-- Table structure for table `campaign_open_action_list_field`
--

CREATE TABLE IF NOT EXISTS `campaign_open_action_list_field` (
  `action_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `campaign_id` int(11) NOT NULL,
  `list_id` int(11) NOT NULL,
  `field_id` int(11) NOT NULL,
  `field_value` varchar(255) NOT NULL,
  `date_added` datetime NOT NULL,
  `last_updated` datetime NOT NULL,
  PRIMARY KEY (`action_id`),
  KEY `fk_campaign_open_action_list_field_list1_idx` (`list_id`),
  KEY `fk_campaign_open_action_list_field_campaign1_idx` (`campaign_id`),
  KEY `fk_campaign_open_action_list_field_list_field1_idx` (`field_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

ALTER TABLE `campaign_open_action_list_field`
  ADD CONSTRAINT `fk_campaign_open_action_list_field_list1` FOREIGN KEY (`list_id`) REFERENCES `list` (`list_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_campaign_open_action_list_field_campaign1` FOREIGN KEY (`campaign_id`) REFERENCES `campaign` (`campaign_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_campaign_open_action_list_field_list_field1` FOREIGN KEY (`field_id`) REFERENCES `list_field` (`field_id`) ON DELETE CASCADE ON UPDATE NO ACTION;
  
-- --------------------------------------------------------

--
-- Table structure for table `campaign_template_url_action_list_field`
--

CREATE TABLE IF NOT EXISTS `campaign_template_url_action_list_field` (
  `url_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `campaign_id` int(11) NOT NULL,
  `template_id` int(11) NOT NULL,
  `list_id` int(11) NOT NULL,
  `field_id` int(11) NOT NULL,
  `field_value` varchar(255) NOT NULL,
  `url` text NOT NULL,
  `date_added` datetime NOT NULL,
  `last_updated` datetime NOT NULL,
  PRIMARY KEY (`url_id`),
  KEY `fk_campaign_template_url_action_list_field_campaign1_idx` (`campaign_id`),
  KEY `fk_campaign_template_url_action_list_field_list1_idx` (`list_id`),
  KEY `fk_campaign_template_url_action_list_field_campaign_temp_idx` (`template_id`),
  KEY `fk_campaign_template_url_action_list_field_list_field1_idx` (`field_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

ALTER TABLE `campaign_template_url_action_list_field`
  ADD CONSTRAINT `fk_campaign_template_url_action_list_field_campaign1` FOREIGN KEY (`campaign_id`) REFERENCES `campaign` (`campaign_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_campaign_template_url_action_list_field_list1` FOREIGN KEY (`list_id`) REFERENCES `list` (`list_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_campaign_template_url_action_list_field_campaign_templa1` FOREIGN KEY (`template_id`) REFERENCES `campaign_template` (`template_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_campaign_template_url_action_list_field_list_field1` FOREIGN KEY (`field_id`) REFERENCES `list_field` (`field_id`) ON DELETE CASCADE ON UPDATE NO ACTION;

-- --------------------------------------------------------

ALTER TABLE `campaign_template` ADD `only_plain_text` ENUM('yes','no') NOT NULL DEFAULT 'no' AFTER `plain_text`;
ALTER TABLE `campaign_delivery_log` 
    ADD `retries` INT(1) NOT NULL DEFAULT '0' AFTER `processed`,
    ADD `max_retries` INT(1) NOT NULL DEFAULT '3' AFTER `retries`;