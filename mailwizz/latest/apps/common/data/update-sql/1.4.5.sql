--
-- Update sql for MailWizz EMA from version 1.4.4 to 1.4.5
--

--
-- Insert into list_field_type
-- 
INSERT INTO `list_field_type` (`type_id`, `name`, `identifier`, `class_alias`, `description`, `date_added`, `last_updated`) VALUES
  (NULL, 'Geo Country', 'geocountry', 'customer.components.field-builder.geocountry.FieldBuilderTypeGeocountry', 'Geo Country', '2017-10-05 19:35:12', '2017-10-05 19:35:12'),
  (NULL, 'Geo State', 'geostate', 'customer.components.field-builder.geostate.FieldBuilderTypeGeostate', 'Geo State', '2017-10-05 19:35:12', '2017-10-05 19:35:12'),
  (NULL, 'Geo City', 'geocity', 'customer.components.field-builder.geocity.FieldBuilderTypeGeocity', 'Geo City', '2017-10-05 19:35:12', '2017-10-05 19:35:12');
  
  
--
-- Table structure for table `campaign_sent_action_list_field`
--
CREATE TABLE IF NOT EXISTS `campaign_sent_action_list_field` (
  `action_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `campaign_id` int(11) NOT NULL,
  `list_id` int(11) NOT NULL,
  `field_id` int(11) NOT NULL,
  `field_value` varchar(255) NOT NULL,
  `date_added` datetime NOT NULL,
  `last_updated` datetime NOT NULL,
  PRIMARY KEY (`action_id`),
  KEY `fk_campaign_sent_action_list_field_list1_idx` (`list_id`),
  KEY `fk_campaign_sent_action_list_field_campaign1_idx` (`campaign_id`),
  KEY `fk_campaign_sent_action_list_field_list_field1_idx` (`field_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Constraints for table `campaign_sent_action_list_field`
--
ALTER TABLE `campaign_sent_action_list_field`
  ADD CONSTRAINT `fk_campaign_sent_action_list_field_list1` FOREIGN KEY (`list_id`) REFERENCES `list` (`list_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_campaign_sent_action_list_field_campaign1` FOREIGN KEY (`campaign_id`) REFERENCES `campaign` (`campaign_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_campaign_sent_action_list_field_list_field1` FOREIGN KEY (`field_id`) REFERENCES `list_field` (`field_id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Table structure for table `email_box_monitor`
--
CREATE TABLE IF NOT EXISTS `email_box_monitor` (
  `server_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) DEFAULT NULL,
  `hostname` varchar(150) NOT NULL,
  `username` varchar(150) NOT NULL,
  `password` varchar(150) NOT NULL,
  `email` varchar(100) NULL,
  `service` enum('imap','pop3') NOT NULL DEFAULT 'imap',
  `port` int(5) NOT NULL DEFAULT '143',
  `protocol` enum('ssl','tls','notls') NOT NULL DEFAULT 'notls',
  `validate_ssl` enum('yes','no') NOT NULL DEFAULT 'no',
  `locked` enum('yes', 'no') NOT NULL DEFAULT 'no',
  `disable_authenticator` VARCHAR(50) NULL,
  `search_charset` VARCHAR(50) NOT NULL DEFAULT 'UTF-8',
  `delete_all_messages` ENUM('yes','no') NOT NULL DEFAULT 'no',
  `meta_data` LONGBLOB NULL DEFAULT NULL,
  `status` char(15) NOT NULL DEFAULT 'active',
  `date_added` datetime NOT NULL,
  `last_updated` datetime NOT NULL,
  PRIMARY KEY (`server_id`),
  KEY `fk_email_box_monitor_customer1_idx` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Constraints for table `email_box_monitor`
--
ALTER TABLE `email_box_monitor`
  ADD CONSTRAINT `fk_email_box_monitor_customer1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE ON UPDATE NO ACTION;

INSERT INTO `start_page` (`page_id`, `application`, `route`, `icon`, `icon_color`, `heading`, `content`, `date_added`, `last_updated`) VALUES
(NULL, 'backend', 'email_box_monitors/index', 'glyphicon-transfer', '', 'Create the first email box monitor', 'Email box monitors will help monitoring given email boxes and<br />\ntake actions against subscribers based on the contents of the incoming emails.', '2017-10-18 09:39:58', '2017-10-18 09:39:58'),
(NULL, 'customer', 'email_box_monitors/index', 'glyphicon-transfer', '', 'Create the first email box monitor', 'Email box monitors will help monitoring given email boxes and<br />\ntake actions against subscribers based on the contents of the incoming emails.', '2017-10-18 09:40:41', '2017-10-18 09:40:41');
