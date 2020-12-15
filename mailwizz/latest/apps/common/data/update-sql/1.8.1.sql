--
-- Update sql for MailWizz EMA from version 1.8.0 to 1.8.1
--

--
-- Table structure for table `survey`
--

DROP TABLE IF EXISTS `survey`;
CREATE TABLE IF NOT EXISTS `survey` (
  `survey_id` int(11) NOT NULL AUTO_INCREMENT,
  `survey_uid` char(13) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `display_name` varchar(255) NOT NULL,
  `description` text NULL DEFAULT NULL,
  `start_at` datetime NULL DEFAULT NULL,
  `end_at` datetime NULL DEFAULT NULL,
  `finish_redirect` VARCHAR(255) DEFAULT NULL,
  `meta_data` BLOB NULL DEFAULT NULL,
  `status` char(15) NOT NULL DEFAULT 'draft',
  `date_added` datetime NOT NULL,
  `last_updated` datetime NOT NULL,
  PRIMARY KEY (`survey_id`),
  UNIQUE KEY `unique_id_UNIQUE` (`survey_uid`),
  KEY `fk_survey_customer1_idx` (`customer_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `survey_responder`
--

DROP TABLE IF EXISTS `survey_responder`;
CREATE TABLE IF NOT EXISTS `survey_responder` (
  `responder_id` int(11) NOT NULL AUTO_INCREMENT,
  `responder_uid` char(13) NOT NULL,
  `survey_id` int(11) NOT NULL,
  `subscriber_id` int(11) NULL DEFAULT NULL,
  `ip_address` varchar(45) NULL,
  `status` char(15) NOT NULL DEFAULT 'active',
  `date_added` datetime NOT NULL,
  `last_updated` datetime NOT NULL,
  PRIMARY KEY (`responder_id`),
  UNIQUE KEY `unique_id_UNIQUE` (`responder_uid`),
  KEY `fk_survey_responder_survey1_idx` (`survey_id`),
  KEY `fk_survey_responder_list_subscriber1_idx` (`subscriber_id`),
  KEY `status_last_updated` (`status`,`last_updated`),
  KEY `survey_id_status`(`survey_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `survey_field_type`
--

DROP TABLE IF EXISTS `survey_field_type`;
CREATE TABLE IF NOT EXISTS `survey_field_type` (
  `type_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `identifier` varchar(50) NOT NULL,
  `class_alias` varchar(255) NOT NULL,
  `description` varchar(255) NULL,
  `date_added` datetime NOT NULL,
  `last_updated` datetime NOT NULL,
  PRIMARY KEY (`type_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- Table structure for table `survey_field`
--

DROP TABLE IF EXISTS `survey_field`;
CREATE TABLE IF NOT EXISTS `survey_field` (
  `field_id` int(11) NOT NULL AUTO_INCREMENT,
  `type_id` int(11) NOT NULL,
  `survey_id` int(11) NOT NULL,
  `label` varchar(255) NOT NULL,
  `default_value` varchar(255) NULL,
  `help_text` varchar(255) NULL,
  `description` varchar(255) NULL,
  `required` enum('yes','no') NOT NULL DEFAULT 'no',
  `visibility` enum('visible','hidden') NOT NULL DEFAULT 'visible',
  `meta_data` BLOB NULL DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT '0',
  `date_added` datetime NOT NULL,
  `last_updated` datetime NOT NULL,
  PRIMARY KEY (`field_id`),
  KEY `fk_survey_field_survey1_idx` (`survey_id`),
  KEY `fk_survey_field_survey_field_type1_idx` (`type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `survey_field_option`
--

DROP TABLE IF EXISTS `survey_field_option`;
CREATE TABLE IF NOT EXISTS `survey_field_option` (
  `option_id` int(11) NOT NULL AUTO_INCREMENT,
  `field_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `value` varchar(255) NOT NULL,
  `is_default` enum('yes','no') NOT NULL DEFAULT 'no',
  `date_added` datetime NOT NULL,
  `last_updated` datetime NOT NULL,
  PRIMARY KEY (`option_id`),
  KEY `fk_survey_field_option_survey_field1_idx` (`field_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `survey_field_value`
--

DROP TABLE IF EXISTS `survey_field_value`;
CREATE TABLE IF NOT EXISTS `survey_field_value` (
  `value_id` int(11) NOT NULL AUTO_INCREMENT,
  `field_id` int(11) NOT NULL,
  `responder_id` int(11) NOT NULL,
  `value` varchar(255) NOT NULL,
  `date_added` datetime NOT NULL,
  `last_updated` datetime NOT NULL,
  PRIMARY KEY (`value_id`),
  KEY `fk_survey_field_value_survey_field1_idx` (`field_id`),
  KEY `fk_survey_field_value_survey_responder1_idx` (`responder_id`),
  KEY `field_responder` (`field_id`,`responder_id`),
  KEY `field_id_value` (`field_id`, `value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Table structure for table `survey_segment`
--

DROP TABLE IF EXISTS `survey_segment`;
CREATE TABLE IF NOT EXISTS `survey_segment` (
  `segment_id` int(11) NOT NULL AUTO_INCREMENT,
  `segment_uid` char(13) NOT NULL,
  `survey_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `operator_match` enum('any','all') NOT NULL DEFAULT 'any',
  `status` char(15) NOT NULL DEFAULT 'active',
  `date_added` datetime NOT NULL,
  `last_updated` datetime NOT NULL,
  PRIMARY KEY (`segment_id`),
  UNIQUE KEY `segment_uid` (`segment_uid`),
  KEY `fk_survey_segment_survey1_idx` (`survey_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `survey_segment_condition`
--

DROP TABLE IF EXISTS `survey_segment_condition`;
CREATE TABLE IF NOT EXISTS `survey_segment_condition` (
    `condition_id` int(11) NOT NULL AUTO_INCREMENT,
    `segment_id` int(11) NOT NULL,
    `operator_id` int(11) NOT NULL,
    `field_id` int(11) NOT NULL,
    `value` varchar(255) NOT NULL,
    `date_added` datetime NOT NULL,
    `last_updated` datetime NOT NULL,
    PRIMARY KEY (`condition_id`),
    KEY `fk_survey_segment_condition_survey_segment_operator1_idx` (`operator_id`),
    KEY `fk_survey_segment_condition_survey_segment1_idx` (`segment_id`),
    KEY `fk_survey_segment_condition_survey_field1_idx` (`field_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `survey_segment_operator`
--

DROP TABLE IF EXISTS `survey_segment_operator`;
CREATE TABLE IF NOT EXISTS `survey_segment_operator` (
   `operator_id` int(11) NOT NULL AUTO_INCREMENT,
   `name` varchar(100) NOT NULL,
   `slug` varchar(100) NOT NULL,
   `date_added` datetime NOT NULL,
   `last_updated` datetime NOT NULL,
   PRIMARY KEY (`operator_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Constraints for table `survey`
--
ALTER TABLE `survey`
  ADD CONSTRAINT `fk_survey_customer1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `survey_responder`
--
ALTER TABLE `survey_responder`
  ADD CONSTRAINT `fk_survey_responder_survey1` FOREIGN KEY (`survey_id`) REFERENCES `survey` (`survey_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_survey_responder_list_subscriber1` FOREIGN KEY (`subscriber_id`) REFERENCES `list_subscriber` (`subscriber_id`) ON DELETE SET NULL ON UPDATE NO ACTION;

--
-- Constraints for table `survey_field`
--
ALTER TABLE `survey_field`
  ADD CONSTRAINT `fk_survey_field_survey1` FOREIGN KEY (`survey_id`) REFERENCES `survey` (`survey_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_survey_field_survey_field_type1` FOREIGN KEY (`type_id`) REFERENCES `survey_field_type` (`type_id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `survey_field_option`
--
ALTER TABLE `survey_field_option`
  ADD CONSTRAINT `fk_survey_field_option_survey_field1` FOREIGN KEY (`field_id`) REFERENCES `survey_field` (`field_id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `survey_field_value`
--
ALTER TABLE `survey_field_value`
  ADD CONSTRAINT `fk_survey_field_value_survey_field1` FOREIGN KEY (`field_id`) REFERENCES `survey_field` (`field_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_survey_field_value_survey_responder1` FOREIGN KEY (`responder_id`) REFERENCES `survey_responder` (`responder_id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `survey_segment`
--
ALTER TABLE `survey_segment`
    ADD CONSTRAINT `fk_survey_segment_survey1` FOREIGN KEY (`survey_id`) REFERENCES `survey` (`survey_id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `survey_segment_condition`
--
ALTER TABLE `survey_segment_condition`
    ADD CONSTRAINT `fk_survey_segment_condition_survey_field1` FOREIGN KEY (`field_id`) REFERENCES `survey_field` (`field_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
    ADD CONSTRAINT `fk_survey_segment_condition_survey_segment1` FOREIGN KEY (`segment_id`) REFERENCES `survey_segment` (`segment_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
    ADD CONSTRAINT `fk_survey_segment_condition_survey_segment_operator1` FOREIGN KEY (`operator_id`) REFERENCES `survey_segment_operator` (`operator_id`) ON DELETE CASCADE ON UPDATE NO ACTION;

-- -----------------------------------------------------
-- Dumping data for table `survey_field_type`
-- -----------------------------------------------------
INSERT INTO `survey_field_type` (`type_id`, `name`, `identifier`, `class_alias`, `description`, `date_added`, `last_updated`) VALUES
(NULL, 'Text', 'text', 'customer.components.survey-field-builder.text.FieldBuilderTypeText', 'Text', NOW(), NOW()),
(NULL, 'Number', 'number', 'customer.components.survey-field-builder.number.FieldBuilderTypeNumber', 'Number', NOW(), NOW()),
(NULL, 'Geo City', 'geocity', 'customer.components.survey-field-builder.geocity.FieldBuilderTypeGeocity', 'Geo City', NOW(), NOW()),
(NULL, 'Geo Country', 'geocountry', 'customer.components.survey-field-builder.geocountry.FieldBuilderTypeGeocountry', 'Geo Country', NOW(), NOW()),
(NULL, 'Geo State', 'geostate', 'customer.components.survey-field-builder.geostate.FieldBuilderTypeGeostate', 'Geo State', NOW(), NOW()),
(NULL, 'Checkbox', 'checkbox', 'customer.components.survey-field-builder.checkbox.FieldBuilderTypeCheckbox', 'Checkbox', NOW(), NOW()),
(NULL, 'Checkbox List', 'checkboxlist', 'customer.components.survey-field-builder.checkboxlist.FieldBuilderTypeCheckboxlist', 'Checkbox List', NOW(), NOW()),
(NULL, 'Radio List', 'radiolist', 'customer.components.survey-field-builder.radiolist.FieldBuilderTypeRadiolist', 'Radio List', NOW(), NOW()),
(NULL, 'Consent Checkbox', 'consentcheckbox', 'customer.components.survey-field-builder.consentcheckbox.FieldBuilderTypeConsentCheckbox', 'Consent Checkbox', NOW(), NOW()),
(NULL, 'Dropdown', 'dropdown', 'customer.components.survey-field-builder.dropdown.FieldBuilderTypeDropdown', 'Dropdown', NOW(), NOW()),
(NULL, 'Multiselect', 'multiselect', 'customer.components.survey-field-builder.multiselect.FieldBuilderTypeMultiselect', 'Multiselect', NOW(), NOW()),
(NULL, 'Date', 'date', 'customer.components.survey-field-builder.date.FieldBuilderTypeDate', 'Date', NOW(), NOW()),
(NULL, 'Datetime', 'datetime', 'customer.components.survey-field-builder.datetime.FieldBuilderTypeDatetime', 'Datetime', NOW(), NOW()),
(NULL, 'Phone Number', 'phonenumber', 'customer.components.survey-field-builder.phonenumber.FieldBuilderTypePhonenumber', 'Phone Number', NOW(), NOW()),
(NULL, 'Email', 'email', 'customer.components.survey-field-builder.email.FieldBuilderTypeEmail', 'Email', NOW(), NOW()),
(NULL, 'Url', 'url', 'customer.components.survey-field-builder.url.FieldBuilderTypeUrl', 'Url', NOW(), NOW()),
(NULL, 'Rating', 'rating', 'customer.components.survey-field-builder.rating.FieldBuilderTypeRating', 'Rating', NOW(), NOW()),
(NULL, 'Country', 'country', 'customer.components.survey-field-builder.country.FieldBuilderTypeCountry', 'Country', NOW(), NOW()),
(NULL, 'State', 'state', 'customer.components.survey-field-builder.state.FieldBuilderTypeState', 'State', NOW(), NOW()),
(NULL, 'Years Range', 'yearsrange', 'customer.components.survey-field-builder.yearsrange.FieldBuilderTypeYearsRange', 'Years Range', NOW(), NOW()),
(NULL, 'Textarea', 'textarea', 'customer.components.survey-field-builder.textarea.FieldBuilderTypeTextarea', 'Textarea', NOW(), NOW());
-- --------------------------------------------------------

-- -----------------------------------------------------
-- Dumping data for table `survey_segment_operator`
-- -----------------------------------------------------

INSERT INTO `survey_segment_operator` (`operator_id`, `name`, `slug`, `date_added`, `last_updated`) VALUES
(1, 'is', 'is', NOW(), NOW()),
(2, 'is not', 'is-not', NOW(), NOW()),
(3, 'contains', 'contains', NOW(), NOW()),
(4, 'not contains', 'not-contains', NOW(), NOW()),
(5, 'starts with', 'starts', NOW(), NOW()),
(6, 'ends with', 'ends', NOW(), NOW()),
(7, 'is greater than', 'greater', NOW(), NOW()),
(8, 'is less than', 'less', NOW(), NOW()),
(9, 'not starts with', 'not-starts', NOW(), NOW()),
(10, 'not ends with', 'not-ends', NOW(), NOW());
-- --------------------------------------------------------

-- Custom fields
INSERT INTO `list_field_type` (`type_id` ,`name` ,`identifier` ,`class_alias` ,`description` ,`date_added` ,`last_updated`) VALUES
(NULL, 'Phone Number', 'phonenumber', 'customer.components.field-builder.phonenumber.FieldBuilderTypePhonenumber', 'Phone Number', NOW(), NOW()),
(NULL, 'Email', 'email', 'customer.components.field-builder.email.FieldBuilderTypeEmail', 'Email', NOW(), NOW()),
(NULL, 'Url', 'url', 'customer.components.field-builder.url.FieldBuilderTypeUrl', 'Url', NOW(), NOW()),
(NULL, 'Rating', 'rating', 'customer.components.field-builder.rating.FieldBuilderTypeRating', 'Rating', NOW(), NOW());
